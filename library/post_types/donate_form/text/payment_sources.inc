<?php
$df_payment_sources = array(
	'Intuit' => 'Intuit',
	'Network for Good'	=> 'Network for Good',
	'Hewlett Packard'	=> 'Hewlett Packard',
	'Razoo'				=> 'Razoo',
	'Eventbrite'		=> 'Eventbrite',
	'United Way'		=> 'United Way',
	'Other'				=> 'Other'
);

foreach ($df_payment_sources as $key => $payment_source) {
	$df_payment_sources[$key] = __($payment_source);
}