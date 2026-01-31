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

            $data['user_id'] = auth()->id();

            if (!empty($data['qr_code'])) {
                $data['qr_code'] = $data['qr_code']->store('yateems/qr', 'public');
            }

            if (!empty($data['video'])) {
                $data['video'] = $data['video']->store('yateems/videos', 'public');
            }

            $yateemsHelp = $this->repository->create(
                collect($data)->except([
                    'images',
                    'aadhaar_front',
                    'aadhaar_back'
                ])->toArray()
            );

            foreach ($data['images'] as $image) {
                $path = $image->store('yateems/images', 'public');

                $yateemsHelp->images()->create([
                    'image' => $path,
                ]);
            }

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

            if (!empty($data['qr_code'])) {
                if ($yateemsHelp->qr_code) {
                    Storage::disk('public')->delete($yateemsHelp->qr_code);
                }
                $data['qr_code'] = $data['qr_code']->store('yateems/qr', 'public');
            }

            if (!empty($data['video'])) {
                if ($yateemsHelp->video) {
                    Storage::disk('public')->delete($yateemsHelp->video);
                }
                $data['video'] = $data['video']->store('yateems/videos', 'public');
            }

            $this->repository->update(
                $yateemsHelp,
                collect($data)->except(['images'])->toArray()
            );

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
