<?php
/**
* 
*/
require '../../header.php';
if(!$superroot) exit('you must be superroot to use this!');
$getP = 'm='.$_GET['m'].'&project='.$projectName;
//print_r($_GET);
// check for existing languages (use login-folder as reference)
$langs = array();
$lf = glob($backend.'/inc/login/locales/*.php');
foreach($lf as $l) $langs[] = substr($l, -6, 2);
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit Script-Manager</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<style>
	body{background: #eee;font:.9em "Trebuchet MS", sans-serif;}
	a, a:visited{text-decoration:none;color:blue;}
	#newwinbutton{float:right;}
</style>
</head>
<body>
	
	<a id="newwinbutton" class="mainmenubutton ui-state-default ui-corner-all" title="open in new Window" onclick="window.open(window.location,'package_manager')" href="#">&uArr;</a>
	
	<h2>Script Manager</h2>
	
	<p>
		<strong>
			This is a Collection of several Development-Tools:
		</strong>
		<hr />
	</p>
    <!--
	<p><b>CSS-Packer</b> lets you concatenate and compress your Backend-CSS-Files</p>
	<ul>
		<li>
			<a href="_compressors/css_pack.php?<?php echo $getP;?>">pack CSS</a> /
			<a href="_compressors/css_pack.php?nocompress=1&<?php echo $getP;?>">concat CSS (uncompressed)</a>
		</li>
	</ul>
	<p>
	<a href="_misc/themeparams/index.php?<?php echo $getP;?>">Translate</a> CSS-Parameter of/for <a target="_blank" href="http://jqueryui.com/themeroller">JQueryUI-Themeroller</a>
	</p>
	<hr />
	
	<p><b>JS-Packer</b> lets you concatenate and compress your Backend-Javascript-Files. In addition some Labels within the JS-Files were translated. </p>
	<p>Available Languages: </p>
	<ul>
	<?php
	foreach ($langs as $l) {
		echo '<li>
		<a href="_compressors/js_pack.php?lang='.$l.'&'.$getP.'">pack "'.strtoupper($l).'"</a> / 
		<a href="_compressors/js_pack.php?nocompress=1&lang='.$l.'&'.$getP.'">concat "'.strtoupper($l).'" (uncompressed)</a>
		</li> ';
	}
	?>

	</ul>
	-->
	<hr />
	<p><a href="_misc/editFilter.php?<?php echo $getP;?>">Filter-Editor</a> lets you edit the global List-Filter-Definitions</p>
	<p><a href="_misc/xml2json.php">xml2json</a> lets you translate old XML-Models to new JSON-Models</p>
	<p><a href="_misc/xml2json.php?<?php echo $getP;?>">xml2json</a> lets you translate __modelxml.php to new JSON-Model</p>
	
	<p><a href="../../../inc/php/setSuperpassword.php?<?php echo $getP;?>">refresh Super-Password</a> let you re-fresh your existing Password (you will get a reminder every 2 Months to do so)</p>
	
	<p><a href="_misc/show_checksums.php?<?php echo $getP;?>">Checksum-Test</a> lets you save a Checksum-Snapshot of your System or test against a previously saved Snapshot. This is useful for testing if someone has corrupted your system.</p>
	<p><a href="_misc/phpinfo.php?<?php echo $getP;?>">phpinfo</a> ...sometimes you need it.</p>
	<hr />

	
	<p>
	Backend-Languages can updated/extended by uploading a Language-ZIP-File
	</p>
	<form action="_misc/update_languages.php?<?php echo $getP;?>" method="post" enctype="multipart/form-data">
		<input type="file" name="langfile" /><br />
		<input type="submit" value="Upload Language-Package" />
	</form>
	<hr />
	
	
</body>
</html>
