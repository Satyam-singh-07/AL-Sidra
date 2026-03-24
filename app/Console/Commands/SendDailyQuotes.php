<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyQuote;
use App\Models\DailyQuoteLog;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDailyQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotes:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily scheduled quotes via push notifications';

    /**
     * Execute the console command.
     */
    public function handle(FirebaseNotificationService $notificationService)
    {
        $now = Carbon::now('Asia/Kolkata');
        $currentTime = $now->format('H:i');

        $quotes = DailyQuote::where('is_active', true)
            ->where('scheduled_time', $currentTime)
            ->where(function ($query) use ($now) {
                $query->whereNull('last_sent_at')
                    ->orWhereDate('last_sent_at', '<', $now->toDateString());
            })
            ->get();

        if ($quotes->isEmpty()) {
            return;
        }

        foreach ($quotes as $quote) {
            $this->info("Sending quote: \"{$quote->quote}\"");

            try {
                $totalSent = $notificationService->sendToAll(
                    "Daily Quote",
                    $quote->quote,
                    ['type' => 'daily_quote', 'quote_id' => $quote->id]
                );

                // Update last_sent_at
                $quote->update(['last_sent_at' => $now]);

                // Create log
                DailyQuoteLog::create([
                    'daily_quote_id' => $quote->id,
                    'sent_to_count' => $totalSent,
                    'sent_at' => $now,
                ]);

                $this->info("Sent to {$totalSent} users.");
            } catch (\Exception $e) {
                Log::error("Failed to send daily quote ID {$quote->id}: " . $e->getMessage());
                $this->error("Failed to send quote ID {$quote->id}");
            }
        }
    }
}
