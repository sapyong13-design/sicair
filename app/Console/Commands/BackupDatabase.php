<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=7 : Number of backups to keep}';
    protected $description = 'Backup SQLite database to storage/app/backups/';

    public function handle(): int
    {
        $dbPath = config('database.connections.sqlite.database');

        if (!file_exists($dbPath)) {
            $this->error("Database file not found: {$dbPath}");
            return 1;
        }

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'database-' . now()->format('Y-m-d-His') . '.sqlite';
        $dest = $backupDir . '/' . $filename;

        if (!copy($dbPath, $dest)) {
            $this->error("Failed to copy database to {$dest}");
            return 1;
        }

        $this->info("Backup created: {$filename}");

        // Prune old backups (keep last N)
        $keep = (int) $this->option('keep');
        $files = glob($backupDir . '/database-*.sqlite');
        rsort($files);
        foreach (array_slice($files, $keep) as $old) {
            unlink($old);
            $this->line("Deleted old backup: " . basename($old));
        }

        return 0;
    }
}
