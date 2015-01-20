<?php

$to = "seangw@seangw.com";

extract($_POST);

mail($to, $action, $email);

?>