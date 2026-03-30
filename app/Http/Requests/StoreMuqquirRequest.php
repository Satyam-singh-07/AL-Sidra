<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMuqquirRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only member can be muqquir
        return $this->user() && $this->user()->whereHas('roles', function($q) {
            $q->where('slug', 'member');
        })->whereHas('memberProfile')->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'videos' => 'nullable|array|min:1|max:4',
            'videos.*' => 'file|mimes:mp4,mov,avi,wmv|max:20480', // 20MB max per video
            'account_no' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'travel_fee' => 'nullable|numeric|min:0',
        ];
    }
}
