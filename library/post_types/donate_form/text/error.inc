<?php
$df_error_msgs = array(
	'form' => array(
		'multiple' => "Uh-oh, there're some problems with the information you entered.  Please check the error messages below and try again.",
		'single' => "So close! It looks like there's a problem with the information you entered.  Please check the error message below and try again.",
		'declined' => "We're sorry, but our payment gateway is indicating that your card has been declined.  Please check your card information and try again.",
		'processing' => "We were not able to process your payment, please check your payment information and try again.  If you've already done that, we're sorry for the hassle and hope you'll email us at <a href=" . '"mailto:' . get_bloginfo('admin_email') . '">' . get_bloginfo('admin_email') . '</a>.'
	),
	'df_firstname' => array(
		'empty' => 'The first name field is required.',
	),
	'df_lastname' => array(
		'empty' => 'The last name field is required.',
	),
	'df_company' => array(
		'empty' => 'The name of the organization making this donation is required.',
	),
	'df_address' => array(
		'empty' => 'The street address field is required.',
	),
	'df_city' => array(
		'empty' => 'The city field is required.',
	),
	'df_state' => array(
		'empty' => 'The state field is required.',
	),
	'df_zip' => array(
		'empty' => 'The zip/postal code field is required.',
	),
	'df_country' => array(
		'empty' => 'The country field is required.',
	),
	'df_phone' => array(
		'empty' => 'The phone number field is required.',
		'format' => 'Please enter a 10-digit U.S. phone number, beginning with area code, e.g. (555) 555-1212.',
	),
	'df_email' => array(
		'empty' => 'The email field is required.',
		'format' => 'Please enter a valid email address.',
		'confirm' => 'The email field has to match the email confirmation field.' ,
	),
	'df_card_num' => array(
		'empty' => 'The credit card number field is required.',
		'format' => 'Please enter a valid credit card number.',
	),
	'df_exp_date' => array(
		'empty' => 'The credit card expiration date field is required.',
		'format' => "The credit card expiration date must be in the format 'mmyy', e.g. '0215'.",
		'invalid' => "The credit card expiration date you entered has passed.",
	),
	'df_card_code' => array(
		'empty' => 'The credit card verification code is required.',
		'format' => "Please enter a valid card code.",
	),
	'df_type' => array(
		'empty' => "Please specify the type of donation you're interested in making.",
	),
	'df_startdate' => array(
		'empty' => 'When should we start your recurring donation?',
		'invalid' => 'Please choose a date (today or later) for your recurring donation to start',
	),
	'df_amount_monthly' => array(
		'empty' => 'Please enter the amount you would like to contribute each month.',
		'format' => 'Please enter the amount you would like to contribute, e.g. 250.25',
	),
	'df_amount_annual' => array(
		'empty' => 'Please enter the amount you would like to contribute annually.',
		'format' => 'Please enter the amount you would like to contribute, e.g. 250.25',
	),
	'df_amount_business' => array(
		'empty' => 'Please enter the amount your business would like to contribute each month.',
		'format' => 'Please enter the amount you would like to contribute, e.g. 250.25',
	),
	'df_amount_onetime' => array(
		'empty' => 'Please enter the amount for your one-time contribution.',
		'format' => 'Please enter the amount you would like to contribute, e.g. 250.25',
	),
	'df_pay_method' => array(
		'empty' => 'Please choose a payment method.'
	),
	'df_check_source' => array(
		'empty' => 'Please choose the payment source.'
	),
	'df_check_number' => array(
		'empty' => 'Please enter the check number.',
		'format' => 'The check number must be numbers only.'
	),
	'df_cash_source' => array(
		'empty' => 'Please choose the payment source.',
	)
);

// Send to translator
foreach ($df_error_msgs as $key => $val) {
	if (is_array($val)) {
		foreach ($val as $key2 => $msg) {
			$df_error_msgs[$key][$key2] = __($msg);
		}
	}
}