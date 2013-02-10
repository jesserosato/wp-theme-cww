<?php
$df_post_process_error_msgs = array(
	'conf_mail' => array(
		'failure' => 'Your donation was processed, but there was a problem sending your confirmation email, which contains some more information about us.  You should still receive a receipt of your payment via email.  If you do not receive that receipt or if you have any questions, please email us at <a href=" mailto:' . get_bloginfo('admin_email') . '">' . get_bloginfo('admin_email') . '</a>.'
	)
);

// Send to translator
foreach ($df_post_process_error_msgs as $key => $val) {
	if (is_array($val)) {
		foreach ($val as $key2 => $msg) {
			$df_post_process_error_msgs[$key][$key2] = __($msg);
		}
	}
}