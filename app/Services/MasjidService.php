<?php


namespace App\Services;

use App\Models\User;
use App\Repositories\Masjid\MasjidRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
}
