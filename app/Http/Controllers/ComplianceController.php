<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FacultyAssignment;
use App\Models\ComplianceDocument;
use App\Models\DocumentType;

class ComplianceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has access to compliance
        if (!$user->hasRole('Faculty Member')) {
            abort(403, 'Unauthorized access to compliance.');
        }

        // Get faculty assignments with compliance documents
        $assignments = FacultyAssignment::where('faculty_id', $user->id)
            ->with(['subject', 'term', 'complianceDocuments.documentType'])
            ->get();

        // Get all document types for reference
        $documentTypes = DocumentType::all();

        return view('compliances.index', compact('assignments', 'documentTypes', 'user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Faculty Member')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'assignment_id' => 'required|exists:faculty_assignments,id',
            'document_type_id' => 'required|exists:document_types,id',
            'drive_link' => 'required|url',
            'self_evaluation' => 'nullable|string',
        ]);

        // Verify the assignment belongs to the user
        $assignment = FacultyAssignment::where('id', $validated['assignment_id'])
            ->where('faculty_id', $user->id)
            ->firstOrFail();

        // Check if document already exists
        $existingDocument = ComplianceDocument::where('assignment_id', $validated['assignment_id'])
            ->where('document_type_id', $validated['document_type_id'])
            ->first();

        if ($existingDocument) {
            $existingDocument->update([
                'drive_link' => $validated['drive_link'],
                'self_evaluation' => $validated['self_evaluation'],
                'status' => 'Compiled',
            ]);
        } else {
            ComplianceDocument::create([
                'assignment_id' => $validated['assignment_id'],
                'document_type_id' => $validated['document_type_id'],
                'drive_link' => $validated['drive_link'],
                'self_evaluation' => $validated['self_evaluation'],
                'status' => 'Compiled',
            ]);
        }

        return back()->with('status', 'Compliance document saved successfully.');
    }

    public function saveDocument(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Faculty Member')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'assignment_id' => 'required|exists:faculty_assignments,id',
            'document_type_id' => 'required|exists:document_types,id',
            'drive_link' => 'required|url',
            'self_evaluation' => 'nullable|string',
        ]);

        // Verify the assignment belongs to the user
        $assignment = FacultyAssignment::where('id', $validated['assignment_id'])
            ->where('faculty_id', $user->id)
            ->firstOrFail();

        // Check if document already exists
        $existingDocument = ComplianceDocument::where('assignment_id', $validated['assignment_id'])
            ->where('document_type_id', $validated['document_type_id'])
            ->first();

        if ($existingDocument) {
            $existingDocument->update([
                'drive_link' => $validated['drive_link'],
                'self_evaluation' => $validated['self_evaluation'],
                'status' => 'Compiled',
            ]);
        } else {
            ComplianceDocument::create([
                'assignment_id' => $validated['assignment_id'],
                'document_type_id' => $validated['document_type_id'],
                'drive_link' => $validated['drive_link'],
                'self_evaluation' => $validated['self_evaluation'],
                'status' => 'Compiled',
            ]);
        }

        return back()->with('status', 'Document saved successfully.');
    }
}


