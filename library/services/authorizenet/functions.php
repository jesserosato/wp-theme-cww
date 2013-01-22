<?php

add_action('cww_df_pre_process', 'cww_df_authorizenet_pre_process');
// Use sandbox mode if WP_DEBUG is on.
function cww_df_authorizenet_pre_process( )
{
	defined('WP_DEBUG') && WP_DEBUG ? define('AUTHORIZENET_SANDBOX', true) : define('AUTHORIZENET_SANDBOX', false);
}