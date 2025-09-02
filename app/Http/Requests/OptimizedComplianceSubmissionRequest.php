<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\DocumentType;
use App\Models\FacultyAssignment;

class OptimizedComplianceSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role && $this->user()->role->name === 'Faculty Member';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'document_type_id' => [
                'required',
                'integer',
                Rule::exists('document_types', 'id')->where(function ($query) {
                    // Cache document types for faster validation
                    $documentTypes = Cache::remember('active_document_types', 3600, function () {
                        return DocumentType::pluck('id')->toArray();
                    });
                    $query->whereIn('id', $documentTypes);
                })
            ],
            'subject_code' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    // Validate that the faculty member is assigned to this subject
                    $userId = $this->user()->id;
                    $cacheKey = "faculty_subjects_{$userId}";
                    
                    $assignedSubjects = Cache::remember($cacheKey, 1800, function () use ($userId) {
                        return FacultyAssignment::where('faculty_id', $userId)
                            ->pluck('subject_code')
                            ->toArray();
                    });

                    if (!in_array($value, $assignedSubjects)) {
                        $fail('You are not assigned to teach this subject.');
                    }
                }
            ],
            'drive_link' => [
                'required',
                'url',
                'max:2048',
                function ($attribute, $value, $fail) {
                    // Validate Google Drive or OneDrive links only
                    $allowedDomains = ['drive.google.com', 'docs.google.com', 'onedrive.live.com', '1drv.ms'];
                    $parsedUrl = parse_url($value);
                    
                    if (!isset($parsedUrl['host']) || !in_array($parsedUrl['host'], $allowedDomains)) {
                        $fail('The link must be from Google Drive or Microsoft OneDrive.');
                    }
                }
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_.,!?()]+$/' // Allow only safe characters
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'document_type_id.required' => 'Please select a document type.',
            'document_type_id.exists' => 'The selected document type is invalid.',
            'subject_code.required' => 'Subject code is required.',
            'subject_code.max' => 'Subject code cannot exceed 20 characters.',
            'drive_link.required' => 'Document link is required.',
            'drive_link.url' => 'Please provide a valid URL.',
            'drive_link.max' => 'Link is too long (maximum 2048 characters).',
            'description.max' => 'Description cannot exceed 500 characters.',
            'description.regex' => 'Description contains invalid characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'document_type_id' => 'document type',
            'subject_code' => 'subject',
            'drive_link' => 'document link',
            'description' => 'description',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateDuplicateSubmission($validator);
            $this->validateSubmissionWindow($validator);
        });
    }

    /**
     * Check for duplicate submissions
     */
    protected function validateDuplicateSubmission($validator)
    {
        $userId = $this->user()->id;
        $documentTypeId = $this->input('document_type_id');
        $subjectCode = $this->input('subject_code');
        
        // Cache recent submissions for this user
        $cacheKey = "recent_submissions_{$userId}";
        $recentSubmissions = Cache::remember($cacheKey, 300, function () use ($userId) {
            return \App\Models\ComplianceDocument::whereHas('assignment', function ($query) use ($userId) {
                $query->where('faculty_id', $userId);
            })
            ->where('created_at', '>=', now()->subMinutes(5))
            ->get(['document_type_id', 'assignment_id'])
            ->toArray();
        });

        // Check if this exact submission was made recently
        $assignmentId = FacultyAssignment::where('faculty_id', $userId)
            ->where('subject_code', $subjectCode)
            ->value('id');

        foreach ($recentSubmissions as $submission) {
            if ($submission['document_type_id'] == $documentTypeId && 
                $submission['assignment_id'] == $assignmentId) {
                $validator->errors()->add('drive_link', 'You have already submitted this document recently. Please wait before resubmitting.');
                break;
            }
        }
    }

    /**
     * Validate submission is within allowed time window
     */
    protected function validateSubmissionWindow($validator)
    {
        $currentSemester = $this->user()->currentSemester;
        
        if (!$currentSemester) {
            $validator->errors()->add('subject_code', 'Please select a semester before submitting documents.');
            return;
        }

        // Check if submissions are allowed (could be based on semester dates)
        $submissionDeadline = Cache::remember('submission_deadline', 3600, function () use ($currentSemester) {
            // You can add logic here to get submission deadlines from database
            // For now, assume submissions are always allowed during active semester
            return null;
        });

        if ($submissionDeadline && now()->isAfter($submissionDeadline)) {
            $validator->errors()->add('drive_link', 'The submission deadline has passed for this semester.');
        }
    }

    /**
     * Get the validated data with additional processing
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Clean and sanitize the description
        if (isset($validated['description'])) {
            $validated['description'] = trim(strip_tags($validated['description']));
        }

        // Ensure the drive link is properly formatted
        if (isset($validated['drive_link'])) {
            $validated['drive_link'] = trim($validated['drive_link']);
            
            // Convert Google Drive share links to direct view links if needed
            $validated['drive_link'] = $this->normalizeGoogleDriveLink($validated['drive_link']);
        }

        return $validated;
    }

    /**
     * Normalize Google Drive links for consistency
     */
    protected function normalizeGoogleDriveLink($link)
    {
        // Convert sharing links to view links for better compatibility
        if (strpos($link, 'drive.google.com/file/d/') !== false) {
            preg_match('/\/file\/d\/([a-zA-Z0-9-_]+)/', $link, $matches);
            if (isset($matches[1])) {
                return "https://drive.google.com/file/d/{$matches[1]}/view";
            }
        }

        return $link;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log validation failures for monitoring
        Log::info('Compliance submission validation failed', [
            'user_id' => $this->user()->id,
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['_token']),
        ]);

        parent::failedValidation($validator);
    }
}
