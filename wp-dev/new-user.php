<?php
if (!defined('WP_CORE_DIR')) {
	define('WP_CORE_DIR', getenv('WP_CORE_DIR') ? rtrim(getenv('WP_CORE_DIR'), '/\\') : dirname(__DIR__));
}

define('WP_USE_THEMES', false);
require(WP_CORE_DIR . '/wp-blog-header.php');

if (!empty($_POST['username'])) {
	$password = trim(wp_generate_password(12, false));

	$username = sanitize_user($_POST['username']);
	if (!empty($username)) {
		$user_id = wp_create_user($username, $password, $username . '@domain.com');

		if (is_wp_error($user_id)) {
			die($user_id->get_error_message());
		} else {
			$new_user = new WP_User($user_id); 
			$new_user->add_cap('administrator', '1');
			die('User created. Password: ' . $password);
		}
	}
}
?>
<form method="post">
	<label>Username: <input type="text" name="username" /></label>
	<button type="submit">Create</button>
</form>