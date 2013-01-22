<?php
/************************************************************************************ 
/* Create an array describing the meta boxes for the Event custom post type.
/*
/* @return array
/************************************************************************************/
function cww_event_meta_boxes() {
	$meta_boxes = array();
	$meta_boxes['cww_event_details'] = array();
	
	// Start date
	$desc = __("Please choose the event's start date.", 'cww');
	$desc .= '<br /><strong>Shortcode:</strong> [eventstartdate format="m-d-Y" eventid="1234"]<br />';
	$desc .= 'The default format is "Monday, January 12th, 2014".  See <a href="http://php.net/manual/en/function.date.php">here</a> for more information about PHP Date formats.';
	$meta_boxes['cww_event_details']['cww_event_start_date'] = array(
		'handle'	=> 'cww_event_start_date',
		'title' 	=> __('Start Date'),
		'args'		=> array(
			'type' 		=> 'date',
			'class' 	=> 'datepicker',
			'desc' 		=> $desc,
			'req'		=> true,
			'default' 	=> date('Y-m-d'),
		)
	);
	// Start time
	$desc = __("Please enter the event's start time.", 'cww');
	$desc .= '<br /><strong>Shortcode:</strong> [eventstarttime eventid="1234"]';
	$default_mins = floor(date('i') / 15) * 15;
	$default_mins = $default_mins ? $default_mins : '00';
	$default_start = date('h') . ':' . $default_mins . date('A');
	$meta_boxes['cww_event_details']['cww_event_start_time'] = array(
		'handle'	=> 'cww_event_start_time',
		'title' 	=> __('Start Time'),
		'args'		=> array(
			'type' 		=> 'time',
			'class' 	=> 'time',
			'desc' 		=> $desc,
			'req'		=> true,
			'default' 	=> $default_start,
		)
	);
	// End date
	$desc = __("Please choose the event's end date.", 'cww');
	$desc .= '<br /><strong>Shortcode:</strong> [eventenddate format="m-d-Y" eventid="1234"]<br />';
	$desc .= 'The default format is "Monday, January 12th, 2014".  See <a href="http://php.net/manual/en/function.date.php">here</a> for more information about PHP Date formats.';
	$meta_boxes['cww_event_details']['cww_event_end_date'] = array(
		'handle'	=> 'cww_event_end_date',
		'title' 	=> __('End Date'),
		'args'		=> array(
			'type' 		=> 'date',
			'class' 	=> 'datepicker',
			'desc' 		=> $desc,
			'default' 	=> date('Y-m-d'),
		)
	);
	// End time
	$desc = __("Please enter the event's end time.", 'cww');
	$desc .= '<br /><strong>Shortcode:</strong> [eventendtime eventid="1234"]';
	$default_end = date('h:i A', strtotime($default_start)+60*60);
	$meta_boxes['cww_event_details']['cww_event_end_time'] = array(
		'handle'	=> 'cww_event_end_time',
		'title' 	=> __('End Time'),
		'args'		=> array(
			'type' 		=> 'time',
			'class' 	=> 'time',
			'desc' 		=> $desc,
			'default' 	=> $default_end,
		)
	);
	
	$desc = __("Please enter the event's location/address.", 'cww');
	$desc .= '<br /><strong>Shortcode:</strong> [eventlocation eventid="1234"]';
	$meta_boxes['cww_event_details']['cww_event_location'] = array(
		'handle'	=> 'cww_event_location',
		'title' 	=> __('Location'),
		'args'		=> array(
			'type' 		=> 'textarea',
			'class' 	=> 'location',
			'desc' 		=> $desc,
			'default' 	=> 'TBD',
		)
	);
	
	$desc = __("Please enter any additional information about the event.", 'cww');
	$desc .= '<br /><strong>Shortcode:</strong> [eventinfo eventid="1234"]';
	$meta_boxes['cww_event_details']['cww_event_info'] = array(
		'handle'	=> 'cww_event_info',
		'title' 	=> __('Additional Info'),
		'args'		=> array(
			'type' 		=> 'textarea',
			'class' 	=> '',
			'desc' 		=> $desc,
			'default' 	=> '',
		)
	);
	
	$desc = __("Please enter the URL for the event's registration form.", 'cww');
	$desc .= '<br /><strong>Shortcode:</strong> [eventregbtn class="css-class-1 css-class-2" eventid="1234"]Link Text[/eventregbtn]';
	$meta_boxes['cww_event_details']['cww_event_reg_btn_url'] = array(
		'handle'	=> 'cww_event_reg_btn_url',
		'title' 	=> __('Registration form URL'),
		'args'		=> array(
			'type' 		=> 'text',
			'class' 	=> 'url',
			'desc' 		=> $desc,
			'default' 	=> '',
		)
	);
	
	$desc = __("Please enter the ID of the post you'd like to replace this event once this event has ended.  Leave this blank to continue to display the event as if it were still upcoming.", 'cww');
	$meta_boxes['cww_event_details']['cww_event_after_post_id'] = array(
		'handle'	=> 'cww_event_after_post_id',
		'title' 	=> __('After-event Post ID'),
		'args'		=> array(
			'type' 		=> 'text',
			'class' 	=> 'numeric',
			'desc' 		=> $desc,
			'default' 	=> '',
		)
	);

	
	return $meta_boxes;
}