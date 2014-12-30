
jQuery(document).ready(function(){
	jQuery(".optionSet a").click(function(e) {
		e.preventDefault();
		var par = jQuery(this).closest(".optionSet");
		par.find("a").toggleClass('active', false);
		
		var obj = jQuery(this);
		obj.toggleClass('active', true);
	});
	
	jQuery(".optionSet input[type=checkbox]").click(function(e) {
		//e.preventDefault();
		var par = jQuery(this).closest(".optionSet");
		par.find("input[type=checkbox]").prop('checked', false);
		//par.toggleClass("SELECTED", true);
		
		var check = jQuery(this);
		check.prop('checked', true);
		
		var li = check.closest("li");
		
		par.find("li").toggleClass("active", false);
		li.toggleClass('active', true);
		
		var icon = check.parent().find("i:after");
		icon.show();
	});	
});


