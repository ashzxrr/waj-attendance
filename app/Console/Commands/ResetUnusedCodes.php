<?php

namespace App\Console\Commands;

use App\Models\EmployeeAuth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetUnusedCodes extends Command
{
    protected $signature = 'attendance:reset-unused-codes
                            {--confirm : Apply the reset instead of showing a dry run}';

    protected $description = 'Reset attendance codes that have never been modified to the employee PIN default';

    public function handle(): int
    {
        $candidates = EmployeeAuth::with('employee')
            ->whereColumn('created_at', 'updated_at')
            ->orderBy('id')
            ->get();

        $skipped = EmployeeAuth::whereColumn('created_at', '!=', 'updated_at')->count();

        $this->info($this->option('confirm')
            ? 'Resetting never-modified attendance codes...'
            : 'DRY RUN: no attendance codes will be changed.');

        if ($candidates->isEmpty()) {
            $this->line('No never-modified employee_auth records found.');
        } else {
            $this->table(
                ['Nama', 'PIN', 'Created At', 'Updated At'],
                $candidates->map(fn (EmployeeAuth $auth) => [
                    $auth->employee?->nama ?? '(Nama tidak ditemukan)',
                    $auth->pin,
                    $auth->created_at->toDateTimeString(),
                    $auth->updated_at->toDateTimeString(),
                ])->all()
            );
        }

        if (! $this->option('confirm')) {
            $this->newLine();
            $this->warn('Dry run selesai. Jalankan dengan --confirm setelah meninjau daftar di atas.');
            $this->line('Total employees reset: 0 (would reset: ' . $candidates->count() . ')');
            $this->line("Total skipped (already changed): {$skipped}");

            return self::SUCCESS;
        }

        $reset = DB::transaction(function () {
            $resetCount = 0;

            EmployeeAuth::whereColumn('created_at', 'updated_at')
                ->orderBy('id')
                ->each(function (EmployeeAuth $auth) use (&$resetCount) {
                    $auth->update([
                        'pin_absensi' => str_pad((string) $auth->pin, 6, '0', STR_PAD_LEFT),
                        'kode_absensi_is_default' => true,
                    ]);
                    $resetCount++;
                });

            return $resetCount;
        });

        $this->newLine();
        $this->info("Total employees reset: {$reset}");
        $this->line("Total skipped (already changed): {$skipped}");

        return self::SUCCESS;
    }
}