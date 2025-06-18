# User Directory Plugin

A custom WordPress plugin that displays an external list of users in a dynamic, AJAX-enhanced table.

## Features

- Fetches users from https://jsonplaceholder.typicode.com/users
- Caches responses via PSR-6 pool (Redis by default, falls back to Filesystem)
- Displays data on a dynamic, customizable, SEO-friendly frontend endpoint
- AJAX-powered user details (ID, name, email, etc.) with live expand
– Custom admin settings page to manage the endpoint slug (requires permalink flush after changes)
- PHPUnit-based isolated unit tests (without loading WP)
- Supports PSR-4 autoloading with Composer
- CI via GitHub Actions (PHPUnit & PHPCS)
- WP-Stash integration

## Requirements

- PHP 8.4+
- WordPress 6.7+
- Redis (optional, fallback to filesystem cache)
- Composer

### Endpoint

The plugin registers a custom rewrite endpoint like:

```
https://example.com/user-directory/
```

You can configure the slug in WP Admin > Settings > User Directory.

Internally, it avoids conflicts with real pages. The endpoint renders the user table using `HtmlRenderer`.

### Caching

Caching is done through [WP-Stash](https://github.com/inpsyde/wp-stash):

- If Redis is installed, it will use `\Stash\Driver\Redis`
- If Redis is not available, fallback to `FileSystem`

No user request will hit the external API unless the cache expires or is invalid.

### Filters & Hooks

You can extend functionality using WP filters:

```php
add_filter('user_directory_users', function (array $users): array {
    return array_filter($users, fn($user) => $user['id'] < 5);
});
```

```php
add_filter('user_directory_enqueue_assets', function (array $assets): array {
    $assets['style'] = get_stylesheet_directory_uri() . '/custom/user-directory.css';
    return $assets;
});
```

Or disable styles entirely:

```php
add_filter('user_directory_enqueue_assets', fn($assets) => ['style' => false]);
```

### Testing

Tests are written with PHPUnit + Brain Monkey:

```bash
composer test
composer lint
```

Test coverage includes:

- API calls and caching
- HTML renderer output
- Settings page behavior
- AJAX handlers
- Endpoint logic

All tests are isolated (WordPress is never loaded).

## 🧪 CI/CD

GitHub Actions workflow validates every push:

- Runs PHPCS (Syde ruleset)
- Runs PHPUnit tests
- Push only if all checks pass

## 📦 Installation

```bash
git clone <repo>
composer install
npm install
```

Make sure `wp-config.php` includes:

```php
define('WP_STASH_DRIVER', '\Stash\Driver\Redis');
define('WP_STASH_DRIVER_ARGS', json_encode([
    'servers' => [['server' => 'redis', 'port' => 6379]],
]));
```

And MU plugin loader at `wp-content/mu-plugins/load-wp-stash.php`:

```php
<?php

/**
 * Plugin Name: WP-Stash Loader
 */

require_once dirname(__DIR__, 2) . '/vendor/inpsyde/wp-stash/wp-stash.php';
```

## Useful command

composer dump-autoload

## 🪪 License

MIT

---

© Developed for Syde Technical Assignment — 2025
