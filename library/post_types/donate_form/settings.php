<?php
require_once( dirname(dirname(dirname(__FILE__))) . '/utilities/CwwSettingsEngine.class.php');
require_once('df_options.php');
/************************************************************************************
/* Create a settings page for the Donate Form Post Type.
/* By: Jesse Rosato, 2012
/************************************************************************************/
$cww_df_settings = array(
	'page-title'		=> __('Donate form settings', 'cww'),
	'menu-title'		=> __('Donate forms'),
	'slug'				=> 'cww_df_options',
	'capability'		=> 'manage_options',
	'sections'			=> cww_df_options_page_sections(),
	'fields'			=> cww_df_options_page_fields(),
	'contextual_help'	=> '',
);
$cww_df_settings_engine = new CwwSettingsEngine($cww_df_settings);
add_action( 'admin_menu', array( &$cww_df_settings_engine, 'add_settings_page' ) );
add_action( 'admin_init', array( &$cww_df_settings_engine, 'register_settings' ) );