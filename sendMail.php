<?php

include "config.php";

global $gEmailRecipient;
$to = $gEmailRecipient;

$to = "seangw@seangw.com";

extract($_POST);


if (isset($emailContent)) {
	mail($to, "Contact Form Submission: $email", $email.": ".$emailContent);
	echo "SENT 1";
} else if (isset($action) && $action== "rethreadForm") {
	$body = print_r($_POST, true);
	mail($to, "Rethread Request: $email", $body);
	echo "SENT $body";
} else {
	mail($to, $action, $email);
	echo "SENT 2";
}

print_r($_POST);

?>