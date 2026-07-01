<?php

// 1. Bootstrap the Laravel Application context
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 2. Import the Laravel Facades
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Organizes files in a direct local path by grouping them into folders based on their prefix.
 *
 * @param string $directoryPath The direct path to the folder (e.g., 'scripts/tests' or base_path('scripts/tests'))
 * @return void
 */
function organizeDirectoryByPrefix(string $directoryPath): void
{
    // 1. Ensure the directory exists
    if (!File::isDirectory($directoryPath)) {
        return;
    }

    // 2. Get only the files inside the directory (excludes subdirectories)
    $files = File::files($directoryPath);

    foreach ($files as $file) {
        // Get the file name (e.g., "word-etc.txt")
        $fileName = $file->getFilename();

        // 3. Skip if the file name does not contain a hyphen
        if (!Str::contains($fileName, '-')) {
            continue;
        }

        // 4. Extract the word before the hyphen
        $prefixDirName = Str::before($fileName, '-');

        // 5. Define the target directory path: scripts/tests/word
        $targetDir = $directoryPath . DIRECTORY_SEPARATOR . $prefixDirName;

        // 6. Ensure the new folder exists
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // 7. Move the file: scripts/tests/word/word-etc.txt
        $newPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        File::move($file->getRealPath(), $newPath);
    }
}


$dir = __DIR__ . DIRECTORY_SEPARATOR . 'endpoint' . DIRECTORY_SEPARATOR . 'appointment';

if (function_exists('organizeDirectoryByPrefix')) {
    organizeDirectoryByPrefix($dir);
}
