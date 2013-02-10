<?php
/************************************************************************************ 
/* Create an array describing the meta boxes for the Event custom post type.
/*
/* @return array
/************************************************************************************/
function cww_associate_meta_boxes() {
	$meta_boxes = array();
	$meta_boxes['cww_associate_details'] = array();
	
	// First name
	$desc = __("Please enter the person's first name.", 'cww');
	$meta_boxes['cww_associate_details']['cww_associate_first_name'] = array(
		'handle'	=> 'cww_associate_first_name',
		'title' 	=> __('First Name'),
		'args'		=> array(
			'type' 		=> 'text',
			'desc' 		=> $desc,
			'req'		=> true,
			'default' 	=> 'John',
		)
	);
	
	// Last name
	$desc = __("Please enter the person's last name.", 'cww');
	$meta_boxes['cww_associate_details']['cww_associate_last_name'] = array(
		'handle'	=> 'cww_associate_last_name',
		'title' 	=> __('Last Name'),
		'args'		=> array(
			'type' 		=> 'text',
			'desc' 		=> $desc,
			'req'		=> true,
			'default' 	=> 'Doe',
		)
	);
	
	$site_name = get_bloginfo('name');
	
	// Organization
	$desc = __("Please enter the person's company or organization.", 'cww');
	$meta_boxes['cww_associate_details']['cww_associate_organization'] = array(
		'handle'	=> 'cww_associate_organization',
		'title' 	=> __('Organization'),
		'args'		=> array(
			'type' 		=> 'text',
			'desc' 		=> $desc,
			'req'		=> true,
			'default' 	=> $site_name,
		)
	);
	
	// Position
	
	$desc = __("If this person is a part of $site_name, use this field to describe their position within the organization (i.e. 'Media director' or 'CFO').", 'cww');
	$meta_boxes['cww_associate_details']['cww_associate_position'] = array(
		'handle'	=> 'cww_associate_position',
		'title' 	=> __('Position'),
		'args'		=> array(
			'type' 		=> 'text',
			'desc' 		=> $desc,
			'req'		=> true,
			'default' 	=> '',
		)
	);
	
	return $meta_boxes;
}