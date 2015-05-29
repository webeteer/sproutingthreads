var imgPath = "/skin/frontend/sproutingthreads/default/images/select/";
var defaultSub = "all new 5";



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
	
	"likes": 0,
	"fixRenewal": 0
};

var arChoices = {
	"upload": "",
	"classic": {},
	"funky": {},
	"sporty": {},
	"vintage": {}
};

var arSelectors = {
	"boy": {
		"classic": [
			"classic-boy-6.jpg",
			"classic-boy-2.jpg",
			"classic-boy-3.jpg",
			"classic-boy-4.jpg"
		],
		"funky": [
			"funky-boy-5.jpg",
			"funky-boy-2.jpg",
			"funky-boy-3.jpg",
			"funky-boy-6.jpg"
		],
		"sporty": [
			"sporty-boy-4.jpg",
			"sporty-boy-5.jpg",
			"sporty-boy-6.jpg",
			"sporty-boy-7.jpg"
		],
		"vintage": [
			"vintage-boy-9.jpg",
			"vintage-boy-2.jpg",
			"vintage-boy-5.jpg",
			"vintage-boy-6.jpg"
		]
	},
	"girl": {
		"classic": [
			"classic-girl-5.jpg",
			"classic-girl-6.jpg",
			"classic-girl-3.jpg",
			"classic-girl-4.jpg"
		],
		"funky": [
			"funky-girl-6.jpg",
			"funky-girl-7.jpg",
			"funky-girl-8.jpg",
			"funky-girl-9.jpg"
		],
		"sporty": [
			"sporty-girl-1.jpg",
			"sporty-girl-6.jpg",
			"sporty-girl-7.jpg",
			"sporty-girl-4.jpg"
		],
		"vintage": [
			"vintage-girl-5.jpg",
			"vintage-girl-2.jpg",
			"vintage-girl-3.jpg",
			"vintage-girl-4.jpg"
		]
	}
};


jQuery(document).ready(function(){

	var likes = jQuery("#likes").val();
	
	if (likes != "") {
		// populate
		gender = getGender();
		loadSelectors(likes, gender);
	} else {
		// reset
		initSelectors();
	}
	setupSelectors();
	
	//window.history.forward(1);
	
	var uploader = new ss.SimpleUpload({
		button: document.getElementById('uploadBtn'),
		url: '/uploadHandler.php',
		name: 'uploadfile',
		responseType: 'json',
		allowedExtensions: ["jpg", "jpeg", "png", "gif"],
		onComplete: function(filename, response) {
			if (!response) {
				alert(filename + 'upload failed');
				return false;            
			}
			
			var newFilename = response.filename;
			arChoices["upload"] = newFilename;
			jQuery("#likes").val(JSON.stringify(arChoices));
			jQuery("#uploadBtn").html("completed!");
		}
	});
	
	jQuery(".back, .next").click(function(e) {
		e.preventDefault();
		
		var obj = jQuery(this);
		
		var change = parseInt(obj.attr("data-change"));
		var parent = jQuery(this).closest(".imageSelector");
		var num = parseInt(parent.attr("data-count"));
		var type = parent.attr("data-type");
		var gender = getGender();
		
		var arBase = arSelectors[gender][type];
		var total = arBase.length;		
		
		var tar = num+change;
		
		if (tar <= total && tar > 0) {
			moveStep(obj, num+change);	
		}
		
		
		
		
	});
	
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
	jQuery("#childNext, #headerNext").click(function(e) {
		e.preventDefault();
		var inactive = jQuery(this).hasClass('inactive');
		if (inactive) return;
		
		jQuery(this).toggleClass("inactive", true);
		
		var arData = getFormData(arFields);
		if (validateForm(arFields, arData)) {
			console.log("GOOD");
			addProduct(arData, arTranslations);
			//document.location = "/checkout/cart";
		} else {
			jQuery(this).toggleClass("inactive", false);
		}
	});
	
	jQuery("#productSub input, #productSub select, #productSub radio").click(function(e) {
		jQuery(this).toggleClass('error', false);
	});
});

function getGender() {
	var field = arTranslations.gender;
	var item = jQuery("input[name='gender']:checked");
	
	var val = item.val();
	
	return val;
	
}

