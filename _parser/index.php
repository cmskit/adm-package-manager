<?php
/**
 *
 */
$getAddition = '<input type="hidden" name="project" value="'.$_GET['project'].'" />';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit script-parser</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<style>
body{background: #eee;font:.9em "Trebuchet MS", sans-serif;}
    a, a:visited{
        text-decoration:none;
        color:blue;
        display:block;
    }
	#newwinbutton{float:right;}
</style>
</head>
<body>

<h3>Parser / Packer</h3>

<p></p>

<form action="cssparser" method="get">
    <p><b>CSS-Packer</b> lets you concatenate and compress your Backend-CSS-Files</p>
    <?php echo $getAddition?>
    <input type="submit" value="open" />
</form>

<form action="javascriptparser" method="get">
    <p><b>Javascript-Packer</b> lets you concatenate and compress your backend-javascript-files. In addition some labels within the JS-files were translated. </p>
    <?php echo $getAddition?>
    <input type="submit" value="open" />
</form>

<form action="templateparser" method="get">
    <p><b>Template-Parser</b> lets you parse template files to callable php-/js-functions</p>
    <?php echo $getAddition?>
    <input type="submit" value="open" />
</form>

</body>
</html>