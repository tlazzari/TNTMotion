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
define( 'DB_NAME', 'i10163827_jhno1' );

/** Database username */
define( 'DB_USER', 'Tom1977' );

/** Database password */
define( 'DB_PASSWORD', 'TNT2024@!' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4_general_ci' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', 'utf8mb4_general_ci' );

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
define('AUTH_KEY',         'NF68iadbUMwZ5JiHS21jf32fD8NcPRZjRox6igUbBfTAr8e410AGEjwnOiN4It2W');
define('SECURE_AUTH_KEY',  'Hxe92StJ9qRU6QavxInleG3ahAr1xswC4rt13N10ZZSbIfqISTjkKufazKK0RUGG');
define('LOGGED_IN_KEY',    'uAcFJK0AbFZuTXJNinVqBdX15lz6oNvOxX7On28mUbFB35IYrXDh5TpaL6r7Eqe3');
define('NONCE_KEY',        'Zr5OXfPKiTUZjIgBNdfzeClX7XKeyD4lcctoyGdZfcbuFtrlQRVDccWPooze1wQV');
define('AUTH_SALT',        'Bhd9tTjhNsW0SDDCafMKtbXxaEzi7q98Yds53wtqeNw0YOCVNY8BXXELBnG55S6l');
define('SECURE_AUTH_SALT', 'QdgJXlU2SGvMKyYUq0ryMwIwffwco0sniSW27FfOiLM4oFaarSMhsqfbHvGOtj6n');
define('LOGGED_IN_SALT',   'WT9uk8TqsiABuc1Uxst8fHoMA9x3G9sMtApQ33TmBjSgBnjKRPMxHWR3ie6CTDsT');
define('NONCE_SALT',       'JJmi9cL0aMhXIh9es3a6rp7sDsdgWiwT8OIY20O56PLbNYymISxGDq30SSuHfazw');

/**
 * Other customizations.
 */
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');


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
$table_prefix = 'cork_';

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
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
