<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../';

require_once ($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv();
$dotenv->load($rootDir . '.env');

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', getenv('WP_DB_NAME') );

/** MySQL database username */
define( 'DB_USER', getenv('WP_DB_USER') );

/** MySQL database password */
define( 'DB_PASSWORD', getenv('WP_DB_PASSWORD') );

/** MySQL hostname */
define( 'DB_HOST', getenv('WP_DB_HOST') );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', getenv('WP_DB_CHARSET') );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', getenv('WP_DB_COLLATE') );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          getenv('WP_AUTH_KEY'));
define( 'SECURE_AUTH_KEY',   getenv('WP_SECURE_AUTH_KEY'));
define( 'LOGGED_IN_KEY',     getenv('WP_LOGGED_IN_KEY'));
define( 'NONCE_KEY',         getenv('WP_NONCE_KEY'));
define( 'AUTH_SALT',         getenv('WP_AUTH_SALT'));
define( 'SECURE_AUTH_SALT',  getenv('WP_SECURE_AUTH_SALT'));
define( 'LOGGED_IN_SALT',    getenv('WP_LOGGED_IN_SALT'));
define( 'NONCE_SALT',        getenv('WP_NONCE_SALT'));
define( 'WP_CACHE_KEY_SALT', getenv('WP_WP_CACHE_KEY_SALT'));

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = getenv('WP_TABLE_PREFIX');




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
