<?php

require dirname(dirname(dirname(__DIR__))) . '/inc/php/session.php';

if(!empty($_POST['captcha']) && $_POST['captcha']==$_SESSION['captcha_answer'])
{
	$i = 0;
	foreach(glob('*') as $f)
	{
		if($f!='clear.php' && $f!='info.md') 
		{
			unlink($f);
			$i++;
		}
	}
	echo $i . ' files deleted';
}
else
{
?>
<!DOCTYPE html>
<html>
<head>

</head>
<body>
<form method="post" action="clear.php">
<img src="../../../inc/php/captcha.php" /> <input type="test" name="captcha" /><input type="submit" value="clear cache" />
</form>
</body>
</html>
<?php
}
?>
