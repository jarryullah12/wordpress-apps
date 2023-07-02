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
define( 'DB_NAME', 'wordpress_plugin' );

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
define( 'AUTH_KEY',         '@G3?;_42bH>fW]r}7_#!ouSanop@9)SBamwN(5+,:3Tr!zMM/+/*Si`qG3_OIsBM' );
define( 'SECURE_AUTH_KEY',  'a8R7|hf^UWK,ly7cb&#5L+<FRjh!b]2>s9xP9 ((z z`lHat|z.dsPz|3U1eXR60' );
define( 'LOGGED_IN_KEY',    'pMf-8I_&Mi5Xed3+[u/gAv6Y$XwYWuhXBZn-s(nI |:Os_pEf@yskHN8%5v}YwT6' );
define( 'NONCE_KEY',        '~8u$gHI49+uB<~>W5uR`spF2hP2A.<&!}|FsaUoF<PRT;^4chtOq3h&M=)M]vQKY' );
define( 'AUTH_SALT',        'A0+`v6|4S`2|H<pF#-wX/AXR+~6.b*g:zIS4@{&-j}q^y%t+(>0c}suF3nI5a>^~' );
define( 'SECURE_AUTH_SALT', 'u^.N1|+zM:f<q,(UbIh.t}~>(f8*%r1lWJ>@+O*XRJ&vzA<>/Sem9u}wr;g:YRia' );
define( 'LOGGED_IN_SALT',   'e*$lDIr s4.IuF)W3N*aKzN@<Uok/q,<Ww76C2@Cq7b$0=;~>2-GFHj:yEbp;pPL' );
define( 'NONCE_SALT',       '(3&E!cCS-Z.;XHCX%3Pl]#L~Q?&KQ.@6`+Dr#s&0@cIa92qUi%_`y,AK4RC!Nm,%' );

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
