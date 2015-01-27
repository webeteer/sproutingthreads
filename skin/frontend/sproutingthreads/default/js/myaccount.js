jQuery(document).ready(function(){
	jQuery("#return .button").click(function(e) {
		e.preventDefault();
		
		var buttons = jQuery("#return .button-set");
		buttons.html("<div class='thanks'>Thank you!</div>");
		
		var email = objCustomer.email;
		var action = jQuery(this).attr('data-action');
		jQuery.ajax({
			type: "POST",
			url: '/sendMail.php',
			data: {
				action: action,
				email: email
			},
			dataType: "json"
			
		});
		
	});
	
	jQuery("#contactFormSection .button").click(function(e) {
		e.preventDefault();
		
		
		var email = objCustomer.email;
		var action = jQuery(this).attr('data-action');
		var content = jQuery("#contactFormField").val();
				
		jQuery.ajax({
			type: "POST",
			url: '/sendMail.php',
			data: {
				emailContent: content,
				action: action,
				email: email
			},
			dataType: "json"
			
		});
		
		var buttons = jQuery("#contactFormSection .forThank");
		buttons.html("<div class='thanks'>Thank you!</div>");
		
		
	});
	
});

