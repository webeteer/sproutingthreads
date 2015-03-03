<?php
/** define the various attributes ids and field names **/

global $productMonthly, $arMonthly, $arMonthlySub, $productSeasonal, $arSeasonal, $arSeasonalSub, $arMonthlyGender, $arSeasonalGender;

function clearCart($cart) {
	
	echo "Clearing Cart: ";
	
	foreach($cart->getItems() as $item) {
		echo "Removing";
		$cart->removeItem($item->getId());
	}
	
	$cart->save();
	$cart->init();
}

function addProduct($productId, $params) {
	
	global $cart, $session;
	
	$cart = Mage::getModel('checkout/cart');
	$cart->init();
	
	$product = Mage::getModel('catalog/product')->load($productId);
	$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore('default')->getId());
	
	$req = new Varien_Object();
	$req->setData($params);
	
	$newItem = $cart->addProduct($product, $params);
	
	//$session->setCartWasUpdated(true);
	
	$cart->save();
	$cart->init();
	
	//updateCartOption($cart, 0);
	
	echo " Product Added ";
}

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
		"subType" => 	71
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
		"subType" => 	86
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
				$option->setValue($value);
			}
		}
	}	

	$cart->save();
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
	$dt1 = date("n/j/Y h:i A", strtotime($date));
	$dt2 = date("Y-m-d H:i:s", strtotime($date));
	
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
					print_r($add);
					$option->setValue(serialize($add));
				}
				if ($code == "recurring_profile_options") {
					$add = unserialize($value);
					$add['start_datetime'] = $dt2; //"2015-03-08 18:50:00";
					print_r($add);
					$option->setValue(serialize($add));
				}
				
			}


		}
		$num++;
	}
	$cart->save();
		
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


function getShipDate($type = "monthly", $which = "current") {
	$today = strtotime("now");
	
	$curMonth = intval(date("m", $today));
	$curDay = date("d", $today);
	$curYear = date("Y", $today);
	
	$springMonth = 2;
	$summerMonth = 5;
	$fallMonth = 8;
	$winterMonth = 11;	
	
	switch($type) {
		case "monthly":
			if ($which == "next") {
				return date("Y-m-d", strtotime(getShipDate($type)." +1 month"));
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
				$current = getShipDate($type);
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

function writeJsTranslation() {
	global $arMonthlyGender;
?>
<script language="javascript">var arTranslations = <?php

	$arTranslations = new stdClass();

	$arTranslations->name = "options[".getConfigField("monthly", "name")."]";
	$arTranslations->gender = "options[".getConfigField("monthly", "gender")."]";
	$arTranslations->birthdayMonth = "options[".getConfigField("monthly", "birthday")."][month]";
	$arTranslations->birthdayDay = "options[".getConfigField("monthly", "birthday")."][day]";
	$arTranslations->birthdayYear = "options[".getConfigField("monthly", "birthday")."][year]";
	$arTranslations->height = "options[".getConfigField("monthly", "height")."]";
	$arTranslations->weight = "options[".getConfigField("monthly", "weight")."]";
	$arTranslations->top = "options[".getConfigField("monthly", "top")."]";
	$arTranslations->bottom = "options[".getConfigField("monthly", "bottom")."]";
	$arTranslations->dress = "options[".getConfigField("monthly", "dress")."]";
	$arTranslations->picky = "options[".getConfigField("monthly", "picky")."]";
	$arTranslations->vintage = "options[".getConfigField("monthly", "vintage")."]";
	$arTranslations->classic = "options[".getConfigField("monthly", "classic")."]";
	$arTranslations->sporty = "options[".getConfigField("monthly", "sporty")."]";
	$arTranslations->funky = "options[".getConfigField("monthly", "funky")."]";
	$arTranslations->likes = "options[".getConfigField("monthly", "likes")."]";
	$arTranslations->subType = "options[".getConfigField("monthly", "subType")."]";
	
	
	echo json_encode($arTranslations);

?>;
	var arGender = <?php
	
	echo json_encode($arMonthlyGender);
	
	?>;
	
	var genderOption = <?php echo getConfigField("monthly", "gender"); ?>;
	var subTypeOption = <?php echo getConfigField("monthly", "subType"); ?>;

</script><?php
}

writeJsTranslation();
?>