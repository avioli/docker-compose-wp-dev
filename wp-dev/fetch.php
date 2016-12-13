<?php
/**
 * A helper script for missing local media files.
 *
 * Version 2.0.0
 *
 * If you have a live/staging instance of a WordPress website and want to work
 * locally, after you get the source files and database, you might be missing
 * some media files. These files could easily be gigabytes of data.
 *
 * This script downloads them on a per-request basis, so if a page require one
 * and it is not found locally, this script will be invoked.
 *
 * It only fetches files that are year/month based:
 * http://domain.com/wp-content/uploads/YYYY/MM/filename.ext
 *
 * INSTALL
 *
 * Put this file to the root of your local WordPress directory
 * (where wp-config.php lives) and adjust/create the .htaccess file to point to
 * it by adding the following to the top:
 *
 * Apache
 * ------
 *

# BEGIN fetch.php
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule wp-content/uploads/(.*)$ /fetch.php?_request=$1 [QSA,NC,L]
</IfModule>
# END fetch.php

 *
 * Nginx
 * -----
 *
 * Just before your location / {} block add this:
 *

	location ~* wp-content/uploads/(.*)$ {
		try_files $uri /fetch.php?_request=$1;
	}

 *
 * CONFIGURE
 *
 * Adjust the FETCH_REMOTE_SERVER_UPLOADS_URL to point to the remote address of your
 * live/staging website's uploads directory.
 *
 * If you would like not only Administrators to be able to load remote files -
 * set FETCH_FOR_ADMINS_ONLY to False. Defaults to True.
 *
 * If your local WordPress core directory and local wp-content directory live in
 * different places, set WP_CORE_DIR to point to the proper one (where the
 * wp-blog-header.php lives).
 *
 * If a request filename differs from the fetched one (because it goes through a
 * sanitization phase) the file will be removed by default. If you would rather
 * leave this file, then set LEAVE_FILE_MISMATCH to True. Be aware that by doing
 * so you might end up with multiple files with similar name, yet not one that
 * has the requested filename. You'll get an error with file_mismatch reason.
 * Eg. file%20name.ext will be sanitized to file-name.ext which normally should
 * never occur. WordPress should have initially saved the file as file-name.ext,
 * thus the request should come already sanitized. But it may happen, because of
 * say plugins.
 */

define( 'FETCH_ENABLED', !!getenv('FETCH_ENABLED') );
define( 'FETCH_REMOTE_SERVER_UPLOADS_URL', getenv('FETCH_REMOTE_SERVER_UPLOADS_URL') );

if ( ! FETCH_ENABLED || ! FETCH_REMOTE_SERVER_UPLOADS_URL ) {
	header("HTTP/1.0 404 Not Found");
	header("Reason: fetch_disabled");
	die();
}

define( 'FETCH_FOR_ADMINS_ONLY', getenv('FETCH_FOR_ADMINS_ONLY') === False ? True : !!getenv('FETCH_FOR_ADMINS_ONLY') );
define( 'FETCH_LIMIT_RESPONSE_SIZE', getenv('FETCH_LIMIT_RESPONSE_SIZE') === False ? 20 * 1024 * 1024 : intval(getenv('FETCH_LIMIT_RESPONSE_SIZE'), 10) );
define( 'FETCH_LIMIT_RESPONSE_TIME', getenv('FETCH_LIMIT_RESPONSE_TIME') === False ? 60 : intval(getenv('FETCH_LIMIT_RESPONSE_TIME'), 10) );
// define( 'LEAVE_FILE_MISMATCH', True ); # DEBUG only !!!

define( 'WP_CORE_DIR', getenv('WP_CORE_DIR') ? rtrim(getenv('WP_CORE_DIR'), '/\\') : dirname(__DIR__) );

/**
 * NOTES:
 * For errors look at the Reason header of the response.
 *
 * Reason could be:
 *   - invalid_request   - your .htaccess may have a different key set -
 *                         this instance works with _request
 *   - no_wp             - WordPress could not be initialized.
 *                         Set WP_CORE_DIR if your WordPress core is in
 *                         separate directory.
 *   - not_admin         - Only when FETCH_FOR_ADMINS_ONLY is set
 *                         to True and a non-logged-in non-admin loads
 *                         a resource.
 *   - bad_date          - When the request has bad date - 1970-2020/01-12
 *                         and should be formatted as YYYY/MM/imagename.ext
 *   - upload_dir_error  - Look at the Localized-Reason header for
 *                         further details.
 *   - import_file_error - Look at the Localized-Reason header for
 *                         further details.
 *   - file_mismatch     - Happens if the filename when downloaded and the
 *                         request filename differ.
 */





























ignore_user_abort(true);
set_time_limit(defined('FETCH_LIMIT_RESPONSE_TIME') ? FETCH_LIMIT_RESPONSE_TIME * 2 : 60);

if (empty($_REQUEST['_request'])) {
	header("HTTP/1.0 404 Not Found");
	header("Reason: invalid_request");
	die();
}

define('WP_USE_THEMES', false);
@include(WP_CORE_DIR . '/wp-blog-header.php');

// This checks if the include above worked, not only if the function exists :)
if (! function_exists('current_user_can')) {
	header("HTTP/1.0 404 Not Found");
	header("Reason: no_wp");
	die();
}

