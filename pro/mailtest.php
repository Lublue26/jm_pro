<?php
$to = 'malexbone@gmail.com';
$subject = 'JMTEST subject';
$message = 'THIS IS THE BODY body.';
$headers = 'From: websales@jennifermillerjewelry.com' . "\r\n" .
    'Reply-To: websales@jennifermillerjewelry.com . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>