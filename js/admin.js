jQuery(document).ready(function($) {
	$(".datepicker").datepicker({ 
		dateFormat: 'yy-mm-dd',
		onClose:function(dateText, inst) {
			var start = $('#cww_event_start_date-input').datepicker('getDate');
			var end = $('#cww_event_end_date-input').datepicker('getDate');
			if (end < start) {
				$('#cww_event_end_date-input').datepicker('setDate', start);
			}
		}
	});
	
	var errors = $('.settings-error');
	$.each(errors, function(index, val) {
		var error_setting = $(this).attr('id').replace('_error', '');
		error_setting = error_setting.replace('setting-error-', '');
		// look for the label with the "for" attribute=setting title and give it an "error" class (style this in the css file!)  
        $("label[for='" + error_setting + "']").addClass('error');  
          
        // look for the input with id=setting title and add a red border to it.  
        $("#" + error_setting).addClass('error');
	});
});