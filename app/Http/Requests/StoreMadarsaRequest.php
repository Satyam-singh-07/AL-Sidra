<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMadarsaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // handle auth elsewhere
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'address' => ['required', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'community_id' => ['required', 'exists:communities,id'],
            'status' => ['required', 'in:active,pending,inactive'],

            'students_count' => ['required', 'integer', 'min:0'],
            'staff_count' => ['required', 'integer', 'min:0'],

            'contact_number' => ['required', 'string', 'max:20'],
            'alternate_contact' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'website_url' => ['nullable', 'url'],

            'courses' => ['nullable', 'array'],
            'courses.*' => ['exists:madarsa_courses,id'],

            'collectors' => ['required', 'array', 'min:1'],
            'collectors.*.name' => ['required', 'string', 'max:255'],
            'collectors.*.contact' => ['required', 'string', 'max:20'],
            'collectors.*.address' => ['nullable', 'string'],

            'passbook' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:5120'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'registration_date' => ['nullable', 'date'],

            'video' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:20480'],

            'madarsa_images' => ['required', 'array', 'min:1', 'max:5'],
            'madarsa_images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }
}
