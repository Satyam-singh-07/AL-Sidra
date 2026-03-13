<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreYateemsHelpUserRequest;
use App\Models\YateemsHelp;
use App\Models\YateemsHelpCategory;
use App\Services\YateemsHelpService;
use Illuminate\Http\Request;

class YateemsHelpController extends Controller
{
    public function __construct(
        protected YateemsHelpService $service
    ) {}

    public function index(Request $request)
    {
        $yateemsHelps = YateemsHelp::where('status', 'active')
            ->with(['images:id,yateems_help_id,image', 'category']) 
            ->latest()
            ->get()
            ->map(function ($help) {
                return [
                    'id'          => $help->id,
                    'title'       => $help->title,
                    'description' => $help->description,
                    'category_name' => $help->category->name ?? null,
                    'category_description' => $help->category->description ?? null,
                    'image'       => optional($help->images->first())->image_url,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $yateemsHelps
        ]);
    }


    public function show($id)
    {
        $yateemsHelp = YateemsHelp::where('id', $id)
            ->where('status', 'active')
            ->with(['images', 'category'])
            ->first();

        if (!$yateemsHelp) {
            return response()->json([
                'success' => false,
                'message' => 'Yateems Help not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $yateemsHelp
        ]);
    }

    public function storeUser(StoreYateemsHelpUserRequest $request)
    {
        $this->service->store($request->validated());

        return response()->json([
            'message' => 'Yateems Help created successfully.'
        ], 201);
    }

    public function getCategories()
    {
        $categories = YateemsHelpCategory::where('status', 'active')
            ->select('id', 'name', 'description')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
