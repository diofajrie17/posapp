<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup 
        {--compress : Simpan sebagai .sql.gz}
        {--rotate-days=30 : Hapus backup lebih tua dari N hari}';

    protected $description = 'Backup database ke storage/backups + rotasi file lama';

    public function handle(): int
    {
        $driver = env('DB_CONNECTION', 'mysql');
        $dir = storage_path('backups');
        if (!File::exists($dir)) File::makeDirectory($dir, 0775, true);

        $timestamp = now()->format('Ymd_His');
        $baseName  = "backup_{$driver}_{$timestamp}.sql";
        $sqlPath   = $dir . DIRECTORY_SEPARATOR . $baseName;

        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $cmd = $this->mysqlDumpCommand($sqlPath);
                break;
            case 'pgsql':
                $cmd = $this->pgDumpCommand($sqlPath);
                break;
            case 'sqlite':
                $cmd = $this->sqliteDump($sqlPath);
                if ($cmd === null) return self::SUCCESS; // sudah selesai (tanpa process)
                break;
            default:
                $this->error("Driver tidak didukung: {$driver}");
                return self::FAILURE;
        }

        if ($cmd instanceof Process) {
            $this->info('Menjalankan dump...');
            $cmd->setTimeout(300);
            $cmd->run(function ($type, $buffer) {
                if ($type === Process::ERR) $this->output->writeln("<fg=red>{$buffer}</>");
            });

            if (!$cmd->isSuccessful() || !File::exists($sqlPath) || File::size($sqlPath) === 0) {
                $this->error('Gagal membuat dump. Cek apakah mysqldump/pg_dump tersedia di PATH.');
                return self::FAILURE;
            }
        }

        // Kompresi optional
        if ($this->option('compress')) {
            $this->info('Mengompresi file...');
            $gzPath = $sqlPath . '.gz';
            $data = File::get($sqlPath);
            File::put($gzPath, gzencode($data, 9));
            File::delete($sqlPath);
            $this->line("OK: " . basename($gzPath));
        } else {
            $this->line("OK: " . basename($sqlPath));
        }

        // Rotasi
        $days = (int) $this->option('rotate-days');
        if ($days > 0) {
            $this->info("Rotasi file > {$days} hari...");
            $deleted = 0;
            foreach (File::files($dir) as $f) {
                if (now()->diffInDays(\Carbon\Carbon::createFromTimestamp($f->getMTime())) > $days) {
                    File::delete($f->getPathname());
                    $deleted++;
                }
            }
            $this->line("Terhapus: {$deleted} file lama.");
        }

        $this->info('Selesai.');
        return self::SUCCESS;
    }

    private function mysqlDumpCommand(string $sqlPath): Process
    {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', 3306);
        $db   = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD', '');

        // Catatan: mysqldump harus tersedia di PATH (XAMPP/MariaDB/MySQL)
        $cmd = [
            'mysqldump',
            "--host={$host}",
            "--port={$port}",
            "--user={$user}",
            "--password={$pass}",
            '--routines',
            '--events',
            '--single-transaction',
            '--quick',
            $db,
        ];

        return Process::fromShellCommandline(implode(' ', array_map('escapeshellarg', $cmd)) . ' > ' . escapeshellarg($sqlPath));
    }

    private function pgDumpCommand(string $sqlPath): Process
    {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', 5432);
        $db   = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD', '');

        // pg_dump baca password via env PGPASSWORD
        $cmd = [
            'pg_dump',
            "-h", $host,
            "-p", (string)$port,
            "-U", $user,
            $db,
        ];

        $process = new Process($cmd);
        $process->setEnv(['PGPASSWORD' => $pass]);
        // Redirect output ke file
        $process->setCommandLine($process->getCommandLine() . ' > ' . escapeshellarg($sqlPath));
        return $process;
    }

    private function sqliteDump(string $sqlPath): ?Process
    {
        $path = database_path('database.sqlite');
        if (!file_exists($path)) {
            $this->error('database.sqlite tidak ditemukan.');
            return null;
        }
        // backup dengan copy file (aman jika koneksi idle). Untuk aman penuh, bisa pakai VACUUM INTO (SQLite 3.27+)
        File::copy($path, $sqlPath); // hasil bukan .sql, tapi snapshot DB (rename jika mau .sqlite)
        $this->line('SQLite snapshot dibuat: ' . basename($sqlPath));
        return null;
    }
}
