<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Tests\Controllers;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use UsersPlugin\UserDirectory\Controllers\SettingsController;

final class SettingsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
    }

    public function test_register_menu_adds_options_page(): void
    {
        Functions\expect('add_options_page')
            ->once()
            ->withArgs(static function (
                string $title,
                string $menu,
                string $cap,
                string $slug,
                callable $callback
            ): bool {
                return $title === 'User Directory Settings'
                    && $menu === 'User Directory'
                    && $cap === 'manage_options'
                    && $slug === 'user-directory-settings'
                    && is_array($callback)
                    && is_object($callback[0])
                    && $callback[1] === 'render_settings_page';
            });

        $controller = new SettingsController();
        $controller->register_menu();

        $this->addToAssertionCount(1);
    }

    public function test_save_settings_successful(): void
    {
        $_POST['slug'] = 'custom-endpoint';

        Functions\expect('check_admin_referer')
            ->once()
            ->with('user_directory_settings_nonce');

        Functions\when('sanitize_title')->alias(
            static fn(string $slug): string => $slug
        );

        Functions\when('get_page_by_path')->justReturn(null);

        Functions\expect('update_option')
            ->once()
            ->with('user_directory_slug', 'custom-endpoint');
        Functions\expect('update_option')
            ->once()
            ->with('user_directory_flush_needed', true);

        Functions\expect('admin_url')
            ->once()
            ->with('options-general.php')
            ->andReturn('https://example.com/wp-admin/options-general.php');

        Functions\expect('add_query_arg')
            ->once()
            ->with([
                'page'    => 'user-directory-settings',
                'success' => '1',
            ], 'https://example.com/wp-admin/options-general.php')
            ->andReturn('https://example.com/wp-admin/options-general.php?page=user-directory-settings&success=1');

        Functions\expect('wp_redirect')
            ->once()
            ->with('https://example.com/wp-admin/options-general.php?page=user-directory-settings&success=1');

        Functions\when('exit')->justReturn(null);

        $controller = new SettingsController();
        $controller->save_settings();

        $this->addToAssertionCount(1);
    }

    public function test_save_settings_page_already_exists(): void
    {
        $_POST['slug'] = 'existing-page';

        Functions\expect('check_admin_referer')
            ->once()
            ->with('user_directory_settings_nonce');

        Functions\when('sanitize_title')->alias(
            static fn(string $slug): string => $slug
        );

        Functions\when('get_page_by_path')->justReturn(['ID' => 123]);

        Functions\expect('admin_url')
            ->twice()
            ->with('options-general.php')
            ->andReturn('https://example.com/wp-admin/options-general.php');

        Functions\expect('add_query_arg')
            ->once()
            ->with([
                'page'  => 'user-directory-settings',
                'error' => 'page_exists',
            ], 'https://example.com/wp-admin/options-general.php')
            ->andReturn('https://example.com/wp-admin/options-general.php?page=user-directory-settings&error=page_exists');

        Functions\expect('wp_redirect')
            ->once()
            ->with('https://example.com/wp-admin/options-general.php?page=user-directory-settings&error=page_exists');

        Functions\when('update_option')->justReturn(true);
        Functions\when('exit')->justReturn(null);

        $controller = new SettingsController();
        $controller->save_settings();

        $this->addToAssertionCount(1);
    }
}
