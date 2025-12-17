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
define( 'DB_NAME', 'portl_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );	//papai2297

/** Database hostname */
define( 'DB_HOST', 'localhost' );	//127.0.0.1

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
define( 'AUTH_KEY',         '/VV 9kAT`tI~/a&Rp@{;##<7Y%$p~)Q|B[9v~E`V$82tn(dcmE{}zGo5KJ17W c>' );
define( 'SECURE_AUTH_KEY',  '&zc9]-_R_M{/&C?Oaw`}zZ,M ;LN~qf1U.tiN*J%JcR]6SbC$Uko17(05EuyeN2%' );
define( 'LOGGED_IN_KEY',    'eV1HvWG&Lh5DZ(sMT~CmGB`4s7027,5]pVE=h68D0sC]!YU|dxH&riR~H${8o,-*' );
define( 'NONCE_KEY',        'Oqc ,e&^nX>LL{,~Ny9<[,v`I9Y7`2jzxCh1y)gmVd`@|dJ;?vH*D^4@fp/dA`$!' );
define( 'AUTH_SALT',        ']fb(Qx?$NsjY^Y;~mE5~V[12w`hCO-ad2|U^~Ea8|FrJ~vh3C[PC8i3o,&`nA=OK' );
define( 'SECURE_AUTH_SALT', 'YWq5_soWG2OMbbLxqc2w/1 7AjV8rL{O#XDWDts;ivn=(}Y>]/Jp!)-q] [TwRFv' );
define( 'LOGGED_IN_SALT',   '+b+fgt}eR~o?TwBdk]g47tV30`.jc&o!s)@qkH^y{=f4}%=$,$rf%4<p-6F(YA;0' );
define( 'NONCE_SALT',       '[)$7X(OxW|*X |=xh%z?UZ*! IWOYe;0.NlgT6Qa$m{.x|KLW0rq>Jm5LL>a4zti' );

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

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
