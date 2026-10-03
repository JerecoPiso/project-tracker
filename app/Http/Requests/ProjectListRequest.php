<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProjectListRequest extends FormRequest
{
    public const SORTABLE = ['client_name', 'project_name', 'status', 'priority', 'start_date', 'due_date', 'created_at'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(Project::STATUSES)],
            'priority' => ['nullable', Rule::in(Project::PRIORITIES)],
            'sort_by' => ['nullable', Rule::in(self::SORTABLE)],
            'sort_dir' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Status filter must be one of: ' . implode(', ', Project::STATUSES) . '.',
            'priority.in' => 'Priority filter must be one of: ' . implode(', ', Project::PRIORITIES) . '.',
            'sort_by.in' => 'Sort field must be one of: ' . implode(', ', self::SORTABLE) . '.',
            'sort_dir.in' => 'Sort direction must be asc or desc.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}
