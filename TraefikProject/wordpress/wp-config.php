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
define( 'DB_NAME', 'wordpress_db' );

/** Database username */
define( 'DB_USER', 'wordpress_user' );

/** Database password */
define( 'DB_PASSWORD', 'wordpress_password' );

/** Database hostname */
define( 'DB_HOST', 'db' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define('AUTH_KEY',         '4TD}^-l6&>IsMILIoWA4f6xRUQ ]!,;-vycL%R5#ls^#|cTh]{?&n/J|58|XbIVP');
define('SECURE_AUTH_KEY',  '1z0{]Th5DR>O(+Y>31bc$3u{TEJLg0Mp-_U}dfh;d9Ai|Rj{&W^:X[!vR)# `|t)');
define('LOGGED_IN_KEY',    '%?i`O2Oj|:[eU9u,/myPj?xxvYQ2h]R5>>e|{hEEJqsr+UDd!KsBjx!F[M@0(RfO');
define('NONCE_KEY',        'N3.}]1oM2(f!a9OkIHwaN -aoO>(jf@;NC+-T )8jX)hX-`K+*w?{$HS{34y[a)/');
define('AUTH_SALT',        'FCjMOiy{Ew<?5.$0+ux|<y`(H12|M|M>Ra0(^Qmy[P2a|m$^;h#iQ,G( m-k/g0g');
define('SECURE_AUTH_SALT', '~bi5)|%&FxOt1el%qhsCLuj`b</dQLd.2n]:? _a|TsizPVR![j/$EXg.l*AhHi6');
define('LOGGED_IN_SALT',   'r9n$;.K(LQ^D.*h+i1O%YHb=zAkIAwqwJTFUO3=0:X%_U?XB^23V6NTC-teOqmDj');
define('NONCE_SALT',       'S3|5|PqiOHJst)3+ggl$vwz`?Im)6fv<UA8[)6?*E/o)=!W?MG@P^AM;7@-rI(x0');

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

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

define( 'WP_HOME', 'http://localhost:8081' );
define( 'WP_SITEURL', 'http://localhost:8081' );

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

