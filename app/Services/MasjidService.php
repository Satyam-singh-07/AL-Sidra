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
                'name'        => $data['name'],
                // 'gender'      => $data['gender'],
                'address'     => $data['address'],
                'latitude'    => $data['latitude'],
                'longitude'   => $data['longitude'],
                'community_id' => $data['community_id'],
                'status'      => $data['status'],
            ]);

            if (!empty($data['passbook'])) {
                if ($masjid->passbook_path) {
                    Storage::disk('public')->delete($masjid->passbook_path);
                }

                $masjid->update([
                    'passbook' => $data['passbook']->store('masjids/passbooks', 'public')
                ]);
            }

            if (!empty($data['video'])) {
                if ($masjid->video_path) {
                    Storage::disk('public')->delete($masjid->video_path);
                }

                $masjid->update([
                    'video' => $data['video']->store('masjids/videos', 'public')
                ]);
            }

            if (!empty($data['masjid_images'])) {
                foreach ($data['masjid_images'] as $image) {
                    $masjid->images()->create([
                        'image_path' => $image->store('masjids/images', 'public')
                    ]);
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
}
