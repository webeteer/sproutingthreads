<?php
require_once("app/Mage.php");
Mage::app();

$productID = 1;

$product = Mage::getModel('catalog/product')->load($productID);

$cart = Mage::getSingleton('checkout/cart');
$cart->init();

$params = array(
	'product' => $productID,
	'qty' => 1,
	'options' => array(
		'1' => 1,
		'3' => 'Sean',
		'4' => 'boy',
		'5' => 1,
		'6' => 1,
		'7' => 1,
		'8' => 1,
		'9' => 1,
		'10' => 1,
		'11' => 1,
		'12' => 1,
		'13' => 'picky test'
	)
);

echo "going";


try {

	$cart->addProduct($product, $params);
	$cart->save();
	
} catch (Exception $e) {
	echo "[".$e->getMessage()."]";
}
foreach($cart->getItems() as $item) {
	print_r($item);
}


Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

echo "DONE";

