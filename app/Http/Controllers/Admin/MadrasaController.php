<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMadarsaRequest;
use App\Http\Requests\UpdateMadarsaRequest;
use App\Models\Community;
use App\Models\Madarsa;
use App\Services\MadarsaService;
use Illuminate\Http\Request;

class MadrasaController extends Controller
{
    public function __construct(
        protected MadarsaService $madarsaService
    ) {}

    public function index(Request $request)
    {
        $query = Madarsa::with(['community', 'images', 'user']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('community')) {
            $query->where('community_id', $request->community);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $madarsas = $query->latest()->paginate(10);

        $stats = [
            'total'    => Madarsa::count(),
            'active'   => Madarsa::where('status', 'active')->count(),
            'pending'  => Madarsa::where('status', 'pending')->count(),
            'inactive' => Madarsa::where('status', 'inactive')->count(),
        ];

        $communities = Community::all();

        return view('admin.madarsas', compact(
            'madarsas',
            'communities',
            'stats'
        ));
    }

    public function create()
    {
        $communities = Community::all();

        return view('admin.madarsas-create', compact('communities'));
    }

    public function store(StoreMadarsaRequest $request)
    {
        $this->madarsaService->create(
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('madarsas.index')
            ->with('success', 'Madarsa created successfully');
    }

    public function show(Madarsa $madarsa)
    {
        $madarsa->load([
            'community',
            'images',
            'user',
            'memberProfiles.user',
            'memberProfiles.category',
        ]);

        return view('admin.madarsas-show', compact('madarsa'));
    }

    public function edit(Madarsa $madarsa)
    {
        $madarsa->load([
            'images',
            'community',
        ]);

        $communities = Community::all();

        return view('admin.madarsas-edit', compact(
            'madarsa',
            'communities'
        ));
    }

    public function update(UpdateMadarsaRequest $request, Madarsa $madarsa)
    {
        $this->madarsaService->update(
            $madarsa,
            $request->validated()
        );

        return redirect()
            ->route('madarsas.index')
            ->with('success', 'Madarsa updated successfully');
    }

    public function destroy(Madarsa $madarsa)
    {
        $this->madarsaService->delete($madarsa);

        return redirect()
            ->route('madarsas.index')
            ->with('success', 'Madarsa deleted successfully');
    }

    public function cycleStatus(Madarsa $madarsa)
    {
        $nextStatus = match ($madarsa->status) {
            'active'   => 'pending',
            'pending'  => 'inactive',
            'inactive' => 'active',
            default    => 'active',
        };

        $madarsa->update([
            'status' => $nextStatus,
        ]);

        return response()->json([
            'status' => $nextStatus,
        ]);
    }
}
