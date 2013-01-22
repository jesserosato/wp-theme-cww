<?php
/**
 * Returns a string indicating the timezone offset in ISO 8601 format
 *
 */
function timezone_offset_str()
{
	$offset = date('Z');
	if ( !$offset )
		return 'Z';
	$sign = $offset < 0 ? '-' : '+';
	$offset = abs($offset);
	$hrs = floor($offset / 3600);
	$mins = round(($offset % 3600)/60);
	return $sign . ($hrs < 10 ? '0' : '') . $hrs . ":" . ($mins < 10 ? '0' : '') . $mins;
}

/**
 * Return the end date of a recurrence
 *
 * THIS FUNCTION EXPECTS $period to be set and one of:
 * - Monthly
 * - Yearly
 * - Weekly
 * - Quarterly
 * - 1st and 15th
 *
 * @param $start_date string Date as YYYY-MM-DD
 * @param $period string See above
 * @param $installments int
 *
 * @return string (Date as YYYY-MM-DD)
 *
 */
function recurrence_end_date( $start_date, $period, $installments ) {
	$period = strtolower(str_replace('ly', '', $period));
	// Adjust for quarterly and 1st and 15th periods.
	if ( preg_match( '/quarter/', $period ) ) {
		$period = 'months';
		$installments *= 3;
	} else if ( preg_match( '/1st and 15th/', $period ) ) {
		$period = 'weeks';
		$installments *= 2;
	}
	$end_date_u = strtotime("+" . $installments . " " . $period . " " . $start_date);
	return date('Y-m-d', $end_date_u);
}
