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
define( 'DB_NAME', 'wp-ele' );

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
define( 'AUTH_KEY',         'Ctpu@*ixnl*306#lZr1DjcStqb Nn(U_,N!3XA8M&}%%T&7x@d](i(}HO%daayH+' );
define( 'SECURE_AUTH_KEY',  'my8oD!fdc%|CGj+=xJs$[#<3bJ0`b&)F.P7^G|V;j|R<!xA.2S<pLPZr^Lq>+F;Q' );
define( 'LOGGED_IN_KEY',    'WQl@1JK~eNCY,mgKBN6%sksWb*C)$P=eJg3:F+-Cf]zTQlh%`g+:Wa~,uh,f!H{Q' );
define( 'NONCE_KEY',        'FL3^3C!Hg9GX02Hlfw R z*.dsYeQ3$@@XXOenoTC!i1y2W4C4D),Ih5Di]L-FNh' );
define( 'AUTH_SALT',        'T6UJvVLV`RUQ.z~j-|%=$|V,n]BFx&|H.60UTv&p[w{)W.efe!iAxJz7^Pfj+[j ' );
define( 'SECURE_AUTH_SALT', 'm1qsJOTq5^]&zYO:h}n2s!d# **#-9M)hrVhn1k@fSZn3t@V5}Z}<D;j7)cBEVD:' );
define( 'LOGGED_IN_SALT',   'dyvCe2@/N!.`Tjv]q~YQuyra3*U6!dWipZX;jY;DuA;M>F6i%|]+Tq)!v/8YP3B^' );
define( 'NONCE_SALT',       'G~MvO|eb->K-M/V3a}fK?7XF|<3E]{)=T1]JfMCb%:>[$q)%/Vi6Z<J#8Y9hEXkf' );

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
