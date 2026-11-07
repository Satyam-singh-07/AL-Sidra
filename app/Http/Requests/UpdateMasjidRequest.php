<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMasjidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Filter out empty file uploads from masjid_images array
        if ($this->hasFile('masjid_images')) {
            $validFiles = array_filter(
                (array) $this->file('masjid_images'),
                fn($file) => $file instanceof \Illuminate\Http\UploadedFile && $file->isValid()
            );

            if (empty($validFiles)) {
                $this->request->remove('masjid_images');
                $this->files->remove('masjid_images');
            } else {
                $this->files->set('masjid_images', $validFiles);
            }
        } else {
            $this->request->remove('masjid_images');
            $this->files->remove('masjid_images');
        }

        // Clean up empty passbook or video if invalid/empty
        if ($this->hasFile('passbook') && !$this->file('passbook')->isValid()) {
            $this->request->remove('passbook');
            $this->files->remove('passbook');
        }

        if ($this->hasFile('masjid_video') && !$this->file('masjid_video')->isValid()) {
            $this->request->remove('masjid_video');
            $this->files->remove('masjid_video');
        }

        // If coordinates weren't passed or empty, fall back to current masjid coordinates
        $masjid = $this->route('masjid');
        if ($masjid) {
            if (!$this->filled('latitude') && $masjid->latitude) {
                $this->merge(['latitude' => $masjid->latitude]);
            }
            if (!$this->filled('longitude') && $masjid->longitude) {
                $this->merge(['longitude' => $masjid->longitude]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'gender'              => ['nullable', 'in:male,female'],
            'address'             => ['required', 'string'],
            'latitude'            => ['nullable', 'numeric'],
            'longitude'           => ['nullable', 'numeric'],
            'community_id'        => ['nullable', 'exists:communities,id'],
            'status'              => ['required', 'in:active,pending,inactive'],
            'kyc_status'          => ['nullable', 'in:active,inactive'],

            'passbook'            => ['nullable', 'file', 'max:5120'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'registration_date'   => ['nullable', 'date'],
            'jummah_khutba_time'  => ['nullable', 'date_format:H:i,H:i:s'],
            'masjid_images'       => ['nullable', 'array', 'max:5'],
            'masjid_images.*'     => ['nullable', 'image', 'max:5120'],
            'masjid_video'        => ['nullable', 'mimes:mp4,webm,ogg', 'max:20480'],
        ];
    }
}

