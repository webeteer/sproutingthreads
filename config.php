<?php
/** define the various attributes ids and field names **/

global $gImmediateShipCutoff, $gBillDateOffset, $productMonthly, $arMonthly, $arMonthlySub, $productSeasonal, $arSeasonal, $arSeasonalSub, $arMonthlyGender, $arSeasonalGender;
global $gDateOverride;

//$gDateOverride = "2015-03-01";
$gDateOverride = "now";

function clearCart($cart) {
	
	echo "Clearing Cart: ";
	
	foreach($cart->getItems() as $item) {
		echo "Removing";
		$cart->removeItem($item->getId());
	}
	
	$cart->save();
	$cart->init();
}

function updateOptionsFieldValue($productId, $type, $ar, $field, $value) {
	print_r($ar);
	
	
	
	if ($field == "fixRenewal" && $value != "" && $value != "ok")
		$value = date("m/d/Y", strtotime($value));
	
	$fieldId = getConfigField($productId, $field);
	
	$ar['options'][$fieldId] = $value;
	
	return $ar;
}

function addProduct($productId, $params) {
	
	global $cart, $session;
	
	$cart = Mage::getModel('checkout/cart');
	$cart->init();
	
	$product = Mage::getModel('catalog/product')->load($productId);
	/*
	$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore('default')->getId());
	
	$req = new Varien_Object();
	$req->setData($params);
	*/
	
	$newItem = $cart->addProduct($product, $params);
	
	//$session->setCartWasUpdated(true);
	
	$cart->save();
	$cart->init();
	
	//updateCartOption($cart, 0);
	
	echo " Product Added ";
}

$gImmediateShipCutoff = "14 days";	// subtracted
$gBillDateOffset = "9 days";		// subtracted

$productMonthly = 7;
$arMonthly = array(
		"weight" => 	57,
		"birthday" => 	58,
		"name" => 		59,
		"gender" => 	60,
		"sporty" => 	61,
		"funky" => 		62,
		"classic" => 	63,
		"vintage" => 	64,
		"dress" => 		65,
		"bottom" => 	66,
		"top" => 		67,
		"height" => 	68,
		"picky" => 		69,
		"likes" => 		70,
		"subType" => 	71,
		"fixRenewal" => 	87
);

$arMonthlySub = array(
	"all2" => 	69,
	"all3" => 	70,
	"all5" => 	71,
	"all6" => 	72,
	"all9" => 	73,
	"half2" => 	74,
	"half3" => 	75,
	"half5" => 	76,
	"half6" => 	77,
	"half9" => 	78
);

$arMonthlyGender = array(
	"Boy" => 66,
	"Girl" => 67
);

$productSeasonal = 81;
$arSeasonal = array(
		"weight" => 	72,
		"birthday" => 	73,
		"name" => 		74,
		"gender" => 	75,
		"sporty" => 	76,
		"funky" => 		77,
		"classic" => 	78,
		"vintage" => 	79,
		"dress" => 		80,
		"bottom" => 	81,
		"top" => 		82,
		"height" => 	83,
		"picky" => 		84,
		"likes" => 		85,
		"subType" => 	86,
		"fixRenewal" => 	88
);
$arSeasonalSub = array(
	"all2" => 	82,
	"all3" => 	83,
	"all5" => 	84,
	"all6" => 	85,
	"all9" => 	86,
	"half2" => 	87,
	"half3" => 	88,
	"half5" => 	89,
	"half6" => 	90,
	"half9" => 	91
);
$arSeasonalGender = array(
	"Boy" => 79,
	"Girl" => 80
);


function updateProduct($cart, $ar, $field, $value) {
	$productId = $ar['product_id'];
	
	echo "START";
	
	$fieldId = getConfigField($productId, $field);
	if ($fieldId == "") 
		$fieldId = $field;
	
	$value = getValueFromKey($productId, $field, $value);
	
	foreach($cart->getItems() as $item) {
		$options = $item->getOptions();
		
		foreach($options as $option) {
			$code = $option->getCode();
			echo $code." - ";
			if ($code == "option_$fieldId") {
				echo "
UPDATING option_".$fieldId." = ".$value."
";
				$option->setValue($value);
			}
		}
	}	

	//$cart->save();
}

