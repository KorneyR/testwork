<?php 
/* 	
If you see this text in your browser, PHP is not configured correctly on this webhost. 
Contact your hosting provider regarding PHP configuration for your site.
*/

require_once('form_throttle.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	if (formthrottle_too_many_submissions($_SERVER["REMOTE_ADDR"]))
	{
		echo '{"MusePHPFormResponse": { "success": false,"error": "Too many recent submissions from this IP"}}';
	} 
	else 
	{
		emailFormSubmission();
		$baza = mysql_connect ("localhost","root","");
		mysql_select_db ("tw",$baza);
		mysql_query("SET NAMES 'utf8'");
		$_REQUEST['Name']=mysql_real_escape_string($_REQUEST['Name']);
		$_REQUEST['Email']=mysql_real_escape_string($_REQUEST['Email']);
		$email=mysql_real_escape_string($_REQUEST['Email']);
		$template="([\.\-_A-Za-z0-9]+?){5,}@[\.\-A-Za-z0-9]+?[\ .A-Za-z0-9]{2,}";
		preg_match_all("~[lsd]~", $email, $m);
		//count(array_count_values($m[0])) == 3 ? $email echo'Верный Email': echo'Неверный Email';
		$_REQUEST['Phone']=mysql_real_escape_string($_REQUEST['Phone']);
		$sql = mysql_query("
						INSERT INTO `form` 
							(`name`, 
							`email`, 
							`phone`, 
							`date`) 
							VALUES 
							('".$_REQUEST['Name']."', 
							'".$_REQUEST['Email']."', 
							'".$_REQUEST['Phone']."', 
							now());
					") or die(mysql_error());
					
		
	}
} 

function emailFormSubmission()
{
	$to = 'admin@rkorney.ru';
	$subject = 'Отправка Форма Домашняя страница';
	
	$message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/><title>' . htmlentities($subject,ENT_COMPAT,'UTF-8') . '</title></head>';
	$message .= '<body style="background-color: #ffffff; color: #000000; font-style: normal; font-variant: normal; font-weight: normal; font-size: 12px; line-height: 18px; font-family: helvetica, arial, verdana, sans-serif;">';
	$message .= '<h2 style="background-color: #eeeeee;">Отправка новой формы</h2><table cellspacing="0" cellpadding="0" width="100%" style="background-color: #ffffff;">'; 
	$message .= '<tr><td valign="top" style="background-color: #ffffff;"><b>Имя:</b></td><td>' . htmlentities($_REQUEST["Name"],ENT_COMPAT,'UTF-8') . '</td></tr>';
	$message .= '<tr><td valign="top" style="background-color: #ffffff;"><b>Электронная почта:</b></td><td>' . htmlentities($_REQUEST["Email"],ENT_COMPAT,'UTF-8') . '</td></tr>';
	$message .= '<tr><td valign="top" style="background-color: #ffffff;"><b>Сотовый телефон:</b></td><td>' . htmlentities($_REQUEST["Phone"],ENT_COMPAT,'UTF-8') . '</td></tr>';

	$message .= '</table><br/><br/>';
	$message .= '<div style="background-color: #eeeeee; font-size: 10px; line-height: 11px;">Формы, отправленные с веб-сайта: ' . htmlentities($_SERVER["SERVER_NAME"],ENT_COMPAT,'UTF-8') . '</div>';
	$message .= '<div style="background-color: #eeeeee; font-size: 10px; line-height: 11px;">IP-адрес посетителя: ' . htmlentities($_SERVER["REMOTE_ADDR"],ENT_COMPAT,'UTF-8') . '</div>';
	$message .= '</body></html>';
	$message = cleanupMessage($message);
	
	$formEmail = cleanupEmail($_REQUEST['Email']);
	$headers = 'From:  admin@rkorney.ru' . "\r\n" . 'Reply-To: ' . $formEmail .  "\r\n" .'X-Mailer: Adobe Muse 7.2.232 with PHP/' . phpversion() . "\r\n" . 'Content-type: text/html; charset=utf-8' . "\r\n";
	
	$sent = @mail($to, $subject, $message, $headers);
	
	if($sent)
	{
		echo '{"FormResponse": { "success": true}}';

	}
	else
	{
		echo '{"MusePHPFormResponse": { "success": false,"error": "Failed to send email"}}';
	}
}

function cleanupEmail($email)
{
	$email = htmlentities($email,ENT_COMPAT,'UTF-8');
	$email = preg_replace('=((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*=i', null, $email);
	return $email;
}

function cleanupMessage($message)
{
	$message = wordwrap($message, 70, "\r\n");
	return $message;
}
?>
