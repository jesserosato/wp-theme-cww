jQuery(document).ready(function($) {
	
	
	// Display the appropriate donation wrapper if a donation type has been selected.
	$('.date-wrap').hide();
	$('.amount-wrap').hide();
	displayDonationWrapper($("input[name='df_type']:checked").val());
	var val = $("input[name='df_pay_method']:checked").val();
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
		var val = $("input[name='df_pay_method']:checked").val();
		$("#" + val + "-wrap").show();
	});
	$("#cancel-donate").click(function(){
		window.location = "/";
	});
	
	// Initialize the "company" field label
	var donor_type = $("#df_donor_type");
	var selected = $(":selected", donor_type);
	if ( selected.val() == 'Individual' ) {
		$("label[for='df_company']").html("Company: ");
	} else {
		var required = $('<span class="required">');
		required.html('*');
		$("label[for='df_company']").html(selected.val() + ": ");
		$("label[for='df_company']").prepend(required);
	}
	
	
	$("#df_donor_type").change(function(){
		var selected = $(':selected', this);
		
		if( selected.val() =='Individual' ) {
			$("label[for='df_company']").children('.required').remove();
			$("label[for='df_company']").html("Company: ");
		} else {
			var required = $('<span class="required">');
			required.html('*');
			$("label[for='df_company']").html(selected.val() + ": ");
			$("label[for='df_company']").prepend(required);
		}
	});

	// Preload loading background.
	var modal = $("#modal");
	var screen = $("#screen");
	modal.addClass("loading");
	modal.show();
	modal.hide();
	modal.removeClass('loading');
	// Front and center loading screen
	$('#donateform').submit(function() {
		screen.show();
		screen.off('click');
		modal.addClass('loading');
		modal.show();
		return true;
	});
	
	//
	$('a.external').on('click', function(e) {
		e.preventDefault();
		screen.toggle();
		if ( modal.html() ) {
			modal.html('');
		} else {
			var frame = $('<iframe>');
			frame.attr('src', this.href);
			modal.html(frame);
		}
		modal.toggle();
	});
	
	$("#screen").on('click', function(e) {
		modal.hide();
		modal.html('');
		screen.hide();
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