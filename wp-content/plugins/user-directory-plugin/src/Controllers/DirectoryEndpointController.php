<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Controllers;

use UsersPlugin\UserDirectory\Interfaces\ApiServiceInterface;
use UsersPlugin\UserDirectory\Services\HtmlRenderer;

final class DirectoryEndpointController
{
    private ApiServiceInterface $apiService;
    private HtmlRenderer $renderer;

    public function __construct(ApiServiceInterface $apiService, HtmlRenderer $renderer)
    {
        $this->apiService = $apiService;
        $this->renderer = $renderer;
    }

    /**
     * Registers a custom rewrite rule and tag for the user directory endpoint.
     *
     * @return void
     */
    public function register_custom_endpoint(): void
    {
        $slug = get_option('user_directory_slug', 'user-directory');

        if (get_page_by_path($slug)) {
            // Prevent conflict with existing page
            return;
        }

        add_rewrite_rule("^{$slug}/?$", 'index.php?user_directory=1', 'top');
        add_rewrite_tag('%user_directory%', '([0-9]+)');
    }

    /**
     * Renders the user directory table on the custom endpoint.
     *
     * @return void
     */
    public function render_endpoint(): void
    {
        if (get_query_var('user_directory') !== '1') {
            return;
        }

        $users = $this->apiService->users();
        $table = $this->renderer->renderTable($users);

        wp_head();
        echo '<main class="wrap">';
        echo $table;
        echo '</main>';
        wp_footer();

        exit;
    }
}
