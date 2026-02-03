<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMadarsaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

            'passbook' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:5120'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'registration_date' => ['nullable', 'date'],

            'video' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:20480'],

            // images optional on update
            'madarsa_images' => ['nullable', 'array', 'max:5'],
            'madarsa_images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }
}
