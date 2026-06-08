<?php
/**
 * The base configuration for WordPress
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

define('FLAG', 'BtSCTF{rehost_test_default}');

define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

define('DB_NAME', 'wordpress');
define('DB_USER', 'wp_user');
define('DB_PASSWORD', '7I9#48c3gWvc7lR@pg4KcW4G');
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('FS_METHOD', 'direct');
define('WP_REST_API_ENABLED', true);
define('ENABLE_REST_API', true);
define('DISABLE_WP_CRON', true);

define('JWT_AUTH_SECRET_KEY', 'e71d0e631895beb6cc0a5b0fb9b17a97c5dc6f1e24f46a3f3a65d7c93267b912');
define('JWT_AUTH_ADMIN_SECRET_KEY', 'd42ccd2046eadb8d0265fe95a0944f7f3fafef4254c75c13f1ff4fad1c2220f1');

/* define('WP_HOME', getenv('REMOTE_URL') ?: 'http://localhost:8080'); */
/* define('WP_SITEURL', getenv('REMOTE_URL') ?: 'http://localhost:8080'); */

// 1. Detect if the connection is HTTPS (including behind proxies/load balancers)
$is_secure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $is_secure = true;
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $is_secure = true;
    $_SERVER['HTTPS'] = 'on'; // Tells WordPress to trust the proxy
}

// 2. Set the protocol (http:// or https://)
$protocol = $is_secure ? 'https://' : 'http://';

// 3. Get the hostname currently being used to access the site
$host = $_SERVER['HTTP_HOST'];

// 4. Dynamically set the Site URL and Home URL!
define('WP_HOME', $protocol . $host);
define('WP_SITEURL', $protocol . $host);

define( 'AUTH_KEY',         ')Cv{(ExMM):5#<E`b$&38*DZ=c5s$E5]7}!.;vn_B{dg2~kshZ)kW?jEG>HfXC{W' );
define( 'SECURE_AUTH_KEY',  'Y}?GP`XgrTbG_hVu37D(Ze7 G-8(:vzx<$?H<+{-G>L>mVqnTUGcwba>JjT24K%]' );
define( 'LOGGED_IN_KEY',    'Qr|ZstT()p#r-ju+-.{UnYSP?=w-4z`)~:cVdb*;@j6VuC#uj_&Tc=+pki![`:L^' );
define( 'NONCE_KEY',        '/fzjhLPXLp4cDS)lRkB=#+GDM8Q#D2kS}aSi5f?iGKMo>4SBbs8KaLbdv@[nl(e|' );
define( 'AUTH_SALT',        'ZD^vQ&TfNN3d])#{Ol]x#6]Jt|E>3G5xj[f+br7M#MMz-Gt9kqmGg 7.0BaM|$zo' );
define( 'SECURE_AUTH_SALT', 'V9*hieF2z+2C!y@h!n_yglZ?]&OHFYX$Tvf$&JJZ[6k-]98-Y-P-F-i_m^=E2GS2' );
define( 'LOGGED_IN_SALT',   'U*UMf1-TA[92_l`gBf}SZ[RSe7fhN(h%W_|lS97|$RC=v/L)(4[lFGc.?qxZwa8A' );
define( 'NONCE_SALT',       'glfI~8jyookJHqLJq{&lrmo5c;=*q&D[sK|&f_.U_ne$s_~%K]uQt$[9ui.Qvz]j' );

$table_prefix = 'wp_';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
