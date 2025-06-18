<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Services;

final class HtmlRenderer
{
    /**
     * Renders an HTML table of users.
     *
     * @param array<int, array<string, mixed>> $users
     * @return string
     */
    public function renderTable(array $users): string
    {
        ob_start();
        ?>
        <table class="syde-user-api-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="user-row" data-user-id="<?= esc_attr((string) $user['id']) ?>">
                        <td>
                            <a href="#user-details-<?= esc_attr((string) $user['id']) ?>">
                                <?= esc_html((string) $user['id']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="#user-details-<?= esc_attr((string) $user['id']) ?>">
                                <?= esc_html($user['name']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="#user-details-<?= esc_attr((string) $user['id']) ?>">
                                <?= esc_html($user['username']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr class="user-details-row"
                        id="user-details-<?= esc_attr((string) $user['id']) ?>"
                        style="display:none;"
                    >
                        <td colspan="3">
                            <div class="user-details-content"></div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Renders detailed HTML for a single user.
     *
     * @param array<string, mixed> $user
     * @return string
     */
    public function renderUserDetails(array $user): string
    {
        if (empty($user)) {
            return '<div class="user-details-content"><p>User not found.</p></div>';
        }

        $address = $user['address'] ?? [];
        $company = $user['company'] ?? [];

        ob_start();
        ?>
        <div class="user-details-content">
            <p>
                <strong>Email:</strong>
                <?= esc_html($user['email'] ?? '') ?>
            </p>
            <p>
                <strong>Phone:</strong>
                <?= esc_html($user['phone'] ?? '') ?>
            </p>
            <p>
                <strong>Website:</strong>
                <a href="http://<?= esc_attr($user['website'] ?? '') ?>" target="_blank">
                    <?= esc_html($user['website'] ?? '') ?>
                </a>
            </p>
            <p>
                <strong>Address:</strong>
                <?= esc_html(
                    ($address['suite'] ?? '') . ', ' .
                    ($address['street'] ?? '') . ', ' .
                    ($address['city'] ?? '') . ' (' .
                    ($address['zipcode'] ?? '') . ')'
                ) ?>
            </p>
            <p>
                <strong>Company:</strong>
                <?= esc_html($company['name'] ?? '') ?>
                -
                <em><?= esc_html($company['catchPhrase'] ?? '') ?></em>
            </p>
            <?php 
                if (!empty($user['custom_note'])) {
                    echo '<p><strong>Note:</strong> ' . esc_html($user['custom_note']) . '</p>';
                }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
