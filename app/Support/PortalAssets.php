<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Versioned URLs for static assets under /public (portal CSS, etc.).
 * Uses filemtime when the file exists; otherwise APP_ASSET_VERSION so Blade never fatals on missing files.
 */
final class PortalAssets
{
    public static function versionForPublicPath(string $relativeToPublic): string
    {
        $full = public_path($relativeToPublic);
        if (is_file($full)) {
            return (string) filemtime($full);
        }

        return (string) config('app.asset_version', '1');
    }

    public static function publicUrl(string $relativeToPublic): string
    {
        return asset($relativeToPublic) . '?v=' . self::versionForPublicPath($relativeToPublic);
    }

    /**
     * @param  string  $stem  Filename without .css under portal/pages (e.g. "dashboard", "protocol-index")
     */
    public static function pageUrl(string $stem): string
    {
        $relative = 'assets/css/portal/pages/' . $stem . '.css';

        return self::publicUrl($relative);
    }

    /**
     * Scan Blade views for portal asset references (asset(), PortalAssets::pageUrl, publicUrl).
     *
     * @return list<string> Relative paths from project root / public html e.g. assets/css/portal/pages/x.css
     */
    public static function discoverReferencedPublicPaths(): array
    {
        $viewsDir = resource_path('views');
        $paths = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewsDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->getExtension() !== 'php' && ! str_ends_with($fileInfo->getFilename(), '.blade.php')) {
                continue;
            }
            if (! str_ends_with($fileInfo->getFilename(), '.blade.php')) {
                continue;
            }

            $content = file_get_contents($fileInfo->getPathname());
            if ($content === false) {
                continue;
            }

            // PortalAssets::pageUrl('stem') or ::pageUrl("stem")
            if (preg_match_all("/PortalAssets::pageUrl\\(\\s*['\\\"]([^'\\\"]+)['\\\"]\\s*\\)/", $content, $m)) {
                foreach ($m[1] as $stem) {
                    $paths['assets/css/portal/pages/' . $stem . '.css'] = true;
                }
            }

            if (preg_match_all("/PortalAssets::publicUrl\\(\\s*['\\\"]([^'\\\"]+)['\\\"]\\s*\\)/", $content, $m2)) {
                foreach ($m2[1] as $rel) {
                    $paths[$rel] = true;
                }
            }

            // asset('assets/css/portal/...')
            if (preg_match_all("/asset\\(\\s*['\\\"](assets\\/css\\/portal\\/[^'\\\"]+)['\\\"]\\s*\\)/", $content, $m3)) {
                foreach ($m3[1] as $rel) {
                    $paths[$rel] = true;
                }
            }
        }

        $list = array_keys($paths);
        sort($list);

        return $list;
    }

    /**
     * @return array{0: bool, 1: list<string>} [ok, missing paths]
     */
    public static function assertAllReferencedExist(): array
    {
        $missing = [];
        foreach (self::discoverReferencedPublicPaths() as $rel) {
            if (! is_file(public_path($rel))) {
                $missing[] = $rel;
            }
        }

        return [count($missing) === 0, $missing];
    }
}
