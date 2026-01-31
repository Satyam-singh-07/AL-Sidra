<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreYateemsHelpAdminRequest;
use App\Http\Requests\UpdateYateemsHelpRequest;
use App\Models\YateemsHelp;
use Illuminate\Http\Request;
use App\Services\YateemsHelpService;
use Illuminate\Support\Facades\Storage;

class YateemController extends Controller
{
    public function __construct(
        protected YateemsHelpService $service
    ) {}

    public function index(Request $request)
    {
        $query = YateemsHelp::with(['images']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $yateemsHelps = $query
            ->latest()
            ->paginate(10);

        return view('admin.yateems', compact('yateemsHelps'));
    }

    public function show($id)
    {
        $yateemsHelp = YateemsHelp::with(['images', 'document'])->findOrFail($id);
        return view('admin.yateems-show', compact('yateemsHelp'));
    }

    public function create()
    {
        return view('admin.yateems-create');
    }

    public function store(StoreYateemsHelpAdminRequest $request)
    {
        $this->service->store($request->validated());

        return redirect()
            ->route('yateems-helps.index')
            ->with('success', 'Yateems Help created successfully.');
    }

    public function destroy($id)
    {
        $yateemsHelp = YateemsHelp::with(['images', 'document'])->findOrFail($id);

        if ($yateemsHelp->video) {
            Storage::disk('public')->delete($yateemsHelp->video);
        }

        if ($yateemsHelp->qr_code) {
            Storage::disk('public')->delete($yateemsHelp->qr_code);
        }

        foreach ($yateemsHelp->images as $image) {
            Storage::disk('public')->delete($image->image);
            $image->delete();
        }

        if ($yateemsHelp->document) {
            Storage::disk('public')->delete($yateemsHelp->document->aadhaar_front);
            Storage::disk('public')->delete($yateemsHelp->document->aadhaar_back);
            $yateemsHelp->document->delete();
        }

        $yateemsHelp->delete();

        return redirect()
            ->route('yateems-helps.index')
            ->with('success', 'Yateems Help deleted successfully.');
    }

    public function edit($id)
    {
        $yateemsHelp = YateemsHelp::findOrFail($id);
        return view('admin.yateems-edit', compact('yateemsHelp'));
    }

    public function update(
        UpdateYateemsHelpRequest $request,
        YateemsHelp $yateemsHelp
    ) {
        $this->service->update($yateemsHelp, $request->validated());

        return redirect()
            ->route('yateems-helps.index')
            ->with('success', 'Yateems Help updated successfully.');
    }

    public function toggleStatus(YateemsHelp $yateemsHelp)
    {
        $nextStatus = match ($yateemsHelp->status) {
            'pending'  => 'active',
            'active'   => 'inactive',
            'inactive' => 'pending',
            default    => 'pending',
        };

        $yateemsHelp->update([
            'status' => $nextStatus,
        ]);

        return response()->json([
            'status' => $nextStatus,
        ]);
    }
}
