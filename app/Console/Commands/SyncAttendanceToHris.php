<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncAttendanceToHris extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-to-hris
                            {--status=verified : which status to sync, e.g. verified or verified,flagged}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync unsynced attendance logs to HRIS attendance receive endpoint';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting attendance sync to HRIS...');

        $statusOption = $this->option('status') ?? 'verified';
        $statuses = array_filter(array_map('trim', explode(',', $statusOption)), fn ($value) => $value !== '');

        if (empty($statuses)) {
            $statuses = ['verified'];
        }

        $query = AttendanceLog::query()
            ->whereNull('synced_to_hris_at')
            ->whereIn('status', $statuses)
            ->orderBy('id');

        // Note: Flagged attendance records that are approved by admin become status='verified'
        // and will be automatically picked up by the next sync run (no manual intervention needed)

        $totalProcessed = 0;
        $totalSynced = 0;
        $totalFailed = 0;
        $chunkSize = 200;
        $dumpedFirstFailure = false;

        $query->chunkById($chunkSize, function ($records) use (&$totalProcessed, &$totalSynced, &$totalFailed, &$dumpedFirstFailure): void {
            $ids = $records->pluck('id')->all();
            $payload = $records->map(function ($record) {
                $photoUrl = null;

                if (! empty($record->photo_path)) {
                    // Build a public URL that the HRIS server can fetch over the network.
                    // This requires the waj-attendance server to be reachable from the HRIS server's network.
                    // In the current dev setup, a laptop/local IP can make this fragile if the address changes.
                    $photoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($record->photo_path);
                }

                return [
                    'pin' => (string) $record->pin,
                    'datetime' => $record->datetime?->format('Y-m-d H:i:s'),
                    'tanggal' => $record->tanggal?->format('Y-m-d'),
                    'status' => $record->type ?? 'IN',
                    'verified' => $record->is_within_geofence ? 1 : 0,
                    'photo_url' => $photoUrl,
                ];
            })->values()->all();

            $totalProcessed += count($records);

            try {
                $response = Http::withHeaders([
                    'X-API-KEY' => config('services.hris.key'),
                    'Accept' => 'application/json',
                ])->post(config('services.hris.url') . '/attendance/receive', [
                    'records' => $payload,
                ]);
            } catch (\Throwable $e) {
                $totalFailed += count($records);
                Log::error('HRIS attendance sync request failed', [
                    'chunk_ids' => $ids,
                    'status_filter' => $this->option('status') ?? 'verified',
                    'error' => $e->getMessage(),
                ]);
                $this->warn("  Failed chunk {$ids[0]}-{$ids[count($ids) - 1]}: {$e->getMessage()}");
                return;
            }

            if (! $response->successful()) {
                $totalFailed += count($records);
                $responseBody = $response->body();
                $responseJson = null;

                try {
                    $responseJson = $response->json();
                } catch (\Throwable $e) {
                    $responseJson = null;
                }

                Log::error('HRIS attendance sync returned non-200 response', [
                    'chunk_ids' => $ids,
                    'status_code' => $response->status(),
                    'response_body' => $responseBody,
                    'response_json' => $responseJson,
                    'payload_sent' => $payload,
                ]);

                $this->warn("  Failed chunk {$ids[0]}-{$ids[count($ids) - 1]} with status {$response->status()}");

                if (! $dumpedFirstFailure) {
                    $dumpedFirstFailure = true;
                    $this->line('  First failed response body:');
                    dump($responseBody);
                }

                return;
            }

            // Retry-safe design: records remain unsynced (synced_to_hris_at stays null)
            // if this run fails, so they will be picked up again by the next scheduled run.
            AttendanceLog::whereIn('id', $ids)->update(['synced_to_hris_at' => now()]);
            $totalSynced += count($records);

            $this->line("  Synced chunk {$ids[0]}-{$ids[count($ids) - 1]}");
        });

        $this->newLine();
        $this->info('Attendance sync summary');
        $this->table(
            ['Processed', 'Successfully Synced', 'Failed (will retry)'],
            [[$totalProcessed, $totalSynced, $totalFailed]]
        );

        return Command::SUCCESS;
    }
}
