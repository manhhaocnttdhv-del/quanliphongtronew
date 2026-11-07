<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizeRentalDocuments extends Command
{
    protected $signature = 'documents:privatize';

    protected $description = 'Move existing identity documents and signatures out of public storage.';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('private');
        $count = 0;
        foreach (['tenants/cccd', 'contracts/signatures'] as $directory) {
            foreach ($public->allFiles($directory) as $path) {
                if (str_starts_with(basename($path), '.')) {
                    continue;
                }
                $contents = $public->get($path);
                if ($private->exists($path) && hash('sha256', $private->get($path)) !== hash('sha256', $contents)) {
                    $this->error('Conflicting private document: '.$path);

                    return self::FAILURE;
                }
                $private->put($path, $contents);
                if (hash('sha256', $private->get($path)) !== hash('sha256', $contents)) {
                    $this->error('Document verification failed: '.$path);

                    return self::FAILURE;
                }
                if (! $public->delete($path)) {
                    $this->error('Could not remove public document: '.$path);

                    return self::FAILURE;
                }
                $count++;
            }
        }
        $this->info("Moved {$count} documents to private storage.");

        return self::SUCCESS;
    }
}
