<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FacultyAssignment;
use App\Models\ComplianceDocument;
use App\Models\DocumentType;

class SubjectsController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasRole('Faculty Member')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $assignments = FacultyAssignment::where('faculty_id', $user->id)
            ->with(['subject', 'term', 'complianceDocuments.documentType'])
            ->get();

        return view('subjects.index', compact('assignments'));
    }

    public function show($assignmentId)
    {
        if (!Auth::user()->hasRole('Faculty Member')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $assignment = FacultyAssignment::where('id', $assignmentId)
            ->where('faculty_id', $user->id)
            ->with(['subject', 'term', 'complianceDocuments.documentType'])
            ->firstOrFail();

        $documentTypes = DocumentType::all();

        return view('subjects.show', compact('assignment', 'documentTypes'));
    }

    public function submitDocument(Request $request, $assignmentId)
    {
        if (!Auth::user()->hasRole('Faculty Member')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $assignment = FacultyAssignment::where('id', $assignmentId)
            ->where('faculty_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'drive_link' => 'required|url',
            'self_evaluation' => 'nullable|string',
        ]);

        // Check if document already exists
        $existingDocument = ComplianceDocument::where('assignment_id', $assignmentId)
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
                'assignment_id' => $assignmentId,
                'document_type_id' => $validated['document_type_id'],
                'drive_link' => $validated['drive_link'],
                'self_evaluation' => $validated['self_evaluation'],
                'status' => 'Compiled',
            ]);
        }

        return back()->with('status', 'Document submitted successfully.');
    }
}
