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
	
});

