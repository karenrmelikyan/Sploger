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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '' );

/** MySQL database username */
define( 'DB_USER', '' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('JWT_AUTH_SECRET_KEY', 'T|MUz|LuA+)A+O1.-N^Ky4Fk318Wc:S:gpJ:O}+FCC$1+aS vS>x`{#|cp7SMOdy');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'GeKjZ]5g]fa7v*;#i/OF-N-cAxt-oUiMe;D{ttWgf=|%7+s^i?(o)M( +ic/*h5?' );
define( 'SECURE_AUTH_KEY',  'gOe?g_1p[d+pibk_pm}Lt|p{@~!t/YW8k-Xdj86(v9k&](g[~aE|R ?-I nTP7;O' );
define( 'LOGGED_IN_KEY',    ';Yu%OUWd89 h+*X/^v#.}+Q?`` fT rD6:}yrxugc>?0$Ps^r2W%).lx.d+{Pa%n' );
define( 'NONCE_KEY',        'b3FfTXY:{?/UFMe@lbwr2>7=nhe<*wJx-iplIvNnQVDYa&Oi`U^((>M1;B:(]f]0' );
define( 'AUTH_SALT',        'H>/QGX$=]XJxLOIf2ho3ELG!c<o/Be_%uRCZQRrdZ,&v<4%:9FFw1|ouWE~f>DO!' );
define( 'SECURE_AUTH_SALT', 'bZ>81NrOm,][j#t>TmHv;N<$4zG[5MhetjCv3G=J:mAR/@Me!`BbRX7,Yf#4]0wl' );
define( 'LOGGED_IN_SALT',   'BcdSwb_-a:>4OWx%!}3$/Mx#l4[WlwJ)^%rf2?-(QKf;(vuT=1hnIsd7rzmtCVRu' );
define( 'NONCE_SALT',       'eA@:zh)&5#wHM<jio>;<,WJodi[YI[]LuKptqX:^Ef/6$jzM4K:4MOy7c%NU&]d{' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define('ABSPATH', __DIR__ . 'wp-config.php/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
