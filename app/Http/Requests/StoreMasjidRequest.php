<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasjidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth already handled by guard
    }

    public function rules(): array
    {
        $imageRule = $this->isMethod('POST') ? 'required|array|min:1|max:5' : 'nullable|array|max:5';

        return [
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'community_id' => 'required|exists:communities,id',
            'status' => 'required|in:active,pending,inactive',
            'kyc_status' => 'nullable|in:active,inactive',

            'passbook' => 'nullable|file|max:5120',
            'registration_number' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'masjid_images' => $imageRule,
            'masjid_images.*' => 'image|max:5120',
            'masjid_video' => 'nullable|mimes:mp4,webm,ogg|max:20480',
        ];
    }
}

