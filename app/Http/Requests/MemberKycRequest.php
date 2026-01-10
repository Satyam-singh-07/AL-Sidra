<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institute_name'        => ['nullable', 'string', 'max:255'],
            'degree_complete_year'  => ['nullable', 'digits:4'],
            'degree_photo'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'aadhaar_front'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'aadhaar_back'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }
}

