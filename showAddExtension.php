<?php

require '../header.php';
require 'inc/path.php';

$message = '';
if (!empty($_POST['package']))
{
	// add the package to your
	//$backend
	if ($root == 1 && intval($_POST['target']) == 1) exit('you are not allowed to install something into backend');
	
	
	if(!isset($targets[intval($_POST['target'])])) exit('no valid Target');
	
	$target = $targets[intval($_POST['target'])];
	$version = (!empty($_POST['version'])) ? $_POST['version'] : 'dev-master';
	
	if (!$j = json_decode(file_get_contents($target.'/composer.json'), true))
	{
		$j = array(
			'require' => array()
		);
	}
	
	if (!isset($j['require'][$_POST['package']]))
	{
		$j['require'][$_POST['package']] = $version;
		$message = 'package registered!';
	}
	else
	{
		$message = '<span class="err">package already exists!</span>';
	}
	
	file_put_contents($target.'/composer.json', str_replace('\\','',json_encode($j)));
	@chmod($target.'/composer.json', 0777);
}

$target = 	'<select name="target"><option value="0">Frontend</option>'
			. (($root == 2)
			   ? '<option value="1">Backend</option>'
			   : '')
			. '</select>';

$bubble_links = '<a href="inc/collectExternalRequirements.php?project='.$projectName.'&target=0">parse Frontend</a> / ';
if ($root == 2) $bubble_links .= '<a href="inc/collectExternalRequirements.php?project='.$projectName.'&target=1">parse Backend</a> ';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit add Extensions</title>
<meta charset="utf-8" />
<style>
	body{font:.8em "Trebuchet MS", sans-serif;}
	img{border:0px none;}
	pre{padding:10px;margin:10px;border:1px dotted #bbb;}
	.footnotes{font-size:.8em;}
	a{color:#33c;text-decoration:none}
	a[href^="http"]{color:#3a3;}
	.err{color:#c00;}
</style>
</head>
<body>
	
<h3>add Extensions</h3>

<p>
	
</p>

<hr />

<ol>
	<li>
		launch <a target="_blank" href="https://packagist.org/search/?q=cmskit">Packagist</a> (opens in new Window)
	</li>
	<li>
		choose your Package (read the docs)
	</li>
	<li>
		to download the Package you have two Options
		<ol>
			<li>
				<em>manual Downloading</em> and extracting the Package from the Mantainer's Homepage (read the Docs). 
				If you want to check for Dependencies lateron <?php echo $bubble_links?>
			</li>
			<li>
				<em>Download and Installation via the <a target="_blank" href="http://getcomposer.org">Composer</a> Package-Management</em> (recommended).<br />
				This Method gives you more control, manages Dependencies and a neat Update-Management!!<br />
				To register a Package to be loaded via Composer, simply put the Package-Name 
				and -<span title="if empty it defaults to 'dev-master'">optional</span>- the Version into the Form below and hit "register".
				
				<hr />
				<form method="post" action="showAddExtension.php?project=<?php echo $projectName?>">
					Install
					<input style="width:200px" type="text" name="package" placeholder='cmskit/your-packagename' />:
					<input type="text" name="version" placeholder="Version (optional)" />
					into
					<?php echo $target?>
					| <input type="submit" value="register" />
					<strong><?php echo $message?></strong>
				</form>
			</li>
		</ol>
	</li>
</ol>


</body>
</html>
