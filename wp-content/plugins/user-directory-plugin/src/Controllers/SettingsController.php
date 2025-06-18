<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Controllers;

final class SettingsController
{
    /**
     * Registers the plugin's settings menu under WordPress "Settings".
     *
     * @return void
     */
    public function register_menu(): void
    {
        add_options_page(
            'User Directory Settings',
            'User Directory',
            'manage_options',
            'user-directory-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Outputs the settings page HTML for configuring the endpoint slug.
     *
     * @return void
     */
    public function render_settings_page(): void
    {
        $slug = get_option('user_directory_slug', 'user-directory');
        ?>
        <div class="wrap">
            <h1>User Directory Settings</h1>
            <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
                <input type="hidden" name="action" value="user_directory_save_settings">
                <?php wp_nonce_field('user_directory_settings_nonce') ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="slug">Endpoint Slug</label>
                        </th>
                        <td>
                            <input
                                name="slug"
                                type="text"
                                id="slug"
                                value="<?= esc_attr($slug) ?>"
                                class="regular-text"
                            >
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Settings') ?>
            </form>
        </div>
        <?php
        if (!empty($_GET['error']) && $_GET['error'] === 'page_exists') {
            echo '<div class="notice notice-error"><p>Page already exists.</p></div>';
        }

        if (!empty($_GET['success'])) {
            echo '<div class="notice notice-success"><p>Settings saved successfully.</p></div>';
        }
    }

    /**
     * Handles saving of the endpoint slug setting from the admin form.
     * Performs nonce validation and redirects with appropriate feedback.
     *
     * @return void
     */
    public function save_settings(): void
    {
        check_admin_referer('user_directory_settings_nonce');

        $slug = sanitize_title($_POST['slug'] ?? 'user-directory');

        if (get_page_by_path($slug)) {
            wp_redirect(
                add_query_arg(
                    ['page' => 'user-directory-settings', 'error' => 'page_exists'],
                    admin_url('options-general.php')
                )
            );
            exit;
        }

        update_option('user_directory_slug', $slug);
        update_option('user_directory_flush_needed', true);

        wp_redirect(
            add_query_arg(
                ['page' => 'user-directory-settings', 'success' => '1'],
                admin_url('options-general.php')
            )
        );
        exit;
    }
}
