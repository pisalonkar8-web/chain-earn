<?php

// Run only against the dedicated disposable database, never the application DB.
require __DIR__.'/../vendor/autoload.php';

$local = Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../.env'));
$environment = array_merge(getenv(), [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => $local['DB_HOST'] ?? '127.0.0.1',
    'DB_PORT' => $local['DB_PORT'] ?? '3306',
    'DB_DATABASE' => 'chain_earn_test',
    'DB_USERNAME' => $local['DB_USERNAME'] ?? 'chain_earn',
    'DB_PASSWORD' => $local['DB_PASSWORD'] ?? '',
    'DB_URL' => '',
]);
if (($local['DB_DATABASE'] ?? '') === $environment['DB_DATABASE']) {
    fwrite(STDERR, "Refusing to test against the configured application database.\n");
    exit(1);
}
$process = proc_open([PHP_BINARY, 'artisan', 'test'], [STDIN, STDOUT, STDERR], $pipes, dirname(__DIR__), $environment);
exit(is_resource($process) ? proc_close($process) : 1);
