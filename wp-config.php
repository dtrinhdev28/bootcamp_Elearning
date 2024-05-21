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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cms_bootcamplearning' );
// define( 'DB_NAME', 'elearn' );

/** Database username */
// define( 'DB_USER', 'webmaster' );
define( 'DB_USER', 'root' );

/** Database password */
// define( 'DB_PASSWORD', 'WcCp}H;_dr1o' );
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
define( 'AUTH_KEY',         'n*cTFX*z2j{tmC-hxtWN-md_)8a=Ibjg.Vm*^-4h*1/0pxULxbb>{OUxP7vjh72f' );
define( 'SECURE_AUTH_KEY',  'iv2|$Dd[++V{5)_[:JaCJ.@!I[JLOss8z{c2Zgr3DrAJ$CMUE-cP9tj`ksvwoO#0' );
define( 'LOGGED_IN_KEY',    '>Or&/0,=~L8d[DQAl]kUJmv&v+v$=p&n4nX$S@-Cd@5mrY](rP5N$uV?`9}o*5?Z' );
define( 'NONCE_KEY',        'GN~^9 IfRn6v8yb lM9!+V8+a]FOO=xo`I4X[e+YBuX5x7o7:vSI//!^Z)hjV.:q' );
define( 'AUTH_SALT',        'tq@OA,Q}j$g#h:Zg=~]7^ZnM(:RF|AMH#xTL@0x^>9Gy,BhhSSw:?lmUq92x*k[P' );
define( 'SECURE_AUTH_SALT', 'GZGr1M@nlPnmy8dw{<0z-{SgOgI8B{6@cGa|<u!qa*G(tSXti:,?Wh-[7VKno7%{' );
define( 'LOGGED_IN_SALT',   'RU#e>-O3[8Qg~uvg}!}VB~_e/|e!Z)D$ &?~B-CAY9UxG~>Ati-()EGi.?W$N(6?' );
define( 'NONCE_SALT',       'qgmN2f#Wd L}Ca)_}bH}&m-~,tt0J&{O0tiM..oJX)-C4xB+-8FT+!DIm8I%wIRc' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'cms_';

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