if ( defined('FETCH_FOR_ADMINS_ONLY') && FETCH_FOR_ADMINS_ONLY && ! current_user_can('administrator')) {
	header("HTTP/1.0 404 Not Found");
	header("Reason: not_admin");
	die();
}

/**
 * Attempt to download a remote file attachment
 *
 * @param string $url URL of item to fetch
 * @param string $upload_date YYYY/MM or YYYY-MM
 * @return array|WP_Error Local file location details on success, WP_Error otherwise
 */
function fetch_remote_file( $url, $upload_date ) {
	// extract the file name and extension from the url
	$file_name = basename( $url );

	// get placeholder file in the upload dir with a unique, sanitized filename
	$upload = wp_upload_bits( $file_name, 0, '', $upload_date );
	if ( $upload['error'] )
		return new WP_Error( 'upload_dir_error', $upload['error'] );

	// fetch the remote url and write it to the placeholder file
	if( ! class_exists( 'WP_Http' ) ) {
	    include_once( ABSPATH . WPINC. '/class-http.php' );
	}

	$request = new WP_Http();
	$result = $request->get( $url, array(
		'stream'              => true,
		'filename'            => $upload['file'],
		'limit_response_size' => defined('FETCH_LIMIT_RESPONSE_SIZE') ? FETCH_LIMIT_RESPONSE_SIZE : null,
		'timeout'             => defined('FETCH_LIMIT_RESPONSE_TIME') ? FETCH_LIMIT_RESPONSE_TIME : 5
	) );

	if ( is_wp_error($result) ) {
		@unlink( $upload['file'] );
		return $result;
	}

	// request failed
	if ( ! $result['headers'] ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', __('Remote server did not respond', 'wordpress-importer') );
	}

	// make sure the fetch was successful
	if ( $result['response']['code'] !== 200 ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'wordpress-importer'), esc_html($result['response']['message']), get_status_header_desc($result['response']['code']) ) );
	}

	$filesize = filesize( $upload['file'] );

	if ( isset( $result['headers']['content-length'] ) && $filesize != $result['headers']['content-length'] ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'wordpress-importer') );
	}

	if ( 0 == $filesize ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'wordpress-importer') );
	}

	$max_size = (int) apply_filters( 'import_attachment_size_limit', 0 );
	if ( ! empty( $max_size ) && $filesize > $max_size ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', sprintf(__('Remote file is too large, limit is %s', 'wordpress-importer'), size_format($max_size) ) );
	}

	return $upload;
}

$request = $_REQUEST['_request'];

$url = FETCH_REMOTE_SERVER_UPLOADS_URL . $request;

if ( ! defined('REMOTE_SERVER_ALLOWED_HOST') ) {
	$parsed_host = @parse_url(FETCH_REMOTE_SERVER_UPLOADS_URL, PHP_URL_HOST);
	if ( ! empty($parsed_host) ) {
		define('REMOTE_SERVER_ALLOWED_HOST', $parsed_host);
	}
}

function fetch_allowed_http_request_hosts( $is_external, $host ) {
	if ( $host === REMOTE_SERVER_ALLOWED_HOST ) {
		return true;
	}
	return $is_external;
}

$time = null;
$y = (int)substr( $request, 0, 4 );
$m = (int)substr( $request, 5, 2 );
if ($y >= 1970 && $y <= 2020 && $m >= 1 && $m <= 12) {
	$time = substr( $request, 0, 7 );
}

if ($time === null) {
	header("HTTP/1.0 404 Not Found");
	header("Reason: bad_date");
	die();
}

// Handle a rare case when a filename ends with a dash (filename-.ext) and a
// size class adds another one (filename--YYYxZZZ.ext), then sanitize_filename()
// converts the multiple dashes into one, which would end up as an image with
// a bit of a different filename, thus creating inifinite number of images.
$basename = basename($request);
$no_duplicate_dashes_basename = preg_replace('/[-]+/', '-', $basename);
if ($basename !== $no_duplicate_dashes_basename) {
	$__revert_sanitized_file_name_if_needed = create_function('$filename, $filename_raw', '
		$no_duplicate_dashes_basename = "' . str_replace('"', '\\"', $no_duplicate_dashes_basename) . '";
		if ($filename_raw !== $filename && $filename === $no_duplicate_dashes_basename) {
			return $filename_raw;
		}
		return $filename;
	');
	add_filter('sanitize_file_name', $__revert_sanitized_file_name_if_needed, 999, 2);
}

add_filter('http_request_host_is_external', 'fetch_allowed_http_request_hosts', PHP_INT_MAX, 2);
$upload = fetch_remote_file( $url, $time );
remove_filter('http_request_host_is_external', 'fetch_allowed_http_request_hosts', PHP_INT_MAX, 2);

if ( is_wp_error( $upload ) ) {
	header("HTTP/1.0 404 Not Found");
	header("Reason: " . $upload->get_error_code());
	header("Localized-Reason: " . $upload->get_error_message());
	die();
}

// Make sure we don't end up in an infinite loop.
if ( basename($upload['file']) === $basename ) {
	wp_redirect($_SERVER['REQUEST_URI']);
	exit();
}

if ( ! defined('LEAVE_FILE_MISMATCH') || ! LEAVE_FILE_MISMATCH ) {
	@unlink($upload['file']);
}

header("HTTP/1.0 404 Not Found");
header("Reason: filename_mismatch");
die();
