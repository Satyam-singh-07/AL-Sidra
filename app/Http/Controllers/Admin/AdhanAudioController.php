<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdhanAudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdhanAudioController extends Controller
{
    public function index(): View
    {
        $adhanAudios = AdhanAudio::withCount('masjids')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('admin.adhan-audios', compact('adhanAudios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'reciter'    => 'nullable|string|max:255',
            'duration'   => 'nullable|string|max:50',
            'audio_file' => 'required|file|mimes:mp3,wav,ogg,aac,m4a|max:15360',
            'status'     => 'required|in:active,inactive',
        ], [
            'audio_file.required' => 'Please select an audio file to upload.',
            'audio_file.mimes'    => 'Audio file must be an MP3, WAV, OGG, AAC, or M4A format.',
            'audio_file.max'      => 'Audio file size cannot exceed 15MB.',
        ]);

        $path = $request->file('audio_file')->store('adhan_audios', 'public');

        AdhanAudio::create([
            'title'      => $data['title'],
            'reciter'    => $data['reciter'] ?? null,
            'duration'   => $data['duration'] ?? null,
            'audio_path' => $path,
            'status'     => $data['status'],
        ]);

        return redirect()->route('adhan-audios.index')->with('success', 'Adhan audio uploaded successfully');
    }

    public function update(Request $request, AdhanAudio $adhanAudio)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'reciter'    => 'nullable|string|max:255',
            'duration'   => 'nullable|string|max:50',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,aac,m4a|max:15360',
            'status'     => 'required|in:active,inactive',
        ]);

        $updateData = [
            'title'    => $data['title'],
            'reciter'  => $data['reciter'] ?? null,
            'duration' => $data['duration'] ?? null,
            'status'   => $data['status'],
        ];

        if ($request->hasFile('audio_file')) {
            if ($adhanAudio->audio_path) {
                Storage::disk('public')->delete($adhanAudio->audio_path);
            }
            $updateData['audio_path'] = $request->file('audio_file')->store('adhan_audios', 'public');
        }

        $adhanAudio->update($updateData);

        return redirect()->route('adhan-audios.index')->with('success', 'Adhan audio updated successfully');
    }

    public function destroy(AdhanAudio $adhanAudio)
    {
        if ($adhanAudio->audio_path) {
            Storage::disk('public')->delete($adhanAudio->audio_path);
        }

        $adhanAudio->delete();

        return redirect()->route('adhan-audios.index')->with('success', 'Adhan audio deleted successfully');
    }
}