function updateProductOption($cart, $ar, $field, $value) {
	$productId = $ar['product_id'];
	
	
	$fieldId = getConfigField($productId, $field);
	if ($fieldId == "") 
		$fieldId = $field;
	
	$value = getValueFromKey($productId, $field, $value);
	
	$ar['options'][$fieldId] = $value;
	
	return $ar;
}

function getCartShip($cart, $which) {
	$num = 0;
	foreach($cart->getItems() as $item) {
		if ($num == $which) {
			
			$productId = $item->getProductId();
			
			$options = $item->getOptions();
			
			foreach($options as $option) {
				$title = $option->getTitle();
				$code = $option->getCode();
				$value = $option->getValue();
				$type = $option->getType();
				
				if ($code == "recurring_profile_options") {
					$vals = unserialize($value);
					$start = $vals['start_datetime'];
					return date("Y-m-d", strtotime($start));
				}
				
			}


		}
		$num++;
	}
		
	return 0;
}

function getProdOptions($cart, $which) {
	
	$arOptions = array();
	
	$num = 0;
	foreach($cart->getItems() as $item) {
		if ($num == $which) {
			
			$productId = $item->getProductId();
			
			$options = $item->getOptions();
			
			foreach($options as $option) {
				$title = $option->getTitle();
				$code = $option->getCode();
				$value = $option->getValue();
				$type = $option->getType();
				
				$numCode = str_replace("option_", "", $code);
				
				$field = getFieldName($productId, $numCode);
				$value = getKeyFromValue($productId, $field, $value);
				
				if ($field != "") {
					$arOptions[$field] = $value;
				}
			}
		}
		$num++;
	}
		
	return $arOptions;

}

function updateCartDateOption($cart, $which, $date) {
	
	if ($date == date("Y-m-d")) {
		// immediate
		
	} else {
		// proper start time, no reset needed
	}
	
	$dt1 = date("n/j/Y 05:00 A", strtotime($date));
	$dt2 = date("Y-m-d 05:00:00", strtotime($date));
	
	$num = 0;
	foreach($cart->getItems() as $item) {
		if ($num == $which) {
			
			$productId = $item->getProductId();
			
			$options = $item->getOptions();
			
			foreach($options as $option) {
				$title = $option->getTitle();
				$code = $option->getCode();
				$value = $option->getValue();
				$type = $option->getType();
				
				
				echo $code." => ".$value."
";				
				
				if ($code == "additional_options") {
					$add = unserialize($value);
					$add[0]["value"] = $dt1; //"3/8/2015 1:50 PM";
					$option->setValue(serialize($add));
				}
				if ($code == "recurring_profile_options") {
					$add = unserialize($value);
					$add['start_datetime'] = $dt2; //"2015-03-08 18:50:00";
					$option->setValue(serialize($add));
				}
				
			}
		}
		$num++;
	}
	//$cart->save();
		
	return;

}

function getKeyFromValue($productId, $field, $value) {
	global $arMonthly, $arSeasonal, $arSeasonalGender, $productMonthly, $productSeasonal, $arMonthlyGender, $arMonthlySub, $arSeasonalSub;
	
	$switch = 0;
	$arLookup = array();
	
	switch($field) {	
		case "gender":
			$switch = 1;
			if ($productId == $productMonthly) {
				$arLookup = $arMonthlyGender;
			} else {
				$arLookup = $arSeasonalGender;
			}
			break;
		case "subType":
			$switch = 1;
			
			if ($productId == $productMonthly) {
				$arLookup = $arMonthlySub;
			} else {
				$arLookup = $arSeasonalSub;
			}
			break;
	}
	
	if ($switch) {
		foreach($arLookup as $key => $val) {
			if ($val == $value) {
				return $key;
			}
		}
	}
	return $value;
}

