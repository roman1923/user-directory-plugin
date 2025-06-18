<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Tests\Services;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use UsersPlugin\UserDirectory\Services\HtmlRenderer;

class HtmlRendererTest extends TestCase
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

    public function test_render_table_with_romanzhurakivskyi_users(): void
    {
        $renderer = new HtmlRenderer();

        $users = [
            ['id' => 1, 'name' => 'Roman', 'username' => 'romanz'],
            ['id' => 2, 'name' => 'Yuliia', 'username' => 'yuliiaz'],
        ];

        $output = $renderer->renderTable($users);

        $this->assertMatchesRegularExpression('/<a href="#user-details-1">\s*1\s*<\/a>/', $output);
        $this->assertMatchesRegularExpression(
            '/<a href="#user-details-1">\s*Roman\s*<\/a>/',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/<a href="#user-details-1">\s*romanz\s*<\/a>/',
            $output
        );

        $this->assertMatchesRegularExpression('/<a href="#user-details-2">\s*2\s*<\/a>/', $output);
        $this->assertMatchesRegularExpression(
            '/<a href="#user-details-2">\s*Yuliia\s*<\/a>/',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/<a href="#user-details-2">\s*yuliiaz\s*<\/a>/',
            $output
        );

        $this->assertStringContainsString('<table class="syde-user-api-table">', $output);
        $this->assertStringContainsString('<tr class="user-row" data-user-id="1">', $output);
    }

    public function test_render_table_with_empty_array(): void
    {
        $renderer = new HtmlRenderer();
        $output = $renderer->renderTable([]);

        $this->assertStringContainsString('<table class="syde-user-api-table">', $output);
        $this->assertStringNotContainsString('<tr class="user-row"', $output);
    }

    public function test_render_user_details_full_block(): void
    {
        $renderer = new HtmlRenderer();
        $user = [
            'email' => 'r@example.com',
            'phone' => '123-456',
            'website' => 'romanz.dev',
            'address' => [
                'suite' => 'Apt. 1',
                'street' => 'Main St',
                'city' => 'Kyiv',
                'zipcode' => '01001',
            ],
            'company' => [
                'name' => 'Syde Corp',
                'catchPhrase' => 'Building future',
            ],
        ];

        $html = $renderer->renderUserDetails($user);

        $this->assertMatchesRegularExpression('/<strong>Email:<\/strong>\s*r@example\.com/', $html);
        $this->assertMatchesRegularExpression('/<strong>Phone:<\/strong>\s*123-456/', $html);
        $this->assertMatchesRegularExpression(
            '/<a href="http:\/\/romanz\.dev" target="_blank">\s*romanz\.dev\s*<\/a>/',
            $html
        );
        $this->assertMatchesRegularExpression('/Apt\. 1,\s*Main St,\s*Kyiv\s*\(01001\)/', $html);
        $this->assertMatchesRegularExpression('/Syde Corp\s*-\s*<em>Building future<\/em>/', $html);
    }

    public function test_render_user_details_empty(): void
    {
        $renderer = new HtmlRenderer();
        $html = $renderer->renderUserDetails([]);

        $this->assertStringContainsString('User not found', $html);
    }
}
