<?php

declare(strict_types=1);

/**
 * Plugin Name: User Directory Plugin
 * Description: Displays users from an external API with AJAX details.
 * Version: 1.0
 * Author: Roman
 * Text Domain: user-directory-plugin
 */

use UsersPlugin\UserDirectory\Controllers\DirectoryEndpointController;
use UsersPlugin\UserDirectory\Controllers\AjaxController;
use UsersPlugin\UserDirectory\Controllers\SettingsController;
use UsersPlugin\UserDirectory\Services\HtmlRenderer;
use UsersPlugin\UserDirectory\Services\CacheService;
use UsersPlugin\UserDirectory\Services\ApiService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

defined('ABSPATH') || exit;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$cache = new CacheService();

$logger = new Logger('user-directory');
$logger->pushHandler(new StreamHandler(WP_CONTENT_DIR . '/logs/user-directory.log', Logger::DEBUG));

$apiService = new ApiService($cache, $logger);

$htmlRenderer = new HtmlRenderer();
$endpointController = new DirectoryEndpointController($apiService, $htmlRenderer);
$ajaxController = new AjaxController($apiService, $htmlRenderer);
$settingsController = new SettingsController();

add_action('init', [$endpointController, 'register_custom_endpoint']);
add_action('template_redirect', [$endpointController, 'render_endpoint']);

add_action('wp_enqueue_scripts', [$ajaxController, 'enqueue_scripts']);
add_action('wp_ajax_fetch_user_details', [$ajaxController, 'handle_ajax_request']);
add_action('wp_ajax_nopriv_fetch_user_details', [$ajaxController, 'handle_ajax_request']);

add_action('admin_menu', [$settingsController, 'register_menu']);
add_action('admin_post_user_directory_save_settings', [$settingsController, 'save_settings']);

define('USER_DIR_PLUGIN_URL', plugin_dir_url(__FILE__));
