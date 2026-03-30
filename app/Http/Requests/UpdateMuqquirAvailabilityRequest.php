<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateMuqquirAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only approved muqquirs can update availability
        return $this->user() && $this->user()->muqquirProfile()->where('status', 'approved')->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $sixMonthsFromNow = now()->addMonths(6)->format('Y-m-d');
        
        return [
            'unavailable_dates' => 'required|array',
            'unavailable_dates.*' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:' . $sixMonthsFromNow
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $allowedKeys = ['unavailable_dates'];
            $inputKeys = array_keys($this->all());
            $unexpectedKeys = array_diff($inputKeys, $allowedKeys);

            if (!empty($unexpectedKeys)) {
                $validator->errors()->add(
                    'payload',
                    'Only unavailable_dates is allowed in this request.'
                );
            }
        });
    }
}
