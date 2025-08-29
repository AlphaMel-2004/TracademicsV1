<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FacultyAssignment;
use App\Models\ComplianceDocument;
use App\Models\ComplianceSubmission;
use App\Models\ComplianceLink;
use App\Models\DocumentType;
use App\Models\Subject;

class ComplianceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has access to compliance
        if (!$user->role || $user->role->name !== 'Faculty Member') {
            abort(403, 'Unauthorized access to compliance.');
        }

        // Get current semester
        $currentSemester = $user->currentSemester;
        if (!$currentSemester) {
            return redirect()->route('semester.select')->with('error', 'Please select a semester first.');
        }

        // Get faculty assignments (loads) for current semester as subject cards
        $assignments = FacultyAssignment::where('faculty_id', $user->id)
            ->where('semester_id', $currentSemester->id)
            ->get();

        // Calculate compliance data for each assignment
        $complianceData = [];
        foreach ($assignments as $assignment) {
            $complianceData[$assignment->id] = $this->calculateSubjectCompliance($assignment);
        }

        return view('compliances.index', compact('assignments', 'user', 'currentSemester', 'complianceData'));
    }

    /**
     * Calculate compliance progress for a specific subject
     */
    private function calculateSubjectCompliance($assignment)
    {
        // Get all document types
        $allDocumentTypes = DocumentType::all();
        
        // Get compliance documents for this assignment
        $complianceDocuments = ComplianceDocument::where('assignment_id', $assignment->id)->get();
        
        $totalRequirements = $allDocumentTypes->count();
        $completedRequirements = 0;
        
        // Check each document type to see if there's a compliance record with 'Complied' or 'Not Applicable' status
        foreach ($allDocumentTypes as $docType) {
            $compliance = $complianceDocuments->where('document_type_id', $docType->id)->first();
            
            if ($compliance && ($compliance->status === 'Complied' || $compliance->status === 'Not Applicable')) {
                $completedRequirements++;
            }
        }
        
        $percentage = $totalRequirements > 0 ? round(($completedRequirements / $totalRequirements) * 100) : 0;
        
        return [
            'total' => $totalRequirements,
            'completed' => $completedRequirements,
            'percentage' => $percentage
        ];
    }

    public function subject($subjectCode)
    {
        $user = Auth::user();
        
        if (!$user->role || $user->role->name !== 'Faculty Member') {
            abort(403, 'Unauthorized access.');
        }

        $currentSemester = $user->currentSemester;
        if (!$currentSemester) {
            return redirect()->route('semester.select')->with('error', 'Please select a semester first.');
        }

        // Get the specific assignment for this subject
        $assignment = FacultyAssignment::where('faculty_id', $user->id)
            ->where('subject_code', $subjectCode)
            ->where('semester_id', $currentSemester->id)
            ->firstOrFail();

        // Get semester-wide and subject-specific document types
        $allDocuments = DocumentType::orderBy('id')->get();
        
        // Faculty-wide documents (submit once per faculty user)
        $facultyWideNames = [
            'Information Sheet',
            'TOR/Diploma', 
            'Certificates of Trainings Attended (past 5 years)',
            'Faculty Load'
        ];
        
        $facultyWideDocuments = $allDocuments->whereIn('name', $facultyWideNames);
        $semesterSpecificDocuments = $allDocuments->whereNotIn('name', $facultyWideNames);

        // Get or create compliance documents for semester-specific requirements
        $semesterComplianceDocuments = collect();
        foreach ($semesterSpecificDocuments as $docType) {
            $compliance = ComplianceDocument::firstOrCreate([
                'user_id' => $user->id,
                'assignment_id' => $assignment->id,
                'document_type_id' => $docType->id,
            ], [
                'term_id' => $currentSemester->id,
                'status' => 'Not Complied',
            ]);
            $compliance->load(['links.submittedBy']);
            $semesterComplianceDocuments->push($compliance);
        }

        // Get or create faculty-wide compliance documents (use assignment but mark them differently)
        $facultyComplianceDocuments = collect();
        foreach ($facultyWideDocuments as $docType) {
            $compliance = ComplianceDocument::firstOrCreate([
                'user_id' => $user->id,
                'assignment_id' => $assignment->id,
                'document_type_id' => $docType->id,
            ], [
                'term_id' => $currentSemester->id,
                'status' => 'Not Complied',
            ]);
            $compliance->load(['links.submittedBy']);
            $facultyComplianceDocuments->push($compliance);
        }

        // Prepare data for the view
        $facultyWideRequirements = $facultyWideDocuments;
        $semesterSpecificRequirements = $semesterSpecificDocuments;
        $subjectDescription = $assignment->subject_description;
        
        // Prepare compliance data array
        $complianceData = [];
        
        // Add faculty-wide compliance data
        foreach ($facultyComplianceDocuments as $compliance) {
            $complianceData[$compliance->document_type_id] = [
                'compliance' => $compliance,
                'links' => $compliance->links
            ];
        }
        
        // Add semester-specific compliance data
        foreach ($semesterComplianceDocuments as $compliance) {
            $complianceData[$compliance->document_type_id] = [
                'compliance' => $compliance,
                'links' => $compliance->links
            ];
        }

        return view('compliances.subject', compact(
            'subjectCode',
            'subjectDescription',
            'currentSemester',
            'facultyWideRequirements',
            'semesterSpecificRequirements', 
            'complianceData'
        ));
    }

    public function submitLink(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->role || $user->role->name !== 'Faculty Member') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'subject_code' => 'required|string',
            'drive_link' => 'required|url',
            'description' => 'nullable|string|max:255',
        ]);

        $currentSemester = $user->currentSemester;
        if (!$currentSemester) {
            return redirect()->back()->with('error', 'Please select a semester first.');
        }

        $documentType = DocumentType::findOrFail($validated['document_type_id']);
        
        // Get the assignment for this subject
        $assignment = FacultyAssignment::where('faculty_id', $user->id)
            ->where('subject_code', $validated['subject_code'])
            ->where('semester_id', $currentSemester->id)
            ->first();
            
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found for this subject.');
        }

        // Create or get compliance document
        $complianceDocument = ComplianceDocument::firstOrCreate([
            'user_id' => $user->id,
            'assignment_id' => $assignment->id,
            'document_type_id' => $documentType->id,
        ], [
            'term_id' => $assignment->semester_id,
            'status' => 'Not Complied',
        ]);

        // Create the link
        ComplianceLink::create([
            'compliance_document_id' => $complianceDocument->id,
            'drive_link' => $validated['drive_link'],
            'description' => $validated['description'],
            'submitted_by' => $user->id,
            'submitted_at' => now(),
        ]);

        // Update compliance document status to "Complied" since a link was submitted
        $complianceDocument->update([
            'status' => 'Complied'
        ]);

        return back()->with('success', 'Document link submitted successfully.');
    }

    public function deleteLink(ComplianceLink $link)
    {
        $user = Auth::user();
        
        if (!$user->role || $user->role->name !== 'Faculty Member') {
            abort(403, 'Unauthorized action.');
        }

        // Verify ownership
        if ($link->submitted_by !== $user->id) {
            abort(403, 'Unauthorized to delete this link.');
        }

        $complianceDocument = $link->complianceDocument;
        $link->delete();

        // Update status if no more links exist
        if (!$complianceDocument->hasLinks()) {
            $complianceDocument->update(['status' => 'Not Complied']);
        }

        return back()->with('success', 'Document link deleted successfully.');
    }

    public function markAsNotApplicable(Request $request, DocumentType $documentType)
    {
        $user = Auth::user();
        $subjectCode = $request->input('subject_code');
        
        // Get current semester
        $currentSemester = $user->currentSemester;
        if (!$currentSemester) {
            return back()->with('error', 'Please select a semester first.');
        }
        
        // Find the assignment
        $assignment = FacultyAssignment::where('faculty_id', $user->id)
            ->where('subject_code', $subjectCode)
            ->where('semester_id', $currentSemester->id)
            ->first();
            
        if (!$assignment) {
            return back()->with('error', 'Assignment not found for this subject.');
        }
        
        // Find or create compliance document for this document type
        $complianceDocument = ComplianceDocument::firstOrCreate([
            'user_id' => $user->id,
            'assignment_id' => $assignment->id,
            'document_type_id' => $documentType->id,
        ], [
            'term_id' => $currentSemester->id,
            'status' => 'Not Applicable'
        ]);

        // If it already exists, update the status
        if (!$complianceDocument->wasRecentlyCreated) {
            $complianceDocument->update(['status' => 'Not Applicable']);
        }

        return back()->with('success', 'Document marked as Not Applicable successfully.');
    }

    public function unmarkNotApplicable(Request $request, DocumentType $documentType)
    {
        $user = Auth::user();
        $subjectCode = $request->input('subject_code');
        
        // Get current semester
        $currentSemester = $user->currentSemester;
        if (!$currentSemester) {
            return back()->with('error', 'Please select a semester first.');
        }
        
        // Find the assignment
        $assignment = FacultyAssignment::where('faculty_id', $user->id)
            ->where('subject_code', $subjectCode)
            ->where('semester_id', $currentSemester->id)
            ->first();
            
        if (!$assignment) {
            return back()->with('error', 'Assignment not found for this subject.');
        }

        // Find the compliance document
        $complianceDocument = ComplianceDocument::where([
            'user_id' => $user->id,
            'assignment_id' => $assignment->id,
            'document_type_id' => $documentType->id,
        ])->first();

        if ($complianceDocument) {
            // Determine the new status based on whether links exist
            $newStatus = $complianceDocument->hasLinks() ? 'Complied' : 'Not Complied';
            $complianceDocument->update(['status' => $newStatus]);
        }

        return back()->with('success', 'Document reverted from Not Applicable successfully.');
    }
}


