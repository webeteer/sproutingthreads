var arFields = {
	"name": 1,
	"gender": 1,
	"birthdayMonth": 1,
	"birthdayDay": 1,
	"birthdayYear": 1,
	"height": 1,
	"weight": 1,
	"top": 1,
	"bottom": 1,
	"dress": 0,
	"picky": 0,
	
	"vintage": 1,
	"classic": 1,
	"sporty": 1,
	"funky": 1
};

var arTranslations = {
	"name": "options[3]",
	"gender": "options[4]",
	"birthdayMonth": "options[2][month]",
	"birthdayDay": "options[2][day]",
	"birthdayYear": "options[2][year]",
	"height": "options[12]",
	"weight": "options[1]",
	"top": "options[11]",
	"bottom": "options[10]",
	"dress": "options[9]",
	"picky": "options[13]",
	
	"vintage": "options[8]",
	"classic": "options[7]",
	"sporty": "options[5]",
	"funky": "options[6]",
};

jQuery(document).ready(function(){
	jQuery("#childAdd").click(function(e) {
		e.preventDefault();
		var arData = getFormData(arFields);
		if (validateForm(arFields, arData)) {
			addProduct(arData, arTranslations);
			//document.location.reload();
		}
		
	});	
	
	// move to next page (cart)
	jQuery("#childNext").click(function(e) {
		e.preventDefault();
		var arData = getFormData(arFields);
		if (validateForm(arFields, arData)) {
			console.log("GOOD");
			addProduct(arData, arTranslations);
			//document.location = "/checkout/cart";
		}
	});
	
	jQuery("#productSub input, #productSub select, #productSub radio").click(function(e) {
		jQuery(this).toggleClass('error', false);
	});
});

function validateForm(arFieldList, arData) {
	var form = jQuery("#productSub");
	var errors = 0;
	
	//return true;
	
	for(var k in arFieldList) {
		if (arFieldList.hasOwnProperty(k)) {
			var key = k;
			var req = arFieldList[k];
			
			var field = jQuery("input[name='"+key+"'],select[name='"+key+"'],textarea[name='"+key+"']");
						
			var fieldValue = arData[key];
			
			if (req && (fieldValue == "" || typeof fieldValue == "undefined")) {
				highlightError(field);
				return 0;
			}	
		}
	}
	
	console.log("Errors", errors);
	return (errors == 0);
}

function highlightError(field) {
	field.toggleClass("error", true);
	jQuery("body,html").animate({
		scrollTop: field.offset().top-200
	}, 500);
}

function getFormData(arFieldList) {
	var form = jQuery("#productSub");
	
	var arData = new Array();
	
	for(var k in arFieldList) {
		if (arFieldList.hasOwnProperty(k)) {
			var key = k;
			var val = arFieldList[k];
			
			var field = jQuery("input[name='"+key+"'],select[name='"+key+"'],textarea[name='"+key+"']");
			var type = field.attr('type');
			
			var fieldValue;
			switch(type) {
				case "radio":
					field = jQuery("input[name='"+key+"']:checked");
				default:
					fieldValue = field.val();
					break;
			}
			
			arData[key] = fieldValue;
		}
	}	
	
	return arData;
}

function addProduct(arData, arTranslations) {	
	for(var k in arData) {
		if (arData.hasOwnProperty(k)) {
			var key = k;
			var val = arData[k];
			
			var tar = arTranslations[key];
			
			console.log("Getting", tar);
			
			
			var field = jQuery("input[name='"+tar+"'],select[name='"+tar+"'],textarea[name='"+tar+"']");
			var type = field.attr('type');
			
			switch(type) {
				case "radio":
					if (val == "boy") {
						jQuery("#options_4_2").prop("checked", true);
					} else {
						jQuery("#options_4_3").prop("checked", true);
					}
					break;
				default:
					break;
			}
			
			field.val(val);
		}
	}	
	
	
	
	jQuery("#product_addtocart_form").submit();
}