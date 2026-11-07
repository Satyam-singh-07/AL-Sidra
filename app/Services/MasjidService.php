<?php


namespace App\Services;

use App\Models\User;
use App\Models\Masjid;
use App\Repositories\Masjid\MasjidRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Access\AuthorizationException;

class MasjidService
{
    public function __construct(
        protected MasjidRepositoryInterface $repository
    ) {}

    public function create(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {

            // Attach creator
            $data['user_id'] = $user->id;

            // // Fix community field
            // $data['community_id'] = $data['community'];
            // unset($data['community']);

            // Handle passbook
            if (!empty($data['passbook'])) {
                $data['passbook'] = $data['passbook']->store(
                    'masjids/passbooks',
                    'public'
                );
            }

            // Handle video
            if (!empty($data['masjid_video'])) {
                $data['video'] = $data['masjid_video']->store(
                    'masjids/videos',
                    'public'
                );
            }
            unset($data['masjid_video']);

            // Create masjid
            $masjid = $this->repository->create($data);

            // Handle images
            foreach ($data['masjid_images'] as $image) {
                $masjid->images()->create([
                    'image_path' => $image->store(
                        'masjids/images',
                        'public'
                    ),
                ]);
            }

            return $masjid;
        });
    }

    public function update(Masjid $masjid, array $data): void
    {
        DB::transaction(function () use ($masjid, $data) {

            $masjid->update([
                'name'                => $data['name'],
                'gender'              => $data['gender'] ?? $masjid->gender,
                'address'             => $data['address'],
                'latitude'            => $data['latitude'] ?? $masjid->latitude,
                'longitude'           => $data['longitude'] ?? $masjid->longitude,
                'community_id'        => array_key_exists('community_id', $data) ? $data['community_id'] : $masjid->community_id,
                'status'              => $data['status'],
                'kyc_status'          => $data['kyc_status'] ?? $masjid->kyc_status,
                'registration_number' => array_key_exists('registration_number', $data) ? $data['registration_number'] : $masjid->registration_number,
                'registration_date'   => array_key_exists('registration_date', $data) ? $data['registration_date'] : $masjid->registration_date,
                'jummah_khutba_time'  => array_key_exists('jummah_khutba_time', $data) ? $data['jummah_khutba_time'] : $masjid->jummah_khutba_time,
            ]);

            if (!empty($data['passbook']) && $data['passbook'] instanceof \Illuminate\Http\UploadedFile) {
                if ($masjid->passbook) {
                    Storage::disk('public')->delete($masjid->passbook);
                }

                $masjid->update([
                    'passbook' => $data['passbook']->store('masjids/passbooks', 'public')
                ]);
            }

            if (!empty($data['masjid_video']) && $data['masjid_video'] instanceof \Illuminate\Http\UploadedFile) {
                if ($masjid->video) {
                    Storage::disk('public')->delete($masjid->video);
                }

                $masjid->update([
                    'video' => $data['masjid_video']->store('masjids/videos', 'public')
                ]);
            }

            if (!empty($data['masjid_images']) && is_array($data['masjid_images'])) {
                foreach ($data['masjid_images'] as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile && $image->isValid()) {
                        $masjid->images()->create([
                            'image_path' => $image->store('masjids/images', 'public')
                        ]);
                    }
                }
            }
        });
    }

    public function delete(Masjid $masjid): void
    {
        DB::transaction(function () use ($masjid) {

            foreach ($masjid->images as $image) {
                if ($image->image_path) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            if ($masjid->passbook_path) {
                Storage::disk('public')->delete($masjid->passbook_path);
            }

            if ($masjid->video_path) {
                Storage::disk('public')->delete($masjid->video_path);
            }

            $masjid->delete();
        });
    }

    public function deleteImage(int $imageId): void
    {
        $image = \App\Models\MasjidImage::findOrFail($imageId);
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();
    }

    public function deleteVideo(Masjid $masjid): void
    {
        if ($masjid->video) {
            Storage::disk('public')->delete($masjid->video);
            $masjid->update(['video' => null]);
        }
    }
}
