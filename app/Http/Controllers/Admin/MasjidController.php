<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMasjidRequest;
use App\Services\MasjidService;
use App\Models\Masjid;

class MasjidController extends Controller
{
    public function __construct(
        protected MasjidService $masjidService
    ) {}

    public function index(Request $request)
    {
        $query = Masjid::with(['community', 'images', 'user']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('address', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('community')) {
            $query->where('community_id', $request->community);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $masjids = $query->latest()->paginate(10);

        $stats = [
            'total'   => Masjid::count(),
            'active'  => Masjid::where('status', 'active')->count(),
            'pending' => Masjid::where('status', 'pending')->count(),
            'inactive' => Masjid::where('status', 'inactive')->count(),
        ];

        $communities = Community::all();

        return view('admin.masjids', compact('masjids', 'communities', 'stats'));
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
