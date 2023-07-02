<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'woocom_theme' );

/** Database username */
define( 'DB_USER', 'root' );


/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         '~j}HqJ1lP9ug^/+,&Q/S=&9@IROx}e)XGQgD55IiGEibkxa/$i%Z:&dSfGPNZoWT' );
define( 'SECURE_AUTH_KEY',  '(WuIh47pT0Mj,C`[jA[I`6,I^z[gl@PU$R!Dz)leui5-f]d|2JP8-DjV>G5%F{St' );
define( 'LOGGED_IN_KEY',    '7jV7mtB#dQ.b%AiLecmb(fAZV<M)QfM1[(|.HdU WTl?6y,<l{)d5?![HX[R)Nj{' );
define( 'NONCE_KEY',        ' !XS2@q iH|mOCekVJ|.S<::kkA/?UEu1MG)l)K{blQlB+O D(]WDz~~uLq(w|rp' );
define( 'AUTH_SALT',        '1q_1sdh53xZf![^<R&k)tx+#_wc7(9GI4z@_o,GnENZ9Kq)ctP$_./X ej[;2>KL' );
define( 'SECURE_AUTH_SALT', '^6_&JKgZ0k^k:L3.;P-Lll2{9K$X_=w]Daf<~%q8}f;qbcNt`f{5z^G>q&OxfFfy' );
define( 'LOGGED_IN_SALT',   '4ca(q*)IJ*aQNbNJfWM&O~r~~^j5QV?JLMjc_rfWU6Ewj:<$d6aj<+uU5~>:;BC*' );
define( 'NONCE_SALT',       'aW5W#v^h{rt_xkEeegLljB1K2 Oc?54W&?QW-Ci5h?l)} mtZC6b3$?Cxsg`wET~' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
