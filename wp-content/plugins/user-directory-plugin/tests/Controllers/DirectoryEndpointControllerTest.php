<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Tests\Controllers;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use UsersPlugin\UserDirectory\Controllers\DirectoryEndpointController;
use UsersPlugin\UserDirectory\Interfaces\ApiServiceInterface;
use UsersPlugin\UserDirectory\Services\HtmlRenderer;

final class DirectoryEndpointControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        if (!defined('WP_CONTENT_DIR')) {
            define('WP_CONTENT_DIR', sys_get_temp_dir());
        }

        Functions\when('esc_attr')->alias(
            static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES)
        );

        Functions\when('esc_html')->alias(
            static fn(string $value): string => htmlspecialchars($value)
        );
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_register_custom_endpoint_adds_rewrite_when_page_does_not_exist(): void
    {
        Functions\when('get_option')->justReturn('user-directory');
        Functions\when('get_page_by_path')->justReturn(null);

        Functions\expect('add_rewrite_rule')
            ->once()
            ->with('^user-directory/?$', 'index.php?user_directory=1', 'top');

        Functions\expect('add_rewrite_tag')
            ->once()
            ->with('%user_directory%', '([0-9]+)');

        $controller = new DirectoryEndpointController(
            $this->createMock(ApiServiceInterface::class),
            new HtmlRenderer()
        );

        $controller->register_custom_endpoint();

        $this->addToAssertionCount(1);
    }

    public function test_register_custom_endpoint_does_nothing_if_page_exists(): void
    {
        Functions\when('get_option')->justReturn('user-directory');
        Functions\when('get_page_by_path')->justReturn(['ID' => 123]);

        Functions\expect('add_rewrite_rule')->never();
        Functions\expect('add_rewrite_tag')->never();

        $controller = new DirectoryEndpointController(
            $this->createMock(ApiServiceInterface::class),
            new HtmlRenderer()
        );

        $controller->register_custom_endpoint();

        $this->addToAssertionCount(1);
    }

    public function test_render_endpoint_outputs_html_and_calls_services(): void
    {
        Functions\when('get_query_var')->justReturn('1');
        Functions\when('wp_head')->justReturn(null);
        Functions\when('wp_footer')->justReturn(null);
        Functions\when('exit')->justReturn(null);

        $fakeUsers = [
            ['id' => 1, 'name' => 'Roman', 'username' => 'romanz'],
        ];

        $apiMock = $this->createMock(ApiServiceInterface::class);
        $apiMock->expects($this->once())
            ->method('users')
            ->willReturn($fakeUsers);

        $controller = new DirectoryEndpointController($apiMock, new HtmlRenderer());

        $startLevel = ob_get_level();
        ob_start();
        $controller->render_endpoint();
        $output = ob_get_clean();

        while (ob_get_level() > $startLevel) {
            ob_end_clean();
        }

        $this->assertStringContainsString('<main class="wrap">', $output);
        $this->assertStringContainsString('romanz', $output);
        $this->addToAssertionCount(1);
    }

    public function test_render_endpoint_exits_early_if_query_var_missing(): void
    {
        Functions\when('get_query_var')->justReturn('0');

        $apiMock = $this->createMock(ApiServiceInterface::class);
        $apiMock->expects($this->never())->method('users');

        $controller = new DirectoryEndpointController($apiMock, new HtmlRenderer());

        ob_start();
        $controller->render_endpoint();
        $output = ob_get_clean();

        $this->assertSame('', trim($output));
        $this->addToAssertionCount(1);
    }
}
