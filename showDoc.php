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
/*
* show Documentation-Files (or redirect to HTM(L)
*/

error_reporting(0);

// decode encoded filepath - useful for translation-services or robust links
// e.g. http://yourdomain.com/backend/admin/package_manager/showDoc.php?&e=...
// your encoded path is shown in the page-source
if(isset($_GET['e'])) $_GET['file'] = base64_decode($_GET['e']);


/*
 Caching if you need it. Uncomment the following Lines AND the Line at the very Bottom of this Script

	$cacheLocation = 'cache/' . md5($_GET['file']);//change the Location for Cache-Files if needed
	if(file_exists($cacheLocation)){ exit(file_get_contents($cacheFile)); }
	ob_start();
*/


$mime = array_pop(explode('.', $_GET['file']));

// redirect ot HTML-Page
if ($mime=='html' || $mime=='htm') {
	header('location: '  . $_GET['file']);
	exit();
}
// exit if Mime-Type is not allowed
if ($mime!='md' && $mime!='txt') {
	exit('Mimetype "'.$mime.'" is not allowed!');
}
$ppos = strpos($_GET['file'], '/doc/');
if ($ppos === false)
{
	exit('Filepath "'.$_GET['file'].'" is not valid!');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation</title>

<meta charset="utf-8"/>

<link rel="stylesheet" href="inc/styles/showDoc.css" />
<script src="inc/js/toc_helper.js"></script>
<script src="inc/js/highlight.pack.js"></script>

</head>
<body>
<?php
// show the e-parameter in the page-source
echo '<!-- e='.trim(base64_encode($_GET['file']),'=')." -->\n";

$basePath = '';

// draw edit link
if (isset($_GET['edit_me']))
{
	$basePath = substr($_GET['file'], 0, (strlen($_GET['file'])-strlen($_GET['edit_me'])) );
	echo '<a id="edit_link" href="showFile.php?m='.$_GET['m'].'&project='.$_GET['project'].'&ext='.$_GET['ext'].'&file='.$_GET['edit_me'].'" title="edit"><img src="inc/styles/edit.png"></a>';
}

// draw "open in new window"-link (with special get-parameter)
if (!isset($_GET['e']))
{
	echo '&nbsp;&nbsp;<a id="share_link" title="Open in new window" target="_blank" href="showDoc.php?e='.trim(base64_encode($_GET['file']),'=').'"><img src="inc/styles/externallink.png"></a>';
}

// 
$doc_path = dirname($_GET['file']) . '/';

require '../../../vendor/michelf/php-markdown/Michelf/MarkdownExtra.inc.php';

function inc($m)
{
	global $doc_path;
	$mime = array_pop(explode('.', $m[2]));
	return ((in_array($mime, array('md','txt'))) 
			? @file_get_contents($doc_path.'/'.$m[2]) 
			: 'Filetype "'.$mime.'" is not allowed');
}
if ($str = file_get_contents($_GET['file']))
{
	// include external Files 
	// @import url(RELATIVE_PATH)
	$str = preg_replace_callback(
		"!@import\s+url\((['\"])(.+?)\\1\)!",
		'inc',
		$str
	);
	// create the html
	$html = Michelf\MarkdownExtra::defaultTransform($str);
	
	// replace some paths 
	$html = str_replace(
				array(
						'href="#', // mask anchor Links
						'href="http', // mask external Links
						'href="', // catch the rest (internal Links)
						'||||||', // catch the previously masked (external) hrefs
						'#######', // catch the previously masked (anchor) hrefs
						'src="', // catch Image-src
						'@SEARCHBOX@',
				),
				array(
						'#######',
						'||||||',
						'href="showDoc.php?m='.$_GET['m'].'&project='.$_GET['project'].'&ext='.$_GET['ext'].'&file='.$doc_path,
						'target="_blank" href="http',
						'href="#',
						'src="'.$doc_path,
						'<form class="searchbox" method="get" action="inc/docSearch.php"><input type="hidden" name="file" value="'.substr($_GET['file'],0,($ppos+7)).'" /><input type="text" name="str" /> <button type="submit"><img src="inc/styles/search.png" /></button></form>',
				),
				$html
			);
	
	echo $html;
}
else
{
	echo 'File "'.$_GET['file'].'" not found!';
}

?>

<script>
(function() {
	document.body.appendChild(createTOC());
	// http://highlightjs.org
	hljs.initHighlightingOnLoad();
})();
</script>
</body>
</html>
<?php
// write HTML-Output to Cache
//file_put_contents($cacheLocation, ob_get_contents());
?>
