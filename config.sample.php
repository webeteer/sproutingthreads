<?php
/** define the various attributes ids and field names **/

include("phpLibrary.php");

global $gImmediateShipCutoff, $gBillDateOffset, $productMonthly, $arMonthly, $arMonthlySub, $productSeasonal, $arSeasonal, $arSeasonalSub, $arMonthlyGender, $arSeasonalGender;
global $gDateOverride;

global $gEmailRecipient;

$gEmailRecipient = "info@sproutingthreads.com";

$gImmediateShipCutoff = "14 days";	// subtracted
$gBillDateOffset = "9 days";		// subtracted



$productMonthly = 82;
$arMonthly = array(
		"weight" => 	89,
		"birthday" => 	90,
		"name" => 		91,
		"gender" => 	92,
		"sporty" => 	93,
		"funky" => 		94,
		"classic" => 	95,
		"vintage" => 	96,
		"dress" => 		97,
		"bottom" => 	98,
		"top" => 		99,
		"height" => 	100,
		"picky" => 		101,
		"likes" => 		102,
		"subType" => 	103,
		"fixRenewal" => 	104
);

$arMonthlySub = array(
	"all2" => 	95,
	"all3" => 	96,
	"all5" => 	97,
	"all6" => 	98,
	"all9" => 	99,
	"half2" => 	100,
	"half3" => 	101,
	"half5" => 	102,
	"half6" => 	103,
	"half9" => 	104
);

$arMonthlyGender = array(
	"Boy" => 92,
	"Girl" => 93
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

?>