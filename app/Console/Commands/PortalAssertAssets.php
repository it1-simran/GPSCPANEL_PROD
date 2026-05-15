<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\PortalAssets;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class PortalAssertAssets extends Command
{
    protected $signature = 'portal:assert-assets';

    protected $description = 'Verify portal CSS/JS paths referenced from Blade exist under public/';

    public function handle(): int
    {
        [$ok, $missing] = PortalAssets::assertAllReferencedExist();
        if (! $ok) {
            $this->error('Missing portal assets:');
            foreach ($missing as $m) {
                $this->line('  - '.$m);
            }

            return self::FAILURE;
        }

        $bad = $this->findMalformedPortalLinks();
        if ($bad !== []) {
            $this->error('Malformed portal link patterns found:');
            foreach ($bad as $line) {
                $this->line('  - '.$line);
            }

            return self::FAILURE;
        }

        $this->info('All referenced portal assets exist.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function findMalformedPortalLinks(): array
    {
        $viewsDir = resource_path('views');
        $bad = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($viewsDir, \FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if (! str_ends_with($fileInfo->getFilename(), '.blade.php')) {
                continue;
            }
            $content = file_get_contents($fileInfo->getPathname());
            if ($content === false) {
                continue;
            }
            if (str_contains($content, '}}" ?v=') || str_contains($content, '}}" ?v={{')) {
                $bad[] = $fileInfo->getPathname();
            }
        }

        return $bad;
    }
}
