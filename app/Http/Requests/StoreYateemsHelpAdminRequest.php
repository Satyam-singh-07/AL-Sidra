<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreYateemsHelpAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',

            'images'      => 'required|array|min:1',
            'images.*'    => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

            'video'       => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:20000',

            'bank_name'   => 'required|string|max:255',
            'ifsc_code'   => 'required|string|max:20',
            'account_no'  => 'required|string|max:50',

            'upi_id'      => 'nullable|string|max:255',
            'qr_code'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Aadhaar OPTIONAL for admin
            'aadhaar_front' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'aadhaar_back'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
