<?php
/** define the various attributes ids and field names **/

include("phpLibrary.php");

global $gImmediateShipCutoff, $gBillDateOffset, $productMonthly, $arMonthly, $arMonthlySub, $productSeasonal, $arSeasonal, $arSeasonalSub, $arMonthlyGender, $arSeasonalGender;
global $gDateOverride;

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

?>