function getValueFromKey($type, $field, $value) {
	global $arMonthly, $arSeasonal, $arSeasonalGender, $productMonthly, $productSeasonal, $arMonthlyGender, $arMonthlySub, $arSeasonalSub;
	
	$switch = 0;
	$arLookup = array();
	
	switch($field) {	
		case "gender":
			$switch = 1;
			if ($type == "monthly" || $type == $productMonthly) {
				$arLookup = $arMonthlyGender;
			} else {
				$arLookup = $arSeasonalGender;
			}
			break;
		case "subType":
			$switch = 1;
			if ($type == "monthly" || $type == $productMonthly) {
				$arLookup = $arMonthlySub;
			} else {
				$arLookup = $arSeasonalSub;
			}
			break;
		case "birthday":
			$day = date("d", strtotime($value));
			$month = date("m", strtotime($value));
			$year = date("Y", strtotime($value));
			
			$value = array(
				"day" => $day,
				"month" => $month,
				"year" => $year
			);
	}
	
	if ($switch) {
		return $arLookup[$value];
		
	}
	return $value;
}

function uniToProduct($arUni, $type) {
	global $arMonthly, $arSeasonal, $productMonthly, $productSeasonal;
	
	$arSearch = $arUni;
	switch($type) {
		case "monthly":
			$arSearch = $arMonthly;
			break;
		case "seasonal":
			$arSearch = $arSeasonal;
			break;
	}
	
	$arOut = array();
	
	foreach($arUni as $key => $val) {
		
		$val = getValueFromKey($type, $key, $val);
		
		$arOut[getConfigField($type, $key)] = $val;
	}
	
	return $arOut;
}

function getFieldName($type, $field) {
	// convert a number to friendly name
	
	global $arMonthly, $arSeasonal, $productMonthly, $productSeasonal;
	
	switch($type) {
		case "monthly":
		case $productMonthly:
			$arSearch = $arMonthly;
			break;
		case "seasonal":
		case $productSeasonal:
			$arSearch = $arSeasonal;
			break;
	}

	foreach($arSearch as $key => $val) {
		if ($val == $field) {
			return $key;
		}
	}
	return "";
}

function getConfigField($type, $field) {
	// convert a friendly anme to a number
	
	global $arMonthly, $arSeasonal, $productMonthly, $productSeasonal;
	
	switch(strtolower($type)) {
		case "monthly":
		case $productMonthly:
			$arSearch = $arMonthly;
			break;
		case "seasonal":
		case $productSeasonal:
			$arSearch = $arSeasonal;
			break;
	}
	
	return $arSearch[$field];
}



function isImmediateShip($type = "monthly") {
	global $gImmediateShipCutoff, $gDateOverride;
	
	$curTime = strtotime($gDateOverride);	
	$cur = date("Y-m-d", $curTime);

	
	$nextShip = getShipDate($type);
	$nextShipTime = strtotime($nextShip);

	$cutOff = new DateTime($nextShip);
	
	date_sub($cutOff, date_interval_create_from_date_string($gImmediateShipCutoff));
	
	$cutOffDate = $cutOff->format("Y-m-d");
	$cutOffTime = strtotime($cutOffDate);
	
	if ($curTime <= $cutOffTime) {
		return 1;
	} else {
		return 0;
	}
	
	
}

function getBillDate($type, $which) {
	global $gBillDateOffset;
	
	$dt = getShipDate($type, $which);
	
	$bill = new DateTime($dt);
	date_sub($bill, date_interval_create_from_date_string($gBillDateOffset));	
	
	$billDate = $bill->format("Y-m-d");
	
	return $billDate;
}

function getShipFromBill($dt) {
	global $gBillDateOffset;
	// $dt = '2015-01-15'
	
	$bill = new DateTime($dt);
	date_add($bill, date_interval_create_from_date_string($gBillDateOffset));	
	return $bill->format("Y-m-d");
}

function getShipDate($type = "monthly", $which = "current", $date = "") {
	global $gImmediateShipCutoff, $gDateOverride;
	
	//$today = strtotime("now");
	
	if ($date == "") {
		$today = strtotime($gDateOverride);
	} else {
		$today = strtotime($date);
	}
	$dtToday = date("Y-n-d", $today);
	
	$curMonth = intval(date("m", $today));
	$curDay = date("d", $today);
	$curYear = date("Y", $today);
	
	$springMonth = 2;
	$summerMonth = 5;
	$fallMonth = 8;
	$winterMonth = 11;	
	
	if ($which == "immediate") {
		$next = getShipDate($type, "current", $date);
		$nextTime = strtotime($next);
		
		
		$cutOff = new DateTime($next);
		
		date_sub($cutOff, date_interval_create_from_date_string($gImmediateShipCutoff));
		
		$cutOffDate = $cutOff->format("Y-m-d");
		$cutOffTime = strtotime($cutOffDate);
		
		if ($today <= $cutOffTime) {
			// we have time, so ship immediately
			return $dtToday;
		} else {
			// 
			return $next;
		}
		
		return $dtToday;
	}
	
	switch($type) {
		case "monthly":
			
			if ($which == "next") {
				return date("Y-m-d", strtotime(getShipDate($type, "current", $date)." +1 month"));
			}
			$firstMonth = new DateTime("$curYear-$curMonth-1");
			
			if ($curDay < 10) {
				// same month, can ship
				$dt = "$curYear-$curMonth-10";
			
			} else {
				$firstMonth->modify('next month');
				$dt = $firstMonth->format('Y-m-10');
			}
			
			break;
		case "seasonal":
			if ($which == "next") {
				$current = getShipDate($type, "current", $date);
				return date("Y-m-d", strtotime(getShipDate($type, "current", $date)." +3 months"));
			}
			$seasonYear = $curYear;
			
			if (in_array($curMonth, array(11, 12, 1))) {
				// spring
				$seasonMonth = $springMonth;
				if ($curMonth == 11 || $curMonth == 12) {
					$seasonYear++;
				}	
			}
			if (in_array($curMonth, array(2, 3, 4))) {
				// summer
				$seasonMonth = $summerMonth;
			}
			if (in_array($curMonth, array(5, 6, 7))) {
				// fall
				$seasonMonth = $fallMonth;
			}
			if (in_array($curMonth, array(8, 9, 10))) {
				// winter
				$seasonMonth = $winterMonth;
			}
			
			$dt = "$seasonYear-$seasonMonth-10";			
			break;
	}
	
	return date("Y-m-d", strtotime($dt));
}

function writeJsTranslation($type="monthly") {
	global $arMonthlyGender, $arSeasonalGender;
	
	if ($type == "monthly") {
		$arGender = $arMonthlyGender;
	} else {
		$arGender = $arSeasonalGender;
	}
	
	
	
	
	
	
?>
<script language="javascript">var arTranslations = <?php

	$arTranslations = new stdClass();

	$arTranslations->name = "options[".getConfigField($type, "name")."]";
	$arTranslations->gender = "options[".getConfigField($type, "gender")."]";
	$arTranslations->birthdayMonth = "options[".getConfigField($type, "birthday")."][month]";
	$arTranslations->birthdayDay = "options[".getConfigField($type, "birthday")."][day]";
	$arTranslations->birthdayYear = "options[".getConfigField($type, "birthday")."][year]";
	$arTranslations->height = "options[".getConfigField($type, "height")."]";
	$arTranslations->weight = "options[".getConfigField($type, "weight")."]";
	$arTranslations->top = "options[".getConfigField($type, "top")."]";
	$arTranslations->bottom = "options[".getConfigField($type, "bottom")."]";
	$arTranslations->dress = "options[".getConfigField($type, "dress")."]";
	$arTranslations->picky = "options[".getConfigField($type, "picky")."]";
	$arTranslations->vintage = "options[".getConfigField($type, "vintage")."]";
	$arTranslations->classic = "options[".getConfigField($type, "classic")."]";
	$arTranslations->sporty = "options[".getConfigField($type, "sporty")."]";
	$arTranslations->funky = "options[".getConfigField($type, "funky")."]";
	$arTranslations->likes = "options[".getConfigField($type, "likes")."]";
	$arTranslations->subType = "options[".getConfigField($type, "subType")."]";
	$arTranslations->fixRenewal = "options[".getConfigField($type, "fixRenewal")."]";
	
	
	echo json_encode($arTranslations);

?>;
	var arGender = <?php
	
	echo json_encode($arGender);
	
	?>;
	
	var genderOption = <?php echo getConfigField($type, "gender"); ?>;
	var subTypeOption = <?php echo getConfigField($type, "subType"); ?>;

</script><?php
}

//writeJsTranslation();
?>