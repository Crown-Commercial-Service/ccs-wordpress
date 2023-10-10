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
$dotenv = new Dotenv(true);
$dotenv->load($rootDir . '.env');

// whether or not to use S3 storage for WordPress media
define('S3_UPLOADS_USE_INSTANCE_PROFILE', getenv('AS3CF_ENABLED') );

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
 * For developers: WordPress debugging mode.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
$debug = (getenv('WP_DEBUG') == 1) ? true : false;
define('WP_DEBUG', $debug);

/**
 * Site URL
 */
$host = getenv('WP_SITEURL');
$ssl = (getenv('WP_SSL') == 1) ? true : false;
$protocol = ($ssl) ? 'https://' : 'http://';
if (empty($host)) {
    throw new \Exception("Cannot detect hostname via env var WP_SITEURL");
}
define( 'WP_SITEURL', $protocol . rtrim($host, '/'));
define('WP_HOME', WP_SITEURL);
define( 'FORCE_SSL_ADMIN', $ssl );

// Ensure SSL works when HTTPS is terminated at load balancer
if (strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
    $_SERVER['HTTPS']='on';
}


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

//Disable File Edits
define('DISALLOW_FILE_EDIT', true);

// Disable plugin installation (requires ENV)

$disable_file_mods = (getenv('WP_DISABLE_FILE_MODS') == 1) ? true : false;
define('DISALLOW_FILE_MODS', $disable_file_mods);
