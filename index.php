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

*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*********************************************************************************/

/**
* cms-kit Extension-Management
* show/edit/create Documentations, Configurations and Files
* 
*/

require '../header.php';
require 'inc/path.php';
require 'inc/functions.php';

$_SESSION['extensionedit'] = 1;//???????????????


/**
* show available Extensions
*/

$pluginNames = array();

$infohtml = '';

// section switch
$html = '
<select 
		class="mainmenu ui-widget ui-state-default ui-corner-all" 
		onchange="window.location.search=\'project='.$projectName.'&m=\'+this.value"
>';

foreach($mainpaths as $mk=>$mv)
{
	if($mv) $html .= '<option value="'.$mk.'"'.(($_GET['m']==$mk)?' selected="selected"':'').'>'.$mv[0].'</option>';
}
$html .= '</select>';

// show "add extension" wizard
$html .= '<a href="#" onclick="frameTo(\'showAddExtension.php?project='.$projectName.'\')" 
class="mainmenubutton ui-state-default ui-corner-all" title="add new Package">
<span class="ui-icon ui-icon-circle-plus"></span>
</a>';

// open in new window
$html .= '<a href="#" id="newwinbutton" onclick="window.open(window.location,\'package_manager\')"
class="mainmenubutton ui-state-default ui-corner-all" title="open in new Window">
<span class="ui-icon ui-icon-newwin"></span>
</a>';

// Accordion BEGIN
$html .= '<div id="accordion">';

$html .= getPackageList($projectName, $m, $mainpath);

/**
* show Extension-Information-Bar
*/

if(isset($_GET['ext'])) 
{
	
	$current_ext_path = $mainpath[2] . $_GET['ext'];
	
	// show current infos from the package at top
	$infohtml .= getPackageInfo($_GET['ext'], $mainpath, $current_ext_path);
	
	// Documentation Menu
	$html .= getDocList($lang, $current_ext_path);
	
	// Configuration Menu
	$html .= getConfigList($current_ext_path);
	
	
	$interfaces = array();
	$imp = array();
	
	// list all Files
	$html .= '<h3>'.L('CODE').'</h3>
	<div>';
	
		$html .= getFileList('php', $imp, $interfaces);
		$html .= getFileList('html', $imp, $interfaces);
        $html .= getFileList('xhtml', $imp, $interfaces);
		$html .= getFileList('css', $imp, $interfaces);
		$html .= getFileList('js', $imp, $interfaces);
		$html .= getFileList('xml', $imp, $interfaces);
		$html .= getFileList('json', $imp, $interfaces);
		$html .= getFileList('sql', $imp, $interfaces);
	
	$html .= '</div>';
	
	// Wizards
	$html .= getWizardList($interfaces);
	
	// Imortable Files
	$html .= getImportList($imp);
	$html .= '<h3>Tools</h3>
	<div>';
	
	if($superroot)
	{
		// Extension-Kickstarter
		$html .= '<button onclick="frameTo(\'kickstarter.php?project='.$projectName.'&m='.$m.'&ext='.$_GET['ext'].'\')" type="button">Kickstarter</button>';
        //
        $html .= '<button onclick="frameTo(\'_script_manager/index.php?project='.$projectName.'&m='.$m.'&ext='.$_GET['ext'].'\')" type="button">Scriptmanager</button>';

    }
    // Templateparser
    $html .= '<button onclick="frameTo(\'inc/tplparser/index.php?project='.$projectName.'&m='.$m.'&ext='.$_GET['ext'].'\')" type="button">Templateparser</button>';

	// backup documentation in the actual "extension"
	$html .= '<button onclick="frameTo(\'backupDoc.php?project='.$projectName.'&m='.$m.'&ext='.$_GET['ext'].'\')" type="button">Backup documentation</button>';
	
	$html .= '
	</div>';
	
	
	$html .= '</div>';
	// Accordion END
	
	// load the Overview
	$html .= '<script>frameTo(\'showDirtree.php?project='.$projectName.'&m='.$m.'&ext='.(isset($_GET['ext'])?$_GET['ext']:'').'\')</script>';

} // if($_GET['ext']) END 


?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit Extension-Management</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
<link rel="stylesheet" type="text/css" href="inc/styles/style.css" />
<link href="../../../vendor/cmskit/jquery-ui/themes/<?php echo end($_SESSION[$projectName]['settings']['interface']['theme'])?>/jquery-ui.css" rel="stylesheet" />
<script src="../../../vendor/cmskit/jquery-ui/jquery.min.js"></script>
<script>$.uiBackCompat = false;</script>
<script src="../../../vendor/cmskit/jquery-ui/jquery-ui.js"></script>

<script type="text/javascript"> 
/*<![CDATA[*//*---->*/

// load frame source
function setFrame(type, name, add)
{
	$('#frame').attr('src', type + '.php?m=<?php echo $m;?>&project=<?php echo $projectName;?>&ext=<?php echo $_GET['ext'];?>&file='+name+(add?add:'') );
};
//simply set a new frame-src
function frameTo(path)
{
	$('#frame').attr('src', path);
};

// load file-importer-script
function importFile(what, name)
{
	// security question
	var q = confirm('<?php echo L('import');?> '+what+'?');
	if(q) {
		var fileref = document.createElement('script');
		fileref.setAttribute("type","text/javascript");
		fileref.setAttribute("src",'inc/importer/import_'+ what+'.php?m=<?php echo $m;?>&project=<?php echo $projectName;?>&ext=<?php echo $_GET['ext'];?>&file='+name);
		document.getElementsByTagName("head")[0].appendChild(fileref);
	}
};

// (re)set the size of menu+frame
function setSize()
{
	var w = $(window).width(),
		h = $(window).height();
	
	$('#frame').width(w-260).height(h-20);
	$('#menu').height(h-20);
};

// prepare the UI
$(document).ready(function()
{
	$('body').removeClass('nojs');
	setSize();
	$('#accordion').accordion({
		//heightStyle: "fill",
		collapsible: true,
		heightStyle: "content"
	});
	$('#menu button').button();
	
	if(window.location == top.location) $('#newwinbutton').hide();
	
});
// set resize-listener
$(window).resize(function() {
  setSize();
  $('#accordion').accordion('refresh');
});

/*--*//*]]>*/
</script>

</head>
<body class="nojs">
	<noscript><h3>javascript must be activated</h3></noscript>
	<iframe id="frame" src="about:blank"></iframe>

	<div id="menu">
	<?php echo $infohtml . $html;?>
	</div>

</body>
</html>
