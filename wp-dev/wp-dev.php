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

// __define('WP_DEBUG', true);

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

$__replacements = array();

// DEPRECATED - use WP_OUTPUT_REPLACE instead
// NOTE: Use WP_HOME_REPLACE_FROM env to replace a _given string_ with WP_HOME in the rendered output
// NOTE: We support multiple values with a '|' (pipe char) as delimiter
if (getenv('WP_HOME_REPLACE_FROM')) {
	$__replace_from = array_map('trim', array_filter(explode('|', getenv('WP_HOME_REPLACE_FROM')), 'trim'));
	if (! empty($__replace_from)) {
		$__replacements = array_combine($__replace_from, array_fill(0, count($__replace_from), WP_HOME));
	}
	unset($__replace_from);
}

// NOTE: Use WP_OUTPUT_REPLACE env to replace any given string in the rendered output
// NOTE: Format is: from_string => to_string
// NOTE: We support multiple values with a '|' (pipe char) as delimiter:
//       from_1 => to_1 | from_2 => to_2 | from_3 => to_3
// NOTE: Beware - this is string replacement of HTML output - it may replace inline JavaScript and CSS
if (getenv('WP_OUTPUT_REPLACE')) {
	$__replace_pairs = array_map('trim', array_filter(explode('|', getenv('WP_OUTPUT_REPLACE')), 'trim'));
	if (! empty($__replace_pairs)) {
		$from = $to = $value = '';
		foreach ($__replace_pairs as $value) {
			list($from, $to) = array_map('trim', array_filter(explode('=>', $value), 'trim'));
			if (!empty($from) && !empty($to)) {
				$__replacements[$from] = $to;
			}
		}
		unset($from, $to, $value);
	}
	unset($__replace_pairs);
}

if (!empty($__replacements)) {
	__define('WP_OUTPUT_REPLACEMENTS', $__replacements);
}
unset($__replacements);

if (defined('WP_OUTPUT_REPLACEMENTS') && is_array(WP_OUTPUT_REPLACEMENTS) && count(WP_OUTPUT_REPLACEMENTS) > 0) {
	// NOTE: WordPress calls wp_ob_end_flush_all() when shutting down (in wp-includes/functions.php).
	ob_start(function($buf = '') {
		if (is_array(WP_OUTPUT_REPLACEMENTS)) {
			$from = array_keys(WP_OUTPUT_REPLACEMENTS);
			$to = array_values(WP_OUTPUT_REPLACEMENTS);
			return str_replace($from, $to, $buf);
		}
		return $buf;
	});
}


function add_smtp_host_support() {
    if (empty(getenv('SMTP_HOST'))) {
        return;
    }

    add_action('phpmailer_init', function ($phpmailer) {
        $phpmailer->IsSMTP();
        $phpmailer->Host = getenv('SMTP_HOST');
        if (!empty(getenv('SMTP_PORT'))) {
            $smtp_port = intval(getenv('SMTP_PORT'), 10);
            if ($smtp_port > 0) {
                $phpmailer->Port = $smtp_port;
            }
        }
        $phpmailer->SMTPAuth = false;
        $phpmailer->SMTPSecure = '';
        $phpmailer->SMTPAutoTLS = false;
    });

    add_filter('wp_mail_from', function ($from_email) {
        if ($from_email !== 'wordpress@_') {
            return $from_email;
        }
        return get_option('admin_email');
    }, 20);
}

// END.
