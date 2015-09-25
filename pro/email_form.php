<?php
$to = "info@jennifermillerjewelry.com";
$subject = "signup";
$email = $_REQUEST['email'] ;
$message = $_REQUEST['email'] ;
$headers = "From: $email";
$sent = mail($to, $subject, $message, $headers) ;
if($sent)
{ header('Location: http://jennifermillerjewelry.com');}
?>