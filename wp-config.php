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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'vapemarket');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '>*n2+pz1h6P*m$oiI+3i (4-AKvHx7KX$_{qu_jgweG.8lCJgRnal#Q~4eb#@H,o');
define('SECURE_AUTH_KEY',  'G]hE#Z]P/bdgYc&?ddRh(3Oi4I;}G]lL-QA3!;RJX{]+KS)J%9{[ss&*jCSSgD+n');
define('LOGGED_IN_KEY',    '8LI*|&#=Jb0D/.zK-[4jr76-b=[m&{wsU%8&`hQ-]uTtR/h)4<Fp)iRo?ad_CS9$');
define('NONCE_KEY',        'n;14*3,]c*_Ic(sI|.4e=u?:YDW&u;T44GHtduMxk#0JycViSP:ZE$Lg{fa+KP U');
define('AUTH_SALT',        'Cr;ako b;]*g?6HRwk?Pte{q^eC7}O>SeXgw-^ZDWb3q4uM*}#4{DKx}u&BPfw|:');
define('SECURE_AUTH_SALT', 'UQq`}Qs&eTO32bqnjDO#p`Hmw)KQ2>eMD|<ME0qA)dip./.kk*|^[ZC[vOK)50o-');
define('LOGGED_IN_SALT',   '=0U9QoO_LiYoxX4RU2yCRA=^D(eN9O=4Z.0RsNBX{a1 ACeU%U!7[/TH;p1|jd@V');
define('NONCE_SALT',       'o{Gi4!|oAbWQ#u@MfBnn!kLJs:%LqKsIz.-?![CJ.3TVA)ZvL?M,)5I=J;%H?&Df');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
