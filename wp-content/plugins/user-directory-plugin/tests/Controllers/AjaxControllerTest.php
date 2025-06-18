<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Tests\Controllers;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use UsersPlugin\UserDirectory\Controllers\AjaxController;
use UsersPlugin\UserDirectory\Interfaces\ApiServiceInterface;
use UsersPlugin\UserDirectory\Services\HtmlRenderer;

final class AjaxControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        if (!defined('WP_CONTENT_DIR')) {
            define('WP_CONTENT_DIR', sys_get_temp_dir());
        }

        if (!defined('USER_DIR_PLUGIN_URL')) {
            define('USER_DIR_PLUGIN_URL',
            'https://example.com/wp-content/plugins/user-directory-plugin/');
        }

        Functions\when('wp_die')->justReturn(null);
        Functions\when('admin_url')->justReturn('https://example.com/wp-admin/admin-ajax.php');

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

    public function test_enqueue_scripts_registers_scripts_and_localizes(): void
    {
        Functions\expect('wp_enqueue_style')->once();
        Functions\expect('wp_enqueue_script')->once();

        Functions\expect('wp_localize_script')
            ->once()
            ->with(
                'user-directory-script',
                'UserDirectory',
                ['ajax_url' => 'https://example.com/wp-admin/admin-ajax.php']
            );

        $controller = new AjaxController(
            $this->createMock(ApiServiceInterface::class),
            new HtmlRenderer()
        );

        $controller->enqueue_scripts();
        $this->addToAssertionCount(1);
    }

    public function test_handle_ajax_request_outputs_user_html(): void
    {
        $_POST['id'] = '5';

        $userData = [
            'id'      => 5,
            'email'   => 'roman@example.com',
            'phone'   => '123-456',
            'website' => 'romanz.dev',
            'address' => [
                'suite'   => 'Apt. 2',
                'street'  => 'Main St',
                'city'    => 'Lviv',
                'zipcode' => '79000',
            ],
            'company' => [
                'name'        => 'Test Inc',
                'catchPhrase' => 'We deliver',
            ],
        ];

        $apiMock = $this->createMock(ApiServiceInterface::class);
        $apiMock->expects($this->once())
            ->method('user')
            ->with(5)
            ->willReturn($userData);

        $controller = new AjaxController($apiMock, new HtmlRenderer());

        ob_start();
        $controller->handle_ajax_request();
        $output = ob_get_clean();

        $this->assertStringContainsString('<div class="user-details-content">', $output);
        $this->assertStringContainsString('roman@example.com', $output);
        $this->assertStringContainsString('romanz.dev', $output);
        $this->addToAssertionCount(1);
    }

    public function test_handle_ajax_request_with_invalid_id(): void
    {
        $_POST['id'] = 'invalid';

        $apiMock = $this->createMock(ApiServiceInterface::class);
        $apiMock->expects($this->never())->method('user');

        $renderer = new HtmlRenderer();

        $controller = new AjaxController($apiMock, $renderer);

        $startLevel = ob_get_level();
        ob_start();
        $controller->handle_ajax_request();
        $output = ob_get_clean();

        while (ob_get_level() > $startLevel) {
            ob_end_clean();
        }

        $this->assertStringContainsString('Invalid user ID.', $output);
        $this->addToAssertionCount(1);
    }
}
