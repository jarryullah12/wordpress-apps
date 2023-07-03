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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'acf' );

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
define( 'AUTH_KEY',         'T/!P2RwlgcF)_*x}hUGEAzv`_vl6t? Lkjs(oB8)< B,%iK3D44TO6idf2.MUQR|' );
define( 'SECURE_AUTH_KEY',  '=I%p#SW~PD(s*T^5C4:7spY1?U-@$Mu|IfiIg ignsDAhon@XP T>s(mo}drgp6N' );
define( 'LOGGED_IN_KEY',    '`)^ep?9)ot8{GtD!7<bJ9b%e|XqBV f 5u=HKgMcJ-,YNBOhB;8H&G$mNsIBicVt' );
define( 'NONCE_KEY',        '] /kgI@!ZTCLk=Q_;8DVm<ERVXGWXN7}le=C3nss}+56]FmcVcv(rdQgjMACHw=k' );
define( 'AUTH_SALT',        'nFN2#u}.j{7E=B)~E3SP1QiLL|ita_bskIVGL#O:+D^o&14PNw5@b%=!2&|5WyA*' );
define( 'SECURE_AUTH_SALT', '&rI*!&18q.r=AlsyX%nQc|@gT%}ZyFH2Y!< %?a,`Ikh{@T,;sZd{/k>LbSpY5.>' );
define( 'LOGGED_IN_SALT',   'P^+dGXyS/6j?OCnalo-Fpdi_}cLT%hTZaR;`yAD>X ~>!u!YlNk.Q_|i,_|3aEZO' );
define( 'NONCE_SALT',       's^cj0?%jR2yCV[{S|::-Hao%vD!HpSK&%N6haUek=7c/f=vG0<L6Y`)FN!CmdUO9' );

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
