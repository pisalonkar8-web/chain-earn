<?php

// Package the current reviewed working tree without secrets or runtime data.
$root = dirname(__DIR__);
chdir($root);
if (! class_exists(ZipArchive::class)) {
    fwrite(STDERR, "PHP ZIP extension is required. Enable extension=zip in the CLI php.ini.\n");
    exit(1);
}
if (! is_file('public/build/manifest.json') || is_file('public/hot')) {
    fwrite(STDERR, "Build assets and stop the Vite development server before packaging.\n");
    exit(1);
}
$process = proc_open(['git', 'ls-files', '--cached', '--others', '--exclude-standard', '-z'],
    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
if (! is_resource($process)) {
    exit(1);
}
fclose($pipes[0]);
$listing = stream_get_contents($pipes[1]);
$error = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
if (proc_close($process) !== 0) {
    fwrite(STDERR, "Could not enumerate repository files.\n");
    exit(1);
}
$rootFiles = ['artisan', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json',
    'vite.config.js', '.env.example', '.env.production.example', '.gitignore', 'README.md', 'AGENTS.md', 'sample'];
$files = array_filter(explode("\0", $listing), static fn ($file) => in_array($file, $rootFiles, true)
    || preg_match('#^(app|config|database|resources|routes|docs)/#', $file)
    || in_array($file, ['bootstrap/app.php', 'bootstrap/providers.php', 'bootstrap/cache/.gitignore'], true)
    || (str_starts_with($file, 'storage/') && str_ends_with($file, '/.gitignore'))
    || (str_starts_with($file, 'public/') && ! preg_match('#^public/(hot|storage|build)(/|$)#', $file)));
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('public/build', FilesystemIterator::SKIP_DOTS)) as $file) {
    if ($file->isFile()) {
        $files[] = str_replace('\\', '/', $file->getPathname());
    }
}
$files = array_unique($files);
sort($files);
if (! is_dir('dist') && ! mkdir('dist', 0750)) {
    throw new RuntimeException('Could not create release directory.');
}
$destination = 'dist/chain-earn-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(3)).'.zip';
$zip = new ZipArchive;
if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
    throw new RuntimeException('Could not create release archive.');
}
try {
    foreach ($files as $file) {
        $resolved = realpath($file);
        if (! $resolved || ! str_starts_with($resolved, $root.DIRECTORY_SEPARATOR) || ! is_file($resolved) || is_link($file)) {
            throw new RuntimeException('Refusing missing or external source: '.$file);
        }
        if (! $zip->addFile($resolved, $file)) {
            throw new RuntimeException('Could not package source: '.$file);
        }
    }
} finally {
    $zip->close();
}
echo "Prepared: $destination\nSHA256: ".hash_file('sha256', $destination)."\n";
echo "Excluded: .env, dependencies, private PDFs, local databases, logs and runtime caches.\n";
echo "Includes current working-tree changes. Install production Composer dependencies on the server.\n";
