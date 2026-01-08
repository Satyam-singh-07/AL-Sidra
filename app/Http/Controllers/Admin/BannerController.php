<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index() : View {
        $banners = Banner::all();
        return view('admin.banners',compact('banners'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp',
            'status' => 'required|in:active,inactive'
        ]);

        $data['image'] = $request->file('image')->store('banners', 'public');

        Banner::create($data);

        return redirect()->route('banners.index')->with('success', 'Banner added successfully');
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            // delete old image
            Storage::disk('public')->delete($banner->getRawOriginal('image'));

            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('banners.index')->with('success', 'Banner updated');
    }

    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->getRawOriginal('image'));

        $banner->delete();

        return redirect()->route('banners.index')->with('success', 'Banner deleted');
    }

    public function getBanners()
    {
        $banners = Banner::get('image');
        return response($banners);
    }
}
