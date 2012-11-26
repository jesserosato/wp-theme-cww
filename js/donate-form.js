jQuery(document).ready(function($) {;
	
	// Display the appropriate donation wrapper if a donation type has been selected.
	displayDonationWrapper($("input[name='df_type']:checked").val());
	var val = $("input[name='df_pay_method']:checked").val()
	$("#" + val + "-wrap").show();
	
	//donate form events
	$(".datepicker").datepicker({ dateFormat: 'yy-mm-dd' });
	/*$("#x_type").click(function(){
		$(".DateWrap").toggle();
	}); */
	$("input[name='df_type']").change(function(){
		//hide all
		$(".amount-date-wrap").hide();
		$(".amount-wrap").hide();
		displayDonationWrapper($("input[name='df_type']:checked").val());
	});
	$("input[name='df_pay_method']").change(function(){
		//hide all
		$(".pay-method-wrap").hide();
		var val = $("input[name='df_pay_method']:checked").val()
		$("#" + val + "-wrap").show();
	});
	$("#cancel-donate").click(function(){
		window.location = "/";							   
	});

	$('#donateform').submit(function() {
		$('#df_submit').attr('disabled', 'disabled');
		$('#df_submit').addClass('disabled');
		$('#cancel-donate').attr('disabled', 'disabled');
		$('#cancel-donate').addClass('disabled');
		return true;
	});

});

function displayDonationWrapper(donation_type) {
	switch(donation_type) {
		case "monthly":
			jQuery("#monthly-wrap").show();
			jQuery(".date-wrap").show();
			break;
		case "annual":
			jQuery("#annual-wrap").show();
			jQuery(".date-wrap").show();
			break;
		case "business":
			jQuery("#business-wrap").show();
			jQuery(".date-wrap").show();
			break;
		case "onetime":
			jQuery("#onetime-wrap").show();
			jQuery(".date-wrap").hide();
			break;
		default:
			jQuery(".date-wrap").hide();
	}
	jQuery(".amount-date-wrap").show();
	return;
}