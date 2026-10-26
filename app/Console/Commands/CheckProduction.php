<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckProduction extends Command
{
    protected $signature = 'chain:check-production';

    protected $description = 'Read-only production configuration checks; does not send mail or change data';

    public function handle(): int
    {
        $checks = [
            'Production environment' => app()->environment('production'),
            'Debug disabled' => ! config('app.debug'),
            'Application key configured' => filled(config('app.key')),
            'HTTPS application URL' => str_starts_with((string) config('app.url'), 'https://'),
            'Secure session cookie' => config('session.secure') === true,
            'Database sessions enabled' => config('session.driver') === 'database',
            'MySQL selected' => config('database.default') === 'mysql',
            'SMTP mail selected' => config('mail.default') === 'smtp',
            'SMTP host configured' => filled(config('mail.mailers.smtp.host')) && ! in_array(config('mail.mailers.smtp.host'), ['localhost', '127.0.0.1']),
            'Sender address configured' => filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL) !== false
                && ! str_ends_with((string) config('mail.from.address'), '@example.com'),
            'Compiled assets available' => is_file(public_path('build/manifest.json')),
            'Vite development marker absent' => ! is_file(public_path('hot')),
            'Storage writable' => is_writable(storage_path()) && is_writable(storage_path('framework/views'))
                && is_writable(storage_path('framework/sessions')) && is_writable(storage_path('logs')),
            'Bootstrap cache writable' => is_writable(base_path('bootstrap/cache')),
        ];

        try {
            DB::select('SELECT 1');
            $checks['Database reachable'] = true;
            $required = ['users', 'profiles', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks'];
            $checks['Required tables present'] = collect($required)->every(fn ($table) => Schema::hasTable($table));
            $pending = app('migrator')->getMigrationFiles(database_path('migrations'));
            $ran = Schema::hasTable('migrations') ? app('migration.repository')->getRan() : [];
            $checks['Migrations applied'] = count(array_diff(array_keys($pending), $ran)) === 0;
        } catch (\Throwable) {
            // Avoid printing exception details that may include account information.
            $checks['Database reachable and migrations checked'] = false;
        }

        $this->table(['Check', 'Result'], collect($checks)->map(fn ($ok, $label) => [$label, $ok ? 'PASS' : 'FAIL'])->all());
        $this->warn('These checks do not verify DNS, certificates, document root, SMTP delivery, backups, browser behavior, or payment readiness.');

        return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
    }
}
