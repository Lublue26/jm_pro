<?php
   	  
    if (!$_POST['submit'] && !$_POST['submitsmtp']) 
	{
		$html = "<html>
			<head>
				<title>Test Mosso Mail Functions</title>
			</head>
			<body>
					<form method = \"post\"; action = \"$PHP_SELF\"; >
					<input type = \"text\"; name = \"to\"; value = \"To\"; /><br />
					<input type = \"text\"; name = \"from\"; value = \"From\"; /><br />
					<input type = \"text\"; name = \"sub\"; value = \"Subject\"; /><br />
					<input type = \"text\"; name = \"mess\"; value = \"Message\"; /><br />
					<input type = \"submit\"; name = \"submit\"; value = \"Send Via Mail Function\"; />
				</form>
				<form method = \"post\"; action = \"$PHP_SELF\"; >
					<input type = \"text\"; name = \"to\"; value = \"To\"; /><br />
					<input type = \"text\"; name = \"cc\"; value = \"CC\"; /><br />
					<input type = \"text\"; name = \"from\"; value = \"From\"; /><br />
					<input type = \"text\"; name = \"sub\"; value = \"Subject\"; /><br />
					<input type = \"text\"; name = \"mess\"; value = \"Message\"; /><br />
					<input type = \"text\"; name = \"username\"; value = \"Username\"; /><br />
					<input type = \"password\"; name = \"password\"; value = \"Password\"; /><br />
					<input type = \"text\"; name = \"host\"; value = \"Mail Server\"; /><br />
					<input type = \"submit\"; name = \"submitsmtp\"; value = \"Send Via SMTP Auth\"; />
				</form>
			 </body>";
		echo $html;
	}
	else 
	{
		if ($_POST['submit'] != null) 
		{
			$headers = "FROM: " . $_POST['from'];
			$extraparams = "";
			mail($_POST['to'], $_POST['sub'], $_POST['mess'], $headers, $extraparams);
			echo "Message successfully sent!<br /> With the following parameters:<br />";
			echo "To: " . $_POST['to'] . "<br />";
			echo "Subject: " . $_POST['sub'] . "<br />";
			echo "From: " . $_POST['from'] . "<br />";
			echo "Message: " . $_POST['mess'] . "<br />";
			echo "Headers: " . $headers . "<br />";
			echo "Reply-to: " . $extraparams . "<br />";
		} 
		else
		{
			require_once "Mail.php";
			$recipients = $_POST['to'];
			$headers = array('From' => $_POST['from'], 'To' => $_POST['to'], 'Subject' => $_POST['sub']);
			if ($_POST['cc'] != '')
			{
				$headers['Cc'] = $_POST['cc'];
				$recipients .= ", ".$_POST['cc'];
			}
			$smtp = Mail::factory('smtp', array('host' => $_POST['host'], 'auth' => true,
			 'username' => $_POST['username'], 'password' => $_POST['password']));
			$mail = $smtp->send($recipients, $headers, $_POST['mess']);
			if (PEAR::isError($mail)) 
			{
				echo ("<p>" . $mail->getMessage() . "</p>");
			} 
			else 
			{
				echo "Message successfully sent!<br /> With the following parameters:<br />";
				echo "To: " . $_POST['to'] . "<br />";
				echo "CC: " . $_POST['cc'] . "<br />";
				echo "Subject: " . $_POST['sub'] . "<br />";
				echo "From: " . $_POST['from'] . "<br />";
				echo "Message: " . $_POST['mess'] . "<br />";
				echo "Headers: " . $headers . "<br />";
				echo "Reply-to: " . $extraparams . "<br />";
			}
		}
	}
 ?>