function loadSelectors(likes, gender) {
	arChoices = JSON.parse(likes);
	
	type = "classic";
	classic = jQuery("#"+type).val();
	arLikes[type] = classic;
	
	type = "funky";
	funky = jQuery("#"+type).val();
	arLikes[type] = funky;
	
	type = "sporty";
	sporty = jQuery("#"+type).val();
	arLikes[type] = sporty;
	
	type = "vintage";
	vintage = jQuery("#"+type).val();
	arLikes[type] = vintage;
	
	jQuery(".start-styles-section").find(".selected").toggleClass("selected", false);	
	
	buildSelectors();
	
	populateSelectors();
	
}

function populateSelectors() {
	jQuery(".imageSelector").each(function() {
		var parent = jQuery(this);
		parent.attr("data-count", 1);
		
		var type = parent.attr("data-type");
		var gender = getGender();
		
		var ar = arChoices[type];
		
		var num = 0;
		for (key in ar) {
			num++;
		}
		
		if (num > 0)
			moveStep(parent.find(".selector"), num);
		
	});			
}

function buildSelectors() {
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

function initSelectors(gender) {
	console.log("Initializing Selectors");
	arLikes["classic"] = 0;
	arLikes["funky"] = 0;
	arLikes["sporty"] = 0;
	arLikes["vintage"] = 0;
	
	
	arChoices["classic"] = {};
	arChoices["funky"] = {};
	arChoices["sporty"] = {};
	arChoices["vintage"] = {};
	
	jQuery("#likes").val(JSON.stringify(arChoices));
	strLikes = "";
	
	type = "classic";
	jQuery("#"+type).val(arLikes[type]);
	type = "funky";
	jQuery("#"+type).val(arLikes[type]);
	type = "sporty";
	jQuery("#"+type).val(arLikes[type]);
	type = "vintage";
	jQuery("#"+type).val(arLikes[type]);
	
	jQuery(".start-styles-section").find(".selected").toggleClass("selected", false);	
	
	buildSelectors();
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
		
		arChoices[type][dataSrc] = dataInfo;
		
		
		jQuery("input#"+type).val(arLikes[type]);
		
		jQuery("#likes").val(JSON.stringify(arChoices));
		
		console.log(JSON.stringify(arChoices));
		
		parent.find(".selected").toggleClass("selected", false);
		obj.toggleClass("selected", true);

		
		if (num >= total) {
			var opener = jQuery(this).closest("ul");
			opener.find(":checkbox").attr("checked", false);
		}
		
		if (num < total) {
			moveStep(obj, ++num);
		}
		
	});
	
}

function moveStep(obj, num) {
	var parent = obj.closest(".imageSelector");
	var img = parent.find(".selector img");
	
	var parNum = parseInt(parent.attr("data-count"));
	var type = parent.attr("data-type");
	var gender = getGender();
	
	var arBase = arSelectors[gender][type];
	var total = arBase.length;
	
	var src = arBase[num - 1];
	
	img.attr('src', imgPath + src);
	img.attr('data-src', src);	
	
	var objCount = parent.find(".count");
	var strCount = num  + " of " + total;
	objCount.html(strCount);
	
	parent.attr("data-count", num );
	parent.find(".select-button").toggleClass("selected", false);
	
	console.log(arChoices, type, src);
	
	val = -5;
	for(var k in arChoices[type]) {
		if (k == src) {
			val = arChoices[type][k];
		}
	}
	
	if (val > -5) {
		parent.find(".select-button[data-info='"+val+"']").toggleClass('selected', true);
	}
	
	
	
	
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
	
	if (strGender == "girl") {
		field = jQuery("select[name='dress']");
		fieldValue = field.val();
		if (fieldValue == "") {
			highlightError(field);
			return 0;
		}
	}
	
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
			
			var field = jQuery("input[name='"+tar+"'],select[name='"+tar+"'],textarea[name='"+tar+"']");
			var type = field.attr('type');
			
			
			console.log(key, val, type);
						
			switch(key) {
				case "radio":	
					if (val == "boy") {
						jQuery("#options_" + genderOption + "_2").click();
					} else {
						jQuery("#options_" + genderOption + "_3").click();
					}
					break;
				case "gender":
					if (val == "boy") {
						val = arGender["Boy"];
					} else {
						val = arGender["Girl"];
					}
					break;
				default:
					break;
			}
			
			field.val(val);
		}
	}	
	
	jQuery("#recurring_start_date").val(jQuery("#startdate").val());
	
	tar = "options["+subTypeOption+"]";
	var field = jQuery('select[name="'+tar+'"]');
	var option = field.find("option:contains('" + defaultSub + "')").attr("selected", true);
	
	jQuery("#product_addtocart_form").submit();
}