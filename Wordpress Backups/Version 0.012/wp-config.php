<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'WPCACHEHOME', '/home3/vgutv/public_html/arcane/arcane/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('WP_CACHE', true); //Added by WP-Cache Manager
define('DB_NAME', 'vgutv_wrdp6');

/** MySQL database username */
define('DB_USER', 'vgutv_wrdp6');

/** MySQL database password */
define('DB_PASSWORD', '8VLacuL7Gb6OQBuo');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'RViB(>oevS6lLq:G8-910(h52!in_R)z$LEq)AJG8FN3L_REukq!byQF\`#sc(E_*Mn0_/A');
define('SECURE_AUTH_KEY',  '');
define('LOGGED_IN_KEY',    'q;WUinor!OIX8PCZlk*_zIX1keRH4\`9Vf4Fp#9)AoO2IT6y=Qkz0^cy8!muVsb)F(0Ls@jDR');
define('NONCE_KEY',        '>RSQ_Ct-m86Q7-aO_CDoocZNBvRXPY?n^V9|pChcmea)Yu;CO;#?rT@lus:TeF$u');
define('AUTH_SALT',        'Uk_bkSqnf8/Y3Iy5CF6XxiT$P4X2O$<_*nFwZQMV-\`FZM7I6a#1H3|xD)a@:bZt3N8tIz$c|W_Y');
define('SECURE_AUTH_SALT', 'tT(Eadg^F\`AtGIFBJ7E=l(:o#xzdaJjukYmv@jUkHkvVkKVt9@l<VZ=ul4e>Qh~BlMi3s-\`m@S9Q');
define('LOGGED_IN_SALT',   ')UHbP(UMqDTOpWMA)x)ESDtSJQ4gYxAiUtXY8T:On;m0UG/^jq_@EsPpb=x~)f6aK4XJ??gZ1O_cW');
define('NONCE_SALT',       'xImMWB9-58B)xfe$_~_x$7x<n>GZH<Qr5~NrWC4>WDIr6Jl2R:)J5^CEo)c$~vsedd!Dd9a<~5V|');

/**#@-*/
define('AUTOSAVE_INTERVAL', 600 );
define('WP_POST_REVISIONS', 1);
define( 'WP_CRON_LOCK_TIMEOUT', 120 );
define( 'WP_AUTO_UPDATE_CORE', true );
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
add_filter( 'auto_update_plugin', '__return_true' );
add_filter( 'auto_update_theme', '__return_true' );
