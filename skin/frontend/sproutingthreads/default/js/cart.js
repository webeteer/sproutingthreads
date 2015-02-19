
jQuery(document).ready(function(){
	
	jQuery(".prodCheck").click(function(e) {
		var obj = jQuery(this);
		
		var optId = 71;
		var optVal = obj.val();
		
		jQuery.ajax({
			type: "POST",
			url: "/checkout/cart/",
			data: {
				"optId": optId,
				"optVal": optVal
			},
			success: function() {
				
			}
			
		});
	});	
	jQuery(".optionSet a").click(function(e) {
		e.preventDefault();
		var par = jQuery(this).closest(".optionSet");
		var sub = jQuery(this).closest(".subSet");
		var li = jQuery(this).closest("li");
		
		
		/*
		par.find("a").toggleClass('active', false);
		
		var obj = jQuery(this);
		obj.toggleClass('active', true);
		*/
		
		var check = sub.find(".allblend input[type=checkbox]:checked");
		var checkUl = check.closest(".allblend");
		
		var num = li.attr("data-row");
		if (num > 0) {
			sub.find("li").toggleClass("rowActive", false);
			sub.find(".row"+num).toggleClass("rowActive", true);
			checkUl.find(".row"+num+" input[type=checkbox]").click();
		}
		
		
		
	});
	
	jQuery(".optionSet input[type=checkbox]").click(function(e) {
		//e.preventDefault();
		var par = jQuery(this).closest(".optionSet");
		var sub = jQuery(this).closest(".subSet");
		var check = jQuery(this);
		var li = check.closest("li");
		
		par.find("input[type=checkbox]").prop('checked', false);
		//par.toggleClass("SELECTED", true);
		
		
		check.prop('checked', true);
		
		par.find("li").toggleClass("active", false);
		li.toggleClass('active', true);
		
		var icon = check.parent().find("i:after");
		icon.show();
		
		var num = li.attr("data-row");		
		if (num > 0) {
			sub.find("li").toggleClass("rowActive", false);
			sub.find(".row"+num).toggleClass("rowActive", true);
		}
	});	
	
	jQuery(".table-frequency input[type=checkbox]").click(function(e) {
			var check = jQuery(this);
			var term = check.attr("value");
			console.log(term);
			
			
			jQuery(".pricingSection").toggleClass("active", false);
			
			jQuery(".pricingSection."+term).toggleClass("active", true);
	});
	
});


