<?php

namespace App\Services;

use App\Models\Madarsa;
use App\Models\User;
use App\Repositories\MadarsaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MadarsaService
{
    public function __construct(
        protected MadarsaRepository $madarsaRepository
    ) {}

    public function create(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {

            // Files
            if (isset($data['passbook'])) {
                $data['passbook'] = $data['passbook']->store('madarsas/passbooks', 'public');
            }

            if (isset($data['video'])) {
                $data['video'] = $data['video']->store('madarsas/videos', 'public');
            }

            // Attach creator
            $data['user_id'] = $user->id;

            // Create madarsa
            $madarsa = $this->madarsaRepository->create($data);

            // Images
            foreach ($data['madarsa_images'] as $image) {
                $path = $image->store('madarsas/images', 'public');

                $this->madarsaRepository->addImage($madarsa->id, $path);
            }

            return $madarsa;
        });
    }

    public function update(Madarsa $madarsa, array $data)
    {
        return DB::transaction(function () use ($madarsa, $data) {

            // Replace passbook if uploaded
            if (isset($data['passbook'])) {
                if ($madarsa->passbook) {
                    Storage::disk('public')->delete($madarsa->passbook);
                }
                $data['passbook'] = $data['passbook']->store('madarsas/passbooks', 'public');
            } else {
                unset($data['passbook']);
            }

            // Replace video if uploaded
            if (isset($data['video'])) {
                if ($madarsa->video) {
                    Storage::disk('public')->delete($madarsa->video);
                }
                $data['video'] = $data['video']->store('madarsas/videos', 'public');
            } else {
                unset($data['video']);
            }

            // Update main record
            $this->madarsaRepository->update($madarsa, $data);

            // Add new images (if any)
            if (!empty($data['madarsa_images'])) {
                foreach ($data['madarsa_images'] as $image) {
                    $path = $image->store('madarsas/images', 'public');
                    $this->madarsaRepository->addImage($madarsa->id, $path);
                }
            }

            return $madarsa->refresh();
        });
    }

    public function delete(Madarsa $madarsa): void
    {
        DB::transaction(function () use ($madarsa) {

            // Delete images (files + DB)
            foreach ($madarsa->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Delete passbook
            if ($madarsa->passbook) {
                Storage::disk('public')->delete($madarsa->passbook);
            }

            // Delete video
            if ($madarsa->video) {
                Storage::disk('public')->delete($madarsa->video);
            }

            // Finally delete madarsa
            $madarsa->delete();
        });
    }
}
