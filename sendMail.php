<?php

$to = "seangw@seangw.com";

extract($_POST);

if (isset($emailContent)) {
	mail($to, "Contact Form Submission: $email", $email.": ".$emailContent);
	echo "SENT 1";
} else {
	mail($to, $action, $email);
	echo "SENT 2";
}

print_r($_POST);

?>