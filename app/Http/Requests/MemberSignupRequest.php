<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberSignupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            // basic info
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:15'],

            // member classification
            'member_category_id' => ['required', 'exists:member_categories,id'],

            // place selection
            'place_type' => ['required', Rule::in(['masjid', 'madarsa'])],
            'place_id'   => ['required', 'integer'],

            // optional education info
            'institute_name'       => ['nullable', 'string', 'max:255'],
            'degree_complete_year' => ['nullable', 'digits:4', 'integer'],

            // optional documents
            'degree_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'aadhaar_front'=> ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'aadhaar_back' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_category_id.required' => 'Member category is required',
            'member_category_id.exists'   => 'Invalid member category selected',

            'place_type.required' => 'Please select masjid or madarsa',
            'place_type.in'       => 'Invalid place type selected',

            'place_id.required'   => 'Please select a masjid or madarsa',
            'place_id.integer'    => 'Invalid place selected',

            'degree_complete_year.digits' => 'Degree year must be a valid year',
        ];
    }
}
