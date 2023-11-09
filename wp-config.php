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
define( 'DB_NAME', 'wp-divi-eco' );

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
define( 'AUTH_KEY',         'Dr@.s5*X(+&5(r68&B1SXKhWAK.[;brrd9p8.tT!QLh}d;DzGWeG2quq?>ZQ<Yfu' );
define( 'SECURE_AUTH_KEY',  ':QMfkqf5u<QZ;EL[b[OrFb81)L4VBxIZ(}:SaJ}JVvBNk~[y%]8_5PMMN0.I#7|J' );
define( 'LOGGED_IN_KEY',    ']IMBI/$}IV6[OUJpXcU{d;zrNJ_d%C!*Zt}NoY#wma~#8rAdnq__Sq/|64uXi`$3' );
define( 'NONCE_KEY',        '&DzdpO67>ltE4awxW0rE@=qUzbSm~qK5Qcc`5klG_9T#{C|8|2#R<1p|nh2~4q C' );
define( 'AUTH_SALT',        'oJ4x]qh$KgJ%!Qb.nkeYiJ2.>5!>n)Y1.U*P-b2HRpwMU/*K{Sz9ym_DhD>S5@B`' );
define( 'SECURE_AUTH_SALT', '/ kr:yt7GaViTZ0^U=<J)(R#Nf}!?gJ]$yE63>lElg)dLQ}`Xd792/RZ(wO=%W9?' );
define( 'LOGGED_IN_SALT',   '<Fs> ^(M#[g>yD@E]X9$Ha,5VBW-lW{4!|ea:kRF6*HbExLLrg((ry@]y~k>f;#]' );
define( 'NONCE_SALT',       '}hK).bo{19UP+D3q[r?_BV,[+qp7ILNOPR0rfEPUk C3ia>)n)6x}N0N16`F7Fb5' );

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
