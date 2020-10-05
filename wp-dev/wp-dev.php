<?php
// START
/**
 * Custom settings for local dev env.
 *
 * @author Evo Stamatov <evo@ionata.com.au>
 * @version 1.0.0
 *
 * @changes:
 *   2016-12-12
 *     - Initial version
 */ 

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

function __define($name, $value) {
	if (! defined($name)) {
		define($name, $value);
	}
}

__define('ENVIRONMENT', getenv('ENVIRONMENT') ? getenv('ENVIRONMENT') : 'development');

__define('WP_CORE_DIR', getenv('WP_CORE_DIR') ? rtrim(getenv('WP_CORE_DIR'), '/\\') : dirname(__DIR__));
__define('WP_HOME', getenv('WP_HOME') ? rtrim(getenv('WP_HOME'), '/\\') : 'http://localhost:8080');
__define('WP_SITEURL', getenv('WP_SITEURL') ? rtrim(getenv('WP_SITEURL'), '/\\') : WP_HOME);

__define('CONCATENATE_SCRIPTS', !!getenv('CONCATENATE_SCRIPTS'));
__define('CONCATENATE_STYLES', !!getenv('CONCATENATE_STYLES'));

__define('DISABLE_WP_CRON', !!getenv('DISABLE_WP_CRON'));
__define('ENABLE_CACHE', !!getenv('ENABLE_CACHE'));

__define('AUTOMATIC_UPDATER_DISABLED', true);
__define('DISALLOW_FILE_EDIT', true);
__define('AUTOSAVE_INTERVAL', 160);
__define('WP_POST_REVISIONS', 3); // or a number
// __define('EMPTY_TRASH_DAYS', 0);

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    __define('FORCE_SSL_ADMIN', true);
}

__define('ABSPATH', WP_CORE_DIR . DIRECTORY_SEPARATOR); // NOTE: ABSPATH is usually already defined (in wp-load.php)

// NOTE: Use WP_HOME_REPLACE_FROM variable to replace a _given string_ with WP_HOME in the rendered output
// NOTE: We support multiple values with a '|' (pipe char) as delimiter
if (getenv('WP_HOME_REPLACE_FROM')) {
	$__replace_from = array_filter(explode('|', getenv('WP_HOME_REPLACE_FROM')), 'trim');
	if (! empty($__replace_from)) {
		__define('WP_HOME_REPLACE_FROM', $__replace_from);
	}
	unset($__replace_from);
}

if (defined('WP_HOME_REPLACE_FROM')
	&& (is_array(WP_HOME_REPLACE_FROM) && sizeof(WP_HOME_REPLACE_FROM) > 0 || strlen(WP_HOME_REPLACE_FROM) > 0)) {
	// NOTE: WordPress calls wp_ob_end_flush_all() when shutting down (in wp-includes/functions.php).
	ob_start(function($buf = '') {
		return str_replace(WP_HOME_REPLACE_FROM, WP_HOME, $buf);
	});
}

// END.