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
		"fixRenewal" => 104
);

$arMonthlySub = array(
	"all2" => 	95,
	"all3" => 	96,
	"all4" => 	97,
	"all5" => 	98,
	"half2" => 	100,
	"half3" => 	101,
	"half4" => 	102,
	"half5" => 	103
);

$arMonthlyGender = array(
	"Boy" => 92,
	"Girl" => 93
);

$productSeasonal = 83;
$arSeasonal = array(
		"weight" => 	105,
		"birthday" => 	106,
		"name" => 		107,
		"gender" => 	108,
		"sporty" => 	109,
		"funky" => 		110,
		"classic" => 	111,
		"vintage" => 	112,
		"dress" => 		113,
		"bottom" => 	114,
		"top" => 		115,
		"height" => 	116,
		"picky" => 		117,
		"likes" => 		118,
		"subType" => 	119,
		"fixRenewal" => 120
);
$arSeasonalSub = array(
	"all2" => 	108,
	"all3" => 	109,
	"all4" => 	110,
	"all5" => 	111,
	"half2" => 	113,
	"half3" => 	114,
	"half4" => 	115,
	"half5" => 	116
);
$arSeasonalGender = array(
	"Boy" => 105,
	"Girl" => 106
);

?>