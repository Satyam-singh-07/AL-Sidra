<?php

namespace App\Services;

use App\Models\YateemsHelp;
use App\Repositories\YateemsHelpRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class YateemsHelpService
{
    public function __construct(
        protected YateemsHelpRepository $repository
    ) {}

    public function store(array $data): YateemsHelp
    {
        return DB::transaction(function () use ($data) {

            // Attach authenticated user
            $data['user_id'] = auth()->id();

            // FORCE status (not from request)
            $data['status'] = 'active';

            // Upload QR Code
            if (!empty($data['qr_code'])) {
                $data['qr_code'] = $data['qr_code']->store('yateems/qr', 'public');
            }

            // Upload Video
            if (!empty($data['video'])) {
                $data['video'] = $data['video']->store('yateems/videos', 'public');
            }

            // Create main record
            $yateemsHelp = $this->repository->create(
                collect($data)->except([
                    'images',
                    'aadhaar_front',
                    'aadhaar_back'
                ])->toArray()
            );

            // Upload Images
            foreach ($data['images'] as $image) {
                $path = $image->store('yateems/images', 'public');

                $yateemsHelp->images()->create([
                    'image' => $path,
                ]);
            }

            // Upload Aadhaar documents (if present)
            if (!empty($data['aadhaar_front']) && !empty($data['aadhaar_back'])) {
                $frontPath = $data['aadhaar_front']->store('yateems/aadhaar', 'public');
                $backPath  = $data['aadhaar_back']->store('yateems/aadhaar', 'public');

                $yateemsHelp->document()->create([
                    'aadhaar_front' => $frontPath,
                    'aadhaar_back'  => $backPath,
                ]);
            }

            return $yateemsHelp;
        });
    }

    public function update(YateemsHelp $yateemsHelp, array $data): YateemsHelp
    {
        return DB::transaction(function () use ($yateemsHelp, $data) {

            // Replace QR Code (if uploaded)
            if (!empty($data['qr_code'])) {
                if ($yateemsHelp->qr_code) {
                    Storage::disk('public')->delete($yateemsHelp->qr_code);
                }
                $data['qr_code'] = $data['qr_code']->store('yateems/qr', 'public');
            }

            // Replace Video (if uploaded)
            if (!empty($data['video'])) {
                if ($yateemsHelp->video) {
                    Storage::disk('public')->delete($yateemsHelp->video);
                }
                $data['video'] = $data['video']->store('yateems/videos', 'public');
            }

            // Update main record
            $this->repository->update(
                $yateemsHelp,
                collect($data)->except(['images'])->toArray()
            );

            // Add new images (do NOT delete old ones)
            if (!empty($data['images'])) {
                foreach ($data['images'] as $image) {
                    $path = $image->store('yateems/images', 'public');

                    $yateemsHelp->images()->create([
                        'image' => $path,
                    ]);
                }
            }

            return $yateemsHelp;
        });
    }
}
