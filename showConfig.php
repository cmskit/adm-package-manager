<?php
/********************************************************************************
*  Copyright notice
*
*  (c) 2014 Christoph Taubmann (info@cms-kit.org)
*  All rights reserved
*
*  This script is part of cms-kit Framework. 
*  This is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License Version 3 as published by
*  the Free Software Foundation, or (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/licenses/gpl.html
*  A copy is found in the textfile GPL.txt and important notices to other licenses
*  can be found found in LICENSES.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*********************************************************************************/
/**
* JSON-Editor Interface based on: http://jsoneditoronline.org
* 
*/
if (!file_exists('../../wizards/jsoneditor/index.php')) exit('JSON-Wizard is missing');

require '../header.php';
require 'inc/path.php';

$isComposer = ($_GET['file'] == 'composer.json');

$file = $mainpath[2] . $_GET['ext'] 
		. (
			$isComposer
			? '/' . $_GET['file']
			: '/config/' . $_GET['file'] . '.php'
		);

$s = '';

if (file_exists($file))
{
	
	// save config-json to file
	if(isset($_POST['json']))
	{
		
		if($isComposer) {
			$out = trim($_POST['json']);
		} else {
			$out = "<?php\n\$config = <<<EOD\n" . trim($_POST['json']) . "\nEOD;\n;?>";
		}
		
		if (@file_put_contents($file, $out))
		{
			exit('File saved!');
		}
		else
		{
			exit('File could not be saved!');
		}
	}
	
	// read file
	if($isComposer) {
		$str = file_get_contents($file);
	} else {
		$a = explode('EOD', file_get_contents($file));
		$str = $a[1];
	}
	
	if($j=json_decode($str))
	{
		$str = trim($str);
	}
	else
	{
		exit('File is not valid!');
	}
}
else
{
	exit('File "'.$file.'" not found!');
}

$action = 'showConfig.php?m='.$_GET['m'].'&project='.$projectName.'&ext='.$_GET['ext'].'&file='.$_GET['file'];

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>JSON-Editor</title>
	<script type="text/javascript" src="../../../vendor/cmskit/lib-jquery-ui/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../../wizards/jsoneditor/jsoneditor/jsoneditor.css" />
	<link rel="stylesheet" type="text/css" href="../../wizards/jsoneditor/add/add.css" />
	<script type="text/javascript" src="../../wizards/jsoneditor/jsoneditor/jsoneditor.js"></script>
</head>
<body>
<?php echo $s;?>
<button onclick="save()" class="jsoneditor-menu jsoneditor-addbuttons jsoneditor-save" id="save" title="Save"></button>
<div id="jsoneditor" style="width: 100%; height: 95%;"></div>

<script>
	
var obj = <?php echo $str;?>;
	

var container = document.getElementById("jsoneditor");
var editor = new jsoneditor.JSONEditor(container);

editor.set(obj);

// deactivate saving
<?php
if(!is_writable($file))
{
	echo "\n".'$("save").hide();';
}
?>

// save json
function save() {
	var json = editor.get();
	$.post('<?php echo $action?>',
	{
		json: JSON.stringify(json, null, 2)
	},
	function(data)
	{
		alert(data)
	});
}

</script>
</body>
</html>
