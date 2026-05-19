<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\PortalAssets;
use Tests\TestCase;

class PortalAssetsTest extends TestCase
{
    public function test_portal_assert_assets_command_succeeds(): void
    {
        $this->artisan('portal:assert-assets')
            ->assertExitCode(0);
    }

    public function test_all_discovered_portal_assets_exist_on_disk(): void
    {
        [$ok, $missing] = PortalAssets::assertAllReferencedExist();

        $this->assertTrue($ok, 'Missing portal assets: ' . implode(', ', $missing));
        $this->assertSame([], $missing);
    }

    public function test_page_url_uses_filemtime_when_css_exists(): void
    {
        $stem = 'dashboard';
        $relative = 'assets/css/portal/pages/' . $stem . '.css';
        $this->assertFileExists(public_path($relative));

        $url = PortalAssets::pageUrl($stem);
        $this->assertStringContainsString('assets/css/portal/pages/dashboard.css', $url);
        $this->assertStringContainsString('?v=' . PortalAssets::versionForPublicPath($relative), $url);
    }

    public function test_version_falls_back_to_config_when_file_missing(): void
    {
        $relative = 'assets/css/portal/pages/__nonexistent_test_file__.css';
        $this->assertFileDoesNotExist(public_path($relative));

        $this->assertSame(
            (string) config('app.asset_version', '1'),
            PortalAssets::versionForPublicPath($relative)
        );
    }

    public function test_login_page_includes_portal_stylesheet_link(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('assets/css/portal/pages/', false);
        $response->assertSee('?v=', false);
    }
}
