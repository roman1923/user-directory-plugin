<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'syde' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '+lpp:X!<ri-`NI1*&g!z1(,h$;f%#^Iz_XTD%.P#/W!|:<6vYC#@~tn)Od6&y5;]' );
define( 'SECURE_AUTH_KEY',  'qz|D/]kA| kN$2C*ylW*C!6zoCEX~Qkz@KrAPcys*94agjW<C}g;>[m#6TNeTB:B' );
define( 'LOGGED_IN_KEY',    'F!*SG~/HodLo5Y3D%$) sm]3]^@qe:7z5X@)a.)jM8Ra>*v{as3i1BOBK/c|F}*2' );
define( 'NONCE_KEY',        'T5fJs(N!80%(+$PM-w${#T49789sE4azR|a{8/I)d8}#{Z,!p-Y?,Cu0W?RdWUvZ' );
define( 'AUTH_SALT',        '#l0LBl99rG>3Nx%$]lpa}v;SWBxmj6.#Rc/fg{O?(f%F?;&ui:2~ $:wO Tb |;W' );
define( 'SECURE_AUTH_SALT', 'Fltiam}U%tisz?qs!~M7DS*eCiCOO-iNJTcZj>=}5_oJRP$_tpqxn37,xYvh=+%0' );
define( 'LOGGED_IN_SALT',   'siVU0/d,1A(1iJ?<y_I?lZ ye:<.rFal$#LGLb(Yrex$lzgO5-ACAb vM-%oFrnQ' );
define( 'NONCE_SALT',       'qI21sG6+NCtbo}|]EhOmx7;fbC:6~RNX|ZbCE&Wv9)*/j#%z;:`$u$ -P`ajw4kE' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

/** Composer autoload */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/** WP-Stash configuration */
define( 'WP_STASH_DRIVER', '\Stash\Driver\Redis' );
define( 'WP_STASH_DRIVER_ARGS', json_encode( array(
	'servers' => array( array( 'server' => 'redis', 'port' => 6379 ) )
) ) );
