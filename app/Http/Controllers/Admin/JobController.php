<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Display all jobs
     */
    public function index()
    {
        $jobs = Job::with(['category', 'user'])->latest()->get();
        return view('admin.jobs.index', compact('jobs'));
    }

    /**
     * Approve a job
     */
    public function approve($id)
    {
        $job = Job::findOrFail($id);
        $job->update(['status' => 'approved']);

        return redirect()
            ->route('jobs.index')
            ->with('success', 'Job approved successfully.');
    }

    /**
     * Reject a job
     */
    public function reject($id)
    {
        $job = Job::findOrFail($id);
        $job->update(['status' => 'rejected']);

        return redirect()
            ->route('jobs.index')
            ->with('success', 'Job rejected successfully.');
    }

    /**
     * Delete a job
     */
    public function destroy($id)
    {
        $job = Job::findOrFail($id);
        $job->delete();

        return redirect()
            ->route('jobs.index')
            ->with('success', 'Job deleted successfully.');
    }
}
