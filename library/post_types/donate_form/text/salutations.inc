<?php
// TO-DO: LOAD THESE FROM SALESFORCE?

$df_salutations = array('', 'Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Prof.');

foreach ($df_salutations as $key => $salutation) {
	$df_salutations[$key] = __($salutation);
}