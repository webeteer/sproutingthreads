var giftCode = "";

jQuery(document).ready(function(){
	jQuery("#gift").blur(function() {
		var obj = jQuery(this);
		
		giftCode = "GIFT/PROMO: "+obj.val();
		
		var tar = jQuery("#ordercomment-comment");
		tar.val(giftCode);
	});
});