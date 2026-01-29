<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMasjidRequest;
use App\Services\MasjidService;

class MasjidController extends Controller
{
    public function __construct(
        protected MasjidService $masjidService
    ) {}

    public function index()
    {
        return view('admin.masjids');
    }

    public function create()
    {
        $communities = Community::all();
        return view('admin.masjid-create', compact('communities'));
    }

    public function store(StoreMasjidRequest $request)
    {
        $this->masjidService->create(
            $request->validated(),
            auth()->user() // admin (web guard)
        );

        return redirect()
            ->route('masjids.index')
            ->with('success', 'Masjid created successfully');
    }
}
