<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Controllers;

use UsersPlugin\UserDirectory\Interfaces\ApiServiceInterface;
use UsersPlugin\UserDirectory\Services\HtmlRenderer;

final class AjaxController
{
    private ApiServiceInterface $apiService;
    private HtmlRenderer $renderer;

    public function __construct(ApiServiceInterface $apiService, HtmlRenderer $renderer)
    {
        $this->apiService = $apiService;
        $this->renderer = $renderer;
    }

    /**
     * Enqueues frontend assets for the user directory.
     *
     * @return void
     */
    public function enqueue_scripts(): void
    {
        $assets = apply_filters('user_directory_enqueue_assets', [
            'style' => true,
        ]);

        if (!empty($assets['style'])) {
            $styleSrc = is_string($assets['style'])
                ? $assets['style']
                : USER_DIR_PLUGIN_URL . 'dist/style.min.css';

            wp_enqueue_style('user-directory-style', $styleSrc, [], '1.0');
        }

        wp_enqueue_script(
            'user-directory-script',
            USER_DIR_PLUGIN_URL . 'dist/script.min.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('user-directory-script', 'UserDirectory', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Handles the AJAX request to fetch and render user details.
     *
     * @return void
     */
    public function handle_ajax_request(): void
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            echo '<p>Invalid user ID.</p>';
            wp_die();
            return; 
        }


        $user = $this->apiService->user($id);
        echo $this->renderer->renderUserDetails($user);
        wp_die();
    }
}
