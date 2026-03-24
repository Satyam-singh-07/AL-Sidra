<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyQuote;
use App\Models\DailyQuoteLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyQuoteController extends Controller
{
    public function index(): View
    {
        $quotes = DailyQuote::orderBy('scheduled_time')->get();
        return view('admin.daily-quotes.index', compact('quotes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quote' => 'required|string',
            'scheduled_time' => 'required|date_format:H:i',
            'is_active' => 'required|boolean',
        ]);

        DailyQuote::create($data);

        return redirect()->route('daily-quotes.index')->with('success', 'Daily quote scheduled successfully');
    }

    public function update(Request $request, DailyQuote $dailyQuote)
    {
        $data = $request->validate([
            'quote' => 'required|string',
            'scheduled_time' => 'required|date_format:H:i',
            'is_active' => 'required|boolean',
        ]);

        $dailyQuote->update($data);

        return redirect()->route('daily-quotes.index')->with('success', 'Daily quote updated successfully');
    }

    public function destroy(DailyQuote $dailyQuote)
    {
        $dailyQuote->delete();
        return redirect()->route('daily-quotes.index')->with('success', 'Daily quote deleted successfully');
    }

    public function logs(): View
    {
        $logs = DailyQuoteLog::with('quote')->orderBy('sent_at', 'desc')->paginate(20);
        return view('admin.daily-quotes.logs', compact('logs'));
    }
}
