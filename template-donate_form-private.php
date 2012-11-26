<?php
/*
 * Event post type template
 *
 * BE VERY CAREFUL WHEN EDITING THIS FILE!!
 */
require_once('library/post_types/donate_form/text/countries.inc');
require_once('library/post_types/donate_form/text/error.inc');
global $df_errors;

$df_countries_options = '';
// If the user hasn't picked a country, default to U.S., otherwise, use the user's choice.
$df_selected_country = empty($df_clean['country']) ? __('United States') : $df_clean['country'];
foreach( $df_countries as $country ) {
	$df_countries_options .= '<option value="' . $country . '" ';
	if ( $country == $df_selected_country )
		$df_countries_options .= 'selected="selected" ';
	$df_countries_options .= '/>' . $country . '</option>';
}

$df_mc_list_id = get_post_meta($post->ID, 'cww_df_mc_list_id', true);
$df_mc_api_token = get_option('cww_df_options', false);
if( !empty($df_mc_api_token['cww_df_mailchimp_setting_api_token']) )
	$df_mc_api_token = $df_mc_api_token['cww_df_mailchimp_setting_api_token'];
?>

<form id="donateform" class="donate" method="POST" enctype="multipart/form-data"><input type="hidden" />
  <input type="hidden" name="df_post_id" id="df_post_id" value="<?php echo $post->ID; ?>" />
  <?php if (!empty($df_errors['form'])) : ?>
  <div class="message error form">
    <?php echo $df_error_msgs['form'][$df_errors['form']]; ?>
  </div>
  <?php endif; ?>
  <p><span class="required">*</span>Required fields</p>
  <div id="type-wrap" class="input-wrap radio" >
    <label for="df_type" class="radio main"><span class="required">*</span>Financial Commitment Types</label><br />
    <?php if (!empty($df_errors['df_type'])) { ?>
    <div class="error message donation-type">
     <?php echo $df_error_msgs['df_type'][$df_errors['df_type']]; ?>
    </div>
    <?php } ?>
    <input type="radio" name="df_type" value="monthly" <?php echo (!empty($df_clean['df_type']) && $df_clean['df_type'] == 'monthly' ? 'checked="checked"' : ''); ?> />
    <label for="df_type" class="radio option">Monthly Partner</label>
    <input type="radio" name="df_type" value="annual" <?php echo (!empty($df_clean['df_type']) && $df_clean['df_type'] == 'annual' ? 'checked="checked"' : ''); ?> />
    <label for="df_type" class="radio option">Annual Donation</label>
    <input type="radio" name="df_type" value="business" <?php echo (!empty($df_clean['df_type']) && $df_clean['df_type'] == 'business' ? 'checked="checked"' : ''); ?> />
    <label for="df_type" class="radio option">Business Partner</label>
    <input type="radio" name="df_type" value="onetime" <?php echo (!empty($df_clean['df_type']) && $df_clean['df_type'] == 'onetime' ? 'checked="checked"' : ''); ?> />
    <label for="df_type" class="radio option">One Time Gift</label>
  </div>
  <div id="amount-date-wrap" class="amount-date-wrap" <?php echo (empty($df_clean['df_type']) ? 'style="display: none;"' : ''); ?>>
    <div id="monthly-wrap" class="amount-wrap" <?php echo ((empty($df_clean['df_type'])) || ($df_clean['df_type'] != 'monthly') ? 'style="display: none;"' : ''); ?>>
      <?php if (!empty($df_errors['df_amount_monthly'])) : ?>
      <div class="error message amount-monthly">
        <?php echo $df_error_msgs['df_amount_monthly'][$df_errors['df_amount_monthly']]; ?>
      </div>
      <?php endif; ?>
      <div id="amount-monthly-wrap" class="input-wrap text">
        <label for="df_amount_monthly">
          <span class="required">*</span>Monthly Partner Amount- Enter amount: $
        </label>
        <input id="df_amount_monthly" value="<?php echo (!empty($df_clean['df_amount_monthly']) ? $df_clean['df_amount_monthly'] : ''); ?>" type="text" name="df_amount_monthly" size="10" />
      </div>
    </div><!-- end #monthly-wrap !-->
    <div id="annual-wrap" class="amount-wrap" <?php echo ((empty($df_clean['df_type'])) || ($df_clean['df_type'] != 'annual') ? 'style="display: none;"' : ''); ?>>
      <?php if (!empty($df_errors['df_amount_annual'])) : ?>
      <div class="error message amount-annual">
        <?php echo $df_error_msgs['df_amount_annual'][$df_errors['df_amount_annual']]; ?>
      </div>
      <?php endif; ?>
      <div id="amount-annually-wrap" class="input-wrap text">
        <label for="df_amount_annual">
          <span class="required">*</span>Annual Donation Amount - Enter amount: $
        </label>
        <input id="df_amount_annual" value="<?php echo (!empty($df_clean['df_amount_annual']) ? $df_clean['df_amount_annual'] : ''); ?>" type="text" name="df_amount_annual" size="10" />
      </div>
    </div><!-- end #annual-wrap !-->
    <div id="business-wrap" class="amount-wrap" <?php echo ((empty($df_clean['df_type'])) || ($df_clean['df_type'] != 'business') ? 'style="display: none;"' : ''); ?>>
      <?php if (!empty($df_errors['df_amount_business'])) : ?>
      <div class="error message amount-business">
        <?php echo $df_error_msgs['df_amount_business'][$df_errors['df_amount_business']]; ?>
      </div>
      <?php endif; ?>
      <div id="amount-business-wrap" class="input-wrap text">
        <label for="df_amount_business">
          <span class="required">*</span>Business Partner Amount - Enter amount: $
        </label>
        <input id="df_amount_business" value="<?php echo (!empty($df_clean['df_amount_business']) ? $df_clean['df_amount_business'] : ''); ?>" type="text" name="df_amount_business" size="10" />
      </div><!-- end #business-amount-wrap !-->
    </div><!-- end #business-wrap !-->
    <div id="onetime-wrap" class="amount-wrap" <?php echo ((empty($df_clean['df_type'])) || ($df_clean['df_type'] != 'onetime') ? 'style="display: none;"' : ''); ?>>
      <?php if (!empty($df_errors['df_amount_onetime'])) : ?>
      <div class="error message amount-onetime">
        <?php echo $df_error_msgs['df_amount_onetime'][$df_errors['df_amount_onetime']]; ?>
      </div>
      <?php endif; ?>
      <div id="amount-onetime-wrap" class="input-wrap text">
        <label for="df_amount_onetime">
          <span class="required">*</span>One Time Gift Amount - Enter amount: $
        </label>
        <input id="df_amount_onetime" value="<?php echo (!empty($df_clean['df_amount_onetime']) ? $df_clean['df_amount_onetime'] : ''); ?>" type="text" name="df_amount_onetime" size="10" />
      </div>
    </div><!-- end #onetime-wrap !-->
    <div id="date-wrap" class="date-wrap" <?php echo (empty($df_clean['df_type']) || $df_clean['df_type'] == 'onetime' ? 'style="display: none;"' : ''); ?>>
      <?php if (!empty($df_errors['df_startdate'])) : ?>
      <div class="error message startdate">
        <?php echo $df_error_msgs['df_startdate'][$df_errors['df_startdate']]; ?>
      </div>
      <?php endif; ?>
      <div id="startdate-wrap" class="input-wrap date">
        <label for="df_startdate"><span class="required">*</span>Start Date</label>
        <input id="df_startdate" value="<?php echo (!empty($df_clean['df_startdate']) ? $df_clean['df_startdate'] : ''); ?>" class="datepicker" type="text" name="df_startdate" />
        <span class="input-info">(yyyy-mm-dd)</span>
      </div><!-- end #startdate-wrap !-->
    </div><!-- end #date-wrap !-->
  </div><!-- end #amount-date-wrap !-->
  <div id="payment-wrap" class="payment-wrap">
    <?php if (!empty($df_errors['df_pay_method'])) : ?>
    <div class="error message pay-method">
      <?php echo $df_error_msgs['df_pay_method'][$df_errors['df_pay_method']]; ?>
    </div>
    <?php endif; ?>
    <h3>Payment Information</h3>
    <div id="pay-method-input-wrap">
      <label for="df_pay_method" class="radio main"><span class="required">*</span>Payment Method</label><br />
      <input name="df_pay_method" type="radio" value="check" class="radio option" <?php echo (!empty($df_clean['df_pay_method']) && $df_clean['df_pay_method'] == 'check' ? 'checked="checked"' : 'false'); ?> />
      <label for="df_pay_method-check" class="radio option">Check </label>
      <input name="df_pay_method" type="radio" value="cash" class="radio option" <?php echo (!empty($df_clean['df_pay_method']) && $df_clean['df_pay_method'] == 'cash' ? 'checked="checked"' : 'false'); ?> />
      <label for="df_pay_method-cash" class="radio option">Cash </label>
    </div>
    <div id="check-wrap" class="pay-method-wrap check-wrap" <?php echo (empty($df_clean['df_pay_method']) || $df_clean['df_pay_method'] == 'cash' ? 'style="display: none;"' : ''); ?>>
      <?php if ( !empty( $df_errors['df_check_bank'] ) ) : ?>
      <div class="error message check-bank">
        <?php echo $df_error_msgs['df_check_bank'][$df_errors['df_check_bank']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_check_bank"><span class="required">Bank</span></label>
      <input id="df_check_bank" name="df_check_bank" value="<?php echo (!empty($df_clean['df_check_bank']) ? $df_clean['df_check_bank'] : ''); ?>" type="text" />
      <br />
      <?php if ( !empty( $df_errors['df_check_number'] ) ) :  ?>
      <div class="error message check-number">
        <?php echo $df_error_msgs['df_check_number'][$df_errors['df_check_number']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_check_number"><span class="required">Check Number</label>
      <input id="df_check_number" name="df_check_number" value="<?php echo (!empty($df_clean['df_check_number']) ? $df_clean['df_check_number'] : ''); ?>" type="text" />
    </div><!-- end #check-wrap !-->
    <div id="cash-wrap" class="pay-method-wrap cash-wrap" <?php echo (empty($df_clean['df_pay_method']) || $df_clean['df_pay_method'] == 'check' ? 'style="display: none;"' : ''); ?>>
      <?php if ( !empty( $df_errors['df_cash_bank'] ) ) : ?>
      <div class="error message cash-bank">
        <?php echo $df_error_msgs['df_cash_bank'][$df_errors['df_cash_bank']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_cash_bank"><span class="required">Bank</span></label>
      <input id="df_cash_bank" name="df_cash_bank" value="<?php echo (!empty($df_clean['']) ? $df_clean['df_cash_bank'] : ''); ?>" type="text" />
    </div><!-- end #cash-wrap !-->
  </div><!-- end #payment-wrap !-->
  <div id="donor-wrap" class="donor-wrap">
    <h3>Donor Information</h3>
    <div id="firstname-wrap" class="input-wrap text">
      <?php if (!empty($df_errors['df_firstname'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_firstname'][$df_errors['df_firstname']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_firstname"><span class="required">*</span>First Name:</label>
      <input id="df_firstname" value="<?php echo (!empty($df_clean['df_firstname']) ? $df_clean['df_firstname'] : ''); ?>" type="text" name="df_firstname" />
    </div>
    <div id="lastname-wrap" class="input-wrap text">
      <?php if (!empty($df_errors['df_lastname'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_lastname'][$df_errors['df_lastname']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_lastname"><span class="required">*</span>Last Name:</label>
      <input id="df_lastname" value="<?php echo (!empty($df_clean['df_lastname']) ? $df_clean['df_lastname'] : ''); ?>" type="text" name="df_lastname" />
    </div>
    <div id="company-wrap" class="input-wrap text">
      <label for="df_company">Company:</label>
      <input id="df_company" type="text" name="df_company" />
    </div>
    <div id="address-wrap" class="input-wrap text long">
      <?php if (!empty($df_errors['df_address'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_address'][$df_errors['df_address']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_address">Address:</label>
      <input id="df_address" value="<?php echo (!empty($df_clean['df_address']) ? $df_clean['df_address'] : ''); ?>" type="text" name="df_address" size="40" />
    </div>
    <div id="city-wrap" class="input-wrap text">
      <?php if (!empty($df_errors['df_city'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_city'][$df_errors['df_city']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_city">City:</label>
      <input id="df_city" value="<?php echo (!empty($df_clean['df_city']) ? $df_clean['df_city'] : ''); ?>" type="text" name="df_city" />
    </div>
    <div id="state-wrap" class="input-wrap text short">
      <?php if (!empty($df_errors['df_state'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_state'][$df_errors['df_state']]; ?>
      </div>
      <?php endif; ?>
      <label for="state">State/Province:</label>
      <input id="df_state" value="<?php echo (!empty($df_clean['df_state']) ? $df_clean['df_state'] : ''); ?>" type="text" name="df_state" size="5" />
    </div>
    <div id="zip-wrap" class="input-wrap text">
      <?php if (!empty($df_errors['df_zip'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_zip'][$df_errors['df_zip']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_zip">Zip/Postal Code:</label>
      <input id="df_zip" value="<?php echo (!empty($df_clean['df_zip']) ? $df_clean['df_zip'] : ''); ?>" type="text" name="df_zip" size="10" />
    </div>
    <div id="country-wrap" class="input-wrap select">
      <?php if (!empty($df_errors['df_country'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_country'][$df_errors['df_country']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_country">Country:</label>
      <select id="df_country" name="df_country" size="1">
        <?php echo $df_countries_options; ?>
      </select>
    </div>
    <div id="phone-wrap" class="input-wrap text">
      <?php if (!empty($df_errors['df_phone'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_phone'][$df_errors['df_phone']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_phone">Phone:</label>
      <input id="df_phone" type="text" value="<?php echo (!empty($df_clean['df_phone']) ? $df_clean['df_phone'] : ''); ?>" name="df_phone" size="17" maxlength="16" />
    </div>
    <div id="email-wrap" class="input-wrap text">
      <?php if (!empty($df_errors['df_email'])) : ?>
      <div class="error message">
        <?php echo $df_error_msgs['df_email'][$df_errors['df_email']]; ?>
      </div>
      <?php endif; ?>
      <label for="df_email">Email:</label>
      <input id="df_email" type="text" value="<?php echo (!empty($df_clean['df_email']) ? $df_clean['df_email'] : ''); ?>" name="df_email" size="35" />
    </div>
    <div id="notes-wrap" class="input-wrap textarea">
      <label for="df_notes">Notes:</label>
      <textarea id="df_notes" name="df_notes" cols="33" rows="4">
	    <?php echo (!empty($df_clean['df_notes']) ? $df_clean['df_notes'] : ''); ?>
      </textarea>
    </div>
    <?php if ($df_mc_api_token && $df_mc_list_id) : ?>
    <div id="subscribe-wrap" class="input-wrap checkbox single">
      <input id="df_subscribe" type="checkbox" name="df_subscribe" value="1" <?php echo (empty($df_clean) || (!empty($df_clean['df_subscribe']) && $df_clean['df_subscribe']) ? 'checked="checked"' : ''); ?> style="padding-right: 16px;" />
      <label for="df_subscribe" class="single-checkbox">Add donor to mailing list</label>
    </div>
    <?php endif; ?>
  </div><!-- end #donor-wrap !-->
  <div id="button-wrap">
    <input id="cancel-donate" type="button" class="cancel button" name="cancel-donate" value="Cancel" /></td>
    <input id="df_submit" type="submit" class="submit button" name="df_submit" value="Submit" /></td>
  </div><!-- end #button-wrap !-->
</form>