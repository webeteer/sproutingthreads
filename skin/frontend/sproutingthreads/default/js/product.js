var imgPath = "/skin/frontend/sproutingthreads/default/images/select/";

var arLikes = {
	"classic": 0,
	"funky": 0,
	"sporty": 0,
	"vintage": 0
};

var strLikes = "";

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
	
	"vintage": 0,
	"classic": 0,
	"sporty": 0,
	"funky": 0,
	
	"likes": 0
};

var arSelectors = {
	"boy": {
		"classic": [
			"classic-boy-1.jpg",
			"classic-boy-2.jpg",
			"classic-boy-3.jpg",
			"classic-boy-4.jpg"
		],
		"funky": [
			"funky-boy-1.jpg",
			"funky-boy-2.jpg",
			"funky-boy-3.jpg",
			"funky-boy-4.jpg"
		],
		"sporty": [
			"sporty-boy-1.jpg",
			"sporty-boy-2.jpg",
			"sporty-boy-5.jpg",
			"sporty-boy-4.jpg"
		],
		"vintage": [
			"vintage-boy-5.jpg",
			"vintage-boy-2.jpg",
			"vintage-boy-3.jpg",
			"vintage-boy-6.jpg"
		]
	},
	"girl": {
		"classic": [
			"classic-girl-1.jpg",
			"classic-girl-2.jpg",
			"classic-girl-3.jpg",
			"classic-girl-4.jpg"
		],
		"funky": [
			"funky-girl-1.jpg",
			"funky-girl-2.jpg",
			"funky-girl-3.jpg",
			"funky-girl-4.jpg"
		],
		"sporty": [
			"sporty-girl-1.jpg",
			"sporty-girl-2.jpg",
			"sporty-girl-3.jpg",
			"sporty-girl-4.jpg"
		],
		"vintage": [
			"vintage-girl-1.jpg",
			"vintage-girl-2.jpg",
			"vintage-girl-3.jpg",
			"vintage-girl-4.jpg"
		]
	}
};

console.log(arSelectors);

// original
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

var base = 4;
var skip = 13;
var num = 4; // iterate this

var arTranslations = {
	"name": "options[" + (base + (skip*num) + 3) + "]",
	"gender": "options[" + (base + (skip*num) + 4) + "]",
	"birthdayMonth": "options[" + (base + (skip*num) + 2) + "][month]",
	"birthdayDay": "options[" + (base + (skip*num) + 2) + "][day]",
	"birthdayYear": "options[" + (base + (skip*num) + 2) + "][year]",
	"height": "options[" + (base + (skip*num) + 12) + "]",
	"weight": "options[" + (base + (skip*num) + 1) + "]",
	"top": "options[" + (base + (skip*num) + 11) + "]",
	"bottom": "options[" + (base + (skip*num) + 10) + "]",
	"dress": "options[" + (base + (skip*num) + 9) + "]",
	"picky": "options[" + (base + (skip*num) + 13) + "]",
	
	"vintage": "options[" + (base + (skip*num) + 8) + "]",
	"classic": "options[" + (base + (skip*num) + 7) + "]",
	"sporty": "options[" + (base + (skip*num) + 5) + "]",
	"funky": "options[" + (base + (skip*num) + 6) + "]",
	
	"likes": "options[" + (base + (skip*num) + 14) + "]"
};
/*
var arTranslations = {
	"name": "options[16]",
	"gender": "options[17]",
	"birthdayMonth": "options[15][month]",
	"birthdayDay": "options[15][day]",
	"birthdayYear": "options[15][year]",
	"height": "options[25]",
	"weight": "options[14]",
	"top": "options[24]",
	"bottom": "options[23]",
	"dress": "options[22]",
	"picky": "options[26]",
	
	"vintage": "options[21]",
	"classic": "options[20]",
	"sporty": "options[18]",
	"funky": "options[19]",
};
*/

jQuery(document).ready(function(){
	initSelectors();
	setupSelectors();
	
	jQuery("input[name='gender']").click(function() {	
		var obj = jQuery(this);
		var val = obj.val();
		
		if (val == "boy") {
			jQuery("#dressSize").hide();
		} else {
			jQuery("#dressSize").show();
		}
		
		
		initSelectors(val);
	});

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

function getGender() {
	console.log(arTranslations);
	var field = arTranslations.gender;
	var item = jQuery("input[name='gender']:checked");
	console.log(item);
	
	var val = item.val();
	
	return val;
	
}
function initSelectors(gender) {
	arLikes["classic"] = 0;
	arLikes["funky"] = 0;
	arLikes["sporty"] = 0;
	arLikes["vintage"] = 0;
	
	strLikes = "";
	
	type = "classic";
	jQuery("#"+type).val(arLikes[type]);
	type = "funky";
	jQuery("#"+type).val(arLikes[type]);
	type = "sporty";
	jQuery("#"+type).val(arLikes[type]);
	type = "vintage";
	jQuery("#"+type).val(arLikes[type]);
	
	jQuery(".imageSelector").each(function() {
		var parent = jQuery(this);
		parent.attr("data-count", 1);
		var type = parent.attr("data-type");
		var gender = getGender();
		
		var arBase = arSelectors[gender][type];
		var total = arBase.length;
		
		var num = 1;
		var src = arBase[num - 1];
		
		var img = parent.find(".selector img");
		img.attr('src', imgPath + src);
		img.attr('data-src', src);
		
		var objCount = parent.find(".count");
		var strCount = num  + " of " + total;
		objCount.html(strCount);
				
		
	});

}
function setupSelectors() {
	jQuery(".select-button").click(function(e) {
		e.preventDefault();
		
		var obj = jQuery(this);
		var parent = jQuery(this).closest(".imageSelector");
		var img = parent.find(".selector img");
		
		var dataInfo = parseInt(obj.attr("data-info"));
		var dataSrc = img.attr("data-src");
		
		var num = parent.attr("data-count");
		var type = parent.attr("data-type");
		var gender = getGender();
		
		var arBase = arSelectors[gender][type];
		var total = arBase.length;

		arLikes[type] += dataInfo;
		
		console.log("1");
		jQuery("#"+type).val(arLikes[type]);
		
		console.log("2");

		if (dataInfo == 1) {
			// liked image
			strLikes += dataSrc+";";
			jQuery("#likes").val(strLikes);
		}
				console.log("3");

		
		parent.find(".selected").toggleClass("selected", false);
		obj.toggleClass("selected", true);

		
				console.log("4");

		
		if (num >= total) {
			console.log("Skip");
			var opener = jQuery(this).closest("ul");
			opener.find(":checkbox").attr("checked", false);
		}
		
				console.log("5");

		
		if (num < total) {
			num++;
			
			var src = arBase[num - 1];
			
			
			img.attr('src', imgPath + src);
			img.attr('data-src', src);
			
			var objCount = parent.find(".count");
			var strCount = num  + " of " + total;
			objCount.html(strCount);
			
			
			parent.attr("data-count", num );
			obj.toggleClass("selected", false);
		}
		console.log("LIKES BTN", arLikes);
		console.log("LIKES STR", strLikes);
		
	});
	
}

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
	
	var gender = jQuery("input[name='gender']:checked");
	var strGender = gender.val();
	
	console.log("GENDER: " + strGender);
	
	if (strGender == "girl") {
		field = jQuery("select[name='dress']");
		fieldValue = field.val();
		if (fieldValue == "") {
			highlightError(field);
			return 0;
		}
	}
	
	console.log("LIKES", arLikes);
	
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
	
	console.log("getForm", arData);
	
	return arData;
}

function addProduct(arData, arTranslations) {	
	for(var k in arData) {
		if (arData.hasOwnProperty(k)) {
			var key = k;
			var val = arData[k];
			
			var tar = arTranslations[key];
			
			var field = jQuery("input[name='"+tar+"'],select[name='"+tar+"'],textarea[name='"+tar+"']");
			var type = field.attr('type');
			
			console.log(key, val, tar, type);
			
						
			switch(key) {
				case "radio":	
					if (val == "boy") {
						jQuery("#options_" + (base + (skip*num) + 4) + "_2").click();
					} else {
						jQuery("#options_" + (base + (skip*num) + 4) + "_3").click();
					}
					break;
				case "gender":
					if (val == "boy") {
						val = 66;
					} else {
						val = 67;
					}
					break;
				default:
					break;
			}
			
			field.val(val);
		}
	}	
	
	//opConfig.reloadPrice();
	
	//jQuery(".btn-cart").click();
	
	
	jQuery("#product_addtocart_form").submit();
}