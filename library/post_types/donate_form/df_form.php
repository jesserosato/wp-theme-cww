<form id="donateform" class="donate" method="POST" enctype="multipart/form-data"><input type="hidden" />
  <input type="hidden" name="df_post_id" id="df_post_id" value="<?php echo $post->ID; ?>" />
  <?php if (isset($df_errors['form'])) : ?>
   <div class="message error form">
    <?php echo $df_error_msgs['form'][$df_errors['form']]; ?>
   </div>
  <?php endif; ?>
  <p><span class="required">*</span>Required fields</p>
  <div id="type-wrap" class="input-wrap radio" >
   <label for="df_type" class="radio main"><span class="required">*</span>Financial Commitment Types</label><br />
   <?php if (isset($df_errors['df_type'])) { ?>
   <div class="error message donation-type">
    <?php echo $df_error_msgs['df_type'][$df_errors['df_type']]; ?>
   </div>
  <?php } ?>
   <input type="radio" name="df_type" value="monthly" <?php echo (isset($df_clean['df_type']) && $df_clean['df_type'] == 'monthly' ? 'checked="checked"' : ''); ?> />
   <label for="df_type" class="radio option">Monthly Partner</label>
   <input type="radio" name="df_type" value="annual" <?php echo (isset($df_clean['df_type']) && $df_clean['df_type'] == 'annual' ? 'checked="checked"' : ''); ?> />
   <label for="df_type" class="radio option">Annual Donation</label>
   <input type="radio" name="df_type" value="business" <?php echo (isset($df_clean['df_type']) && $df_clean['df_type'] == 'business' ? 'checked="checked"' : ''); ?> />
   <label for="df_type" class="radio option">Business Partner</label>
   <input type="radio" name="df_type" value="onetime" <?php echo (isset($df_clean['df_type']) && $df_clean['df_type'] == 'onetime' ? 'checked="checked"' : ''); ?> />
   <label for="df_type" class="radio option">One Time Gift</label>
  </div>
  <div id="amount-date-wrap" class="amount-date-wrap" <?php echo (!isset($df_clean['df_type']) ? 'style="display: none;"' : ''); ?>>
   <div id="monthly-wrap" class="amount-wrap" <?php echo ((!isset($df_clean['df_type'])) || ($df_clean['df_type'] != 'monthly') ? 'style="display: none;"' : ''); ?>>
    <?php if (isset($df_errors['df_amount_monthly'])) : ?>
     <div class="error message amount-monthly">
      <?php echo $df_error_msgs['df_amount_monthly'][$df_errors['df_amount_monthly']]; ?>
     </div>
    <?php endif; ?>
    <div id="amount-monthly-wrap" class="input-wrap text">
     <label for="df_amount_monthly">
      <span class="required">*</span>Monthly Partner Amount- Enter amount: $
     </label>
     <input id="df_amount_monthly" value="<?php echo (isset($df_clean['df_amount_monthly']) ? $df_clean['df_amount_monthly'] : ''); ?>" type="text" name="df_amount_monthly" size="10" />
    </div>
    <div class="info">$25 or more per month commitment qualifies you for the Courage Partner program.</div>
   </div><!-- end #monthly-wrap !-->
   <div id="annual-wrap" class="amount-wrap" <?php echo ((!isset($df_clean['df_type'])) || ($df_clean['df_type'] != 'annual') ? 'style="display: none;"' : ''); ?>>
    <?php if (isset($df_errors['df_amount_annual'])) : ?>
     <div class="error message amount-annual">
      <?php echo $df_error_msgs['df_amount_annual'][$df_errors['df_amount_annual']]; ?>
     </div>
    <?php endif; ?>
    <div id="amount-annually-wrap" class="input-wrap text">
     <label for="df_amount_annual">
      <span class="required">*</span>Annual Donation Amount - Enter amount: $
     </label>
     <input id="df_amount_annual" value="<?php echo (isset($df_clean['df_amount_annual']) ? $df_clean['df_amount_annual'] : ''); ?>" type="text" name="df_amount_annual" size="10" />
    </div>
    <div class="info">
     $300 or more per year commitment qualifies you for the Courage Partner program.
    </div><!-- end #annually-amount-wrap !-->
   </div><!-- end #annual-wrap !-->
   <div id="business-wrap" class="amount-wrap" <?php echo ((!isset($df_clean['df_type'])) || ($df_clean['df_type'] != 'business') ? 'style="display: none;"' : ''); ?>>
    <?php if (isset($df_errors['df_amount_business'])) : ?>
     <div class="error message amount-business">
      <?php echo $df_error_msgs['df_amount_business'][$df_errors['df_amount_business']]; ?>
     </div>
    <?php endif; ?>
    <div id="amount-business-wrap" class="input-wrap text">
     <label for="df_amount_business">
      <span class="required">*</span>Business Partner Amount - Enter amount: $
     </label>
     <input id="df_amount_business" value="<?php echo (isset($df_clean['df_amount_business']) ? $df_clean['df_amount_business'] : ''); ?>" type="text" name="df_amount_business" size="10" />
     <div class="info">
      $100 or more per month or a $1,200 annual commitment qualifies you for the Courage Business Partner program.
     </div>
    </div><!-- end #business-amount-wrap !-->
   </div><!-- end #business-wrap !-->
   <div id="onetime-wrap" class="amount-wrap" <?php echo ((!isset($df_clean['df_type'])) || ($df_clean['df_type'] != 'onetime') ? 'style="display: none;"' : ''); ?>>
    <?php if (isset($df_errors['df_amount_onetime'])) : ?>
     <div class="error message amount-onetime">
      <?php echo $df_error_msgs['df_amount_onetime'][$df_errors['df_amount_onetime']]; ?>
     </div>
    <?php endif; ?>
    <div id="amount-onetime-wrap" class="input-wrap text">
     <label for="df_amount_onetime">
      <span class="required">*</span>One Time Gift Amount - Enter amount: $
     </label>
     <input id="df_amount_onetime" value="<?php echo (isset($df_clean['df_amount_onetime']) ? $df_clean['df_amount_onetime'] : ''); ?>" type="text" name="df_amount_onetime" size="10" />
    </div>
   </div><!-- end #onetime-wrap !-->
   <div id="date-wrap" class="date-wrap" <?php echo ((!isset($df_clean['df_type'])) || ($df_clean['df_type'] == 'onetime') ? 'style="display: none;"' : ''); ?>>
    <?php if (isset($df_errors['df_startdate'])) : ?>
     <div class="error message startdate">
      <?php echo $df_error_msgs['df_startdate'][$df_errors['df_startdate']]; ?>
     </div>
    <?php endif; ?>
    <div id="startdate-wrap" class="input-wrap date">
     <label for="df_startdate"><span class="required">*</span>Start Date</label>
     <input id="df_startdate" value="<?php echo (isset($df_clean['df_startdate']) ? $df_clean['df_startdate'] : ''); ?>" class="datepicker" type="text" name="df_startdate" />
     <span class="input-info">(yyyy-mm-dd)</span>
    </div><!-- end #startdate-wrap !-->
   </div><!-- end #date-wrap !-->
  </div><!-- end #amount-date-wrap !-->
  <div id="payment-wrap">
   <h3>Payment Information</h3>
   <div id="cc-logos">
    <img title="Visa" src="/wp-content/themes/cww/images/V.gif" alt="Visa" width="43" height="26" />
    <img title="MasterCard" src="/wp-content/themes/cww/images/MC.gif" alt="MasterCard" width="41" height="26" />
    <img title="American Express" src="/wp-content/themes/cww/images/Amex.gif" alt="American Express" width="40" height="26" />
    <img title="Discover" src="/wp-content/themes/cww/images/Disc.gif" alt="Discover" width="40" height="26" />
   </div><!-- end #cc-logos !-->
   <div id="card-num-wrap" class="input-wrap text">
    <?php if (isset($df_errors['df_card_num'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_card_num'][$df_errors['df_card_num']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_card_num"><span class="required">*</span>Card Number:</label>
    <input id="df_card_num" class="input_text" value="<?php echo (isset($df_clean['df_card_num']) ? $df_clean['df_card_num'] : ''); ?>" type="text" name="df_card_num" maxlength="16" />
    <span class="input-info">(enter number without spaces or dashes)</span>
   </div>
   <div id="exp-date-wrap" class="input-wrap text short">
    <?php if (isset($df_errors['df_exp_date'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_exp_date'][$df_errors['df_exp_date']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_exp_date"><span class="required">*</span>Expiration Date:</label>
    <input id="df_exp_date" class="input_text" value="<?php echo (isset($df_clean['df_exp_date']) ? $df_clean['df_exp_date'] : ''); ?>" type="text" name="df_exp_date" size="5" maxlength="4" />
    <span class="input-info">(mmyy)</span>
   </div>
   <div id="card-code-wrap" class="input-wrap text short">
    <?php if (isset($df_errors['df_card_code'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_card_code'][$df_errors['df_card_code']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_card_code"><span class="required">*</span>Security Code:</label>
    <input id="df_card_code" class="input_text" value="<?php echo (isset($df_clean['df_card_code']) ? $df_clean['df_card_code'] : ''); ?>" type="text" name="df_card_code" size="5" maxlength="4" />
    <span class="input-info">
     <a id="aCardCodeWhatsThis" onclick="javascript:return PopupLink(this);" href="https://account.authorize.net/help/Miscellaneous/Pop-up_Terms/Virtual_Terminal/Card_Code.htm" target="_blank">
      What's this?
     </a>
    </span>
   </div>
  </div><!-- end #payment-wrap !-->
  <div id="donor-wrap" class="donor-wrap">
   <h3>Your Information</h3>
   <p><span class="required">*</span>Required fields</p>
   <div id="firstname-wrap" class="input-wrap text">
    <?php if (isset($df_errors['df_firstname'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_firstname'][$df_errors['df_firstname']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_firstname"><span class="required">*</span>First Name:</label>
    <input id="df_firstname" value="<?php echo (isset($df_clean['df_firstname']) ? $df_clean['df_firstname'] : ''); ?>" type="text" name="df_firstname" />
   </div>
   <div id="lastname-wrap" class="input-wrap text">
    <?php if (isset($df_errors['df_lastname'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_lastname'][$df_errors['df_lastname']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_lastname"><span class="required">*</span>Last Name:</label>
    <input id="df_lastname" value="<?php echo (isset($df_clean['df_lastname']) ? $df_clean['df_lastname'] : ''); ?>" type="text" name="df_lastname" />
   </div>
   <div id="company-wrap" class="input-wrap text">
    <label for="df_company">Company:</label>
    <input id="df_company" type="text" name="df_company" />
   </div>
   <div id="address-wrap" class="input-wrap text long">
    <?php if (isset($df_errors['df_address'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_address'][$df_errors['df_address']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_address"><span class="required">*</span>Address:</label>
    <input id="df_address" value="<?php echo (isset($df_clean['df_address']) ? $df_clean['df_address'] : ''); ?>" type="text" name="df_address" size="40" />
   </div>
   <div id="city-wrap" class="input-wrap text">
    <?php if (isset($df_errors['df_city'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_city'][$df_errors['df_city']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_city"><span class="required">*</span>City:</label>
    <input id="df_city" value="<?php echo (isset($df_clean['df_city']) ? $df_clean['df_city'] : ''); ?>" type="text" name="df_city" />
   </div>
   <div id="state-wrap" class="input-wrap text short">
    <?php if (isset($df_errors['df_state'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_state'][$df_errors['df_state']]; ?>
     </div>
    <?php endif; ?>
    <label for="state"><span class="required">*</span>State/Province:</label>
    <input id="df_state" value="<?php echo (isset($df_clean['df_state']) ? $df_clean['df_state'] : ''); ?>" type="text" name="df_state" size="5" />
   </div>
   <div id="zip-wrap" class="input-wrap text">
    <?php if (isset($df_errors['df_zip'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_zip'][$df_errors['df_zip']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_zip"><span class="required">*</span>Zip/Postal Code:</label>
    <input id="df_zip" value="<?php echo (isset($df_clean['df_zip']) ? $df_clean['df_zip'] : ''); ?>" type="text" name="df_zip" size="10" />
   </div>
   <div id="country-wrap" class="input-wrap select">
    <?php if (isset($df_errors['df_country'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_country'][$df_errors['df_country']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_country"><span class="required">*</span>Country:</label>
    <select id="df_country" name="df_country" size="1">
     <?php foreach($df_countries as $country) {
	     echo '<option value="' . $country . '" ';
	     if ($country == __('United States')) {
		     echo 'selected="selected" ';
	     }
	     echo '/>' . $country . '</option>';
     } ?>
    </select>
   </div>
   <div id="phone-wrap" class="input-wrap text">
    <?php if (isset($df_errors['df_phone'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_phone'][$df_errors['df_phone']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_phone"><span class="required">*</span>Phone:</label>
    <input id="df_phone" type="text" value="<?php echo (isset($df_clean['df_phone']) ? $df_clean['df_phone'] : ''); ?>" name="df_phone" size="17" maxlength="16" />
   </div>
   <div id="email-wrap" class="input-wrap text">
    <?php if (isset($df_errors['df_email'])) : ?>
     <div class="error message">
      <?php echo $df_error_msgs['df_email'][$df_errors['df_email']]; ?>
     </div>
    <?php endif; ?>
    <label for="df_email"><span class="required">*</span>Email:</label>
    <input id="df_email" type="text" value="<?php echo (isset($df_clean['df_email']) ? $df_clean['df_email'] : ''); ?>" name="df_email" size="35" />
   </div>
   <div id="notes-wrap" class="input-wrap textarea">
    <label for="df_notes">Notes:</label>
    <textarea id="df_notes" name="df_notes" cols="33" rows="4">
	 <?php echo (isset($df_clean['df_notes']) ? $df_clean['df_notes'] : ''); ?>
    </textarea>
   </div>
   <?php 
   $df_mc_list_id = get_post_meta($post->ID, 'cww_df_mc_list_id', true);
   $df_mc_api_token = get_option('cww_df_options', false);
   $df_mc_api_token = $df_mc_api_token['cww_df_mailchimp_setting_api_token'];
   if ($df_mc_api_token && $df_mc_list_id) : ?>
   <div id="subscribe-wrap" class="input-wrap checkbox single">
    <input id="df_subscribe" type="checkbox" name="df_subscribe" value="1" <?php echo (empty($df_clean) || (isset($df_clean['df_subscribe']) && $df_clean['df_subscribe']) ? 'checked="checked"' : ''); ?> style="padding-right: 16px;" />
    <label for="df_subscribe" class="single-checkbox">Get news and information about <?php echo get_bloginfo('name'); ?></label>
   </div>
   <?php endif; ?>
  </div><!-- end #donor-wrap !-->
  <div id="button-wrap">
   <input id="cancel-donate" type="button" class="cancel button" name="cancel-donate" value="Cancel" /></td>
   <input id="df_submit" type="submit" class="submit button" name="df_submit" value="Donate" /></td>
  </div><!-- end #button-wrap !-->
 </form>