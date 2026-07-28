<?php

namespace App\Console\Commands;

use App\Models\EmployeeCache;
use App\Models\EmployeeAuth;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-employees
                            {--full : Force full sync ignoring last sync timestamp}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync employee data from HRIS API into employees_cache table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('Starting employee sync from HRIS...');

        // ---------------------------------------------------------------
        // Determine sync mode:
        //   --full flag  → fetch all records (no time filter)
        //   otherwise    → get latest synced_at from DB as updated_since
        //                  if no prior sync exists, fall back to full sync
        // ---------------------------------------------------------------
        $isFullSync = $this->option('full');
        $queryParams = [];

        if (! $isFullSync) {
            $latestSync = EmployeeCache::max('synced_at');

            if ($latestSync) {
                // Incremental sync: only fetch records updated since last sync
                $updatedSince = Carbon::parse($latestSync)->toIso8601String();
                $queryParams['updated_since'] = $updatedSince;
                $this->line("  Mode: incremental (since {$updatedSince})");
            } else {
                $this->line('  Mode: full (no prior sync found)');
            }
        } else {
            $this->line('  Mode: full (--flag forced)');
        }

        // ---------------------------------------------------------------
        // Pagination loop — fetch page by page until we reach last_page
        // ---------------------------------------------------------------
        $page = 1;
        $lastPage = 1;
        $totalSynced = 0;
        $createdCount = 0;
        $updatedCount = 0;

        // In-memory store for newly generated credentials (plain PINs)
        // These will be exported to CSV after the sync loop completes.
        // ⚠ WARNING: contains plaintext PINs — handle securely.
        $newCredentials = [];

        do {
            $this->line("  Fetching page {$page}...");

            try {
                $response = Http::withHeaders([
                    'X-API-KEY' => config('services.hris.key'),
                    'Accept' => 'application/json',
                ])->get(config('services.hris.url') . '/employees', array_merge(
                    $queryParams,
                    ['page' => $page]
                ));
            } catch (\Exception $e) {
                Log::error('HRIS sync: HTTP request failed', [
                    'page' => $page,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  HTTP request failed on page {$page}: {$e->getMessage()}");
                return Command::FAILURE;
            }

            if (! $response->successful()) {
                Log::error('HRIS sync: non-200 response', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->error("  HRIS API returned status {$response->status()} on page {$page}");
                return Command::FAILURE;
            }

            $payload = $response->json();
            $employees = $payload['data'] ?? [];
            $lastPage = $payload['last_page'] ?? 1;

            if (empty($employees)) {
                $this->line('  No employees returned on this page.');
                break;
            }

            // ---------------------------------------------------------------
            // Upsert each employee record matched by PIN
            // ---------------------------------------------------------------
            foreach ($employees as $emp) {
                $result = EmployeeCache::updateOrCreate(
                    ['pin' => $emp['pin']],
                    [
                        'nama' => $emp['nama'] ?? '',
                        'nik' => $emp['nik'] ?? null,
                        'is_active' => isset($emp['is_active']) ? (bool) $emp['is_active'] : true,
                        'synced_at' => now(),
                    ]
                );

                $totalSynced++;

                if ($result->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }

                // ---------------------------------------------------------------
                // Auto-generate login credentials for new employees
                // Only create auth record if one doesn't already exist for this PIN
                // ---------------------------------------------------------------
                if (! EmployeeAuth::where('pin', $emp['pin'])->exists()) {
                    // Generate a random 6-digit numeric security PIN
                    $plainPin = (string) random_int(100000, 999999);

                    EmployeeAuth::create([
                        'pin' => $emp['pin'],
                        'pin_absensi' => $plainPin, // model mutator auto-hashes this
                    ]);

                    // Store plain PIN in memory for CSV export (never persisted to DB)
                    $newCredentials[] = [
                        'pin' => $emp['pin'],
                        'nama' => $emp['nama'] ?? '',
                        'pin_absensi' => $plainPin,
                    ];
                }
            }

            $page++;

        } while ($page <= $lastPage);

        // ---------------------------------------------------------------
        // Export newly generated credentials to CSV (if any)
        // ---------------------------------------------------------------
        $exportPath = null;

        if (! empty($newCredentials)) {
            $exportDir = storage_path('app/exports');
            if (! is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            $filename = 'kode-absensi-baru-' . now()->format('Ymd_His') . '.csv';
            $exportPath = $exportDir . '/' . $filename;

            $handle = fopen($exportPath, 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Pin Karyawan', 'Nama', 'Kode Absensi']);

            foreach ($newCredentials as $cred) {
                fputcsv($handle, [$cred['pin'], $cred['nama'], $cred['pin_absensi']]);
            }

            fclose($handle);

            // ╔══════════════════════════════════════════════════════════════╗
            // ║  SAFETY NOTICE                                              ║
            // ║  This CSV contains plaintext (unhashed) security PINs.      ║
            // ║  - Share securely with HR only.                             ║
            // ║  - Delete the file after PINs have been distributed.        ║
            // ║  - The exports directory is in .gitignore — do NOT remove.  ║
            // ╚══════════════════════════════════════════════════════════════╝
        }

        // ---------------------------------------------------------------
        // Summary output
        // ---------------------------------------------------------------
        $elapsed = number_format((microtime(true) - $startTime), 2);

        $this->newLine();
        $this->info("Sync complete! — {$elapsed}s");
        $this->table(
            ['Total Synced', 'Created', 'Updated'],
            [[$totalSynced, $createdCount, $updatedCount]]
        );

        $newCount = count($newCredentials);
        if ($newCount > 0) {
            $this->newLine();
            $this->info("{$newCount} new employee credential(s) generated.");
            $this->line("  Exported to: {$exportPath}");
            $this->warn('  ⚠ This file contains plaintext PINs — delete after HR distributes them.');
        }

        return Command::SUCCESS;
    }
}