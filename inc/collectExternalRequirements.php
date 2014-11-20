<?php
/**
* 
* 
* 1. recursively scan all Directories for doc/info.php
* 2. decode Json & look for the tag requirements
* 3. add Requirements to composer.json
*/

// exit(); // activate this to prevent acessing/running this service at all!!
require '../../header.php';
require 'path.php';

if ($root == 1 && intval($_GET['target']) == 1) exit('you are not allowed to install something into backend');
if(!isset($targets[intval($_GET['target'])])) exit('no valid Target');
$target = $targets[intval($_POST['target'])];

$html = '';
$prev = array();
$composer = array 	(
						'require' => array(
							'php' => '>=5.3.3',// add a php-test for composer
						),
					);
//$backend = realpath('../../../');

if (!is_writable($target)) $html .= '<h3 class="warning">Folder is not writable</h3>';

$directories = new RecursiveIteratorIterator(
												new ParentIterator(new RecursiveDirectoryIterator($target)), 
												RecursiveIteratorIterator::SELF_FIRST
											);

foreach ($directories as $directory)
{
	if (file_exists($directory . '/doc/info.php'))
	{
		
		include $directory . '/doc/info.php';
		if ($j = json_decode($config, true))
		{
			
			// get Libraries for development-purposes
			if (isset($_GET['dev']) && isset($j['system']['external_requirements_dev']))
			{
				$c = 0;
				foreach ($j['system']['external_requirements_dev'] as $k => $v)
				{
					if (isset($composer['require'][$k]) && $composer['require'][$k] != $v)
					{
						$html .= '<p class="warning">another Version of '.$k.' Library in '.$directory.' is required in '.$prev[$k]."!!</p>\n";
					}
					
					$composer['require'][$k] = $v;
					$prev[$k] = $directory;
					$c++;
				}
				if ($c>0) $html .= '<p>found '.$c.' external <i>Development-</i>Requirement'.(($c>1)?'s':'').' for: ' . $directory . "/</p>\n";
			}
			
			// get Libraries for production-purposes
			if (isset($j['system']['external_requirements']))
			{
				$c = 0;
				foreach ($j['system']['external_requirements'] as $k => $v)
				{
					if (isset($composer['require'][$k]) && $composer['require'][$k] != $v)
					{
						$html .= '<p class="warning">another Version of '.$k.' Library in '.$directory.' is required in '.$prev[$k]."!!</p>\n";
					}
					
					$composer['require'][$k] = $v;
					$prev[$k] = $directory;
					$c++;
				}
				
				if ($c>0) $html .= '<p>found '.$c.' external Requirement'.(($c>1)?'s':'').' for: ' . $directory . "/</p>\n";
				
			}
		}
	}
}

// create composer.json
file_put_contents($target.'/composer.json', str_replace('\\','',json_encode($composer)));
chmod($target.'/composer.json', 0777);



?>
<!DOCTYPE html>
<html>
<head>
<title>Dependencies-Collector</title>
<meta charset="utf-8" />
<style>
body {
	font-family: sans-serif;
	font-size: .9em;
}
.warning {
	color: #c00;
}
</style>
</head>
<body>
	
<h3>Dependencies-Collector</h3>
<p>
	This Service scans all installed Modules for Configurations that needs external Libraries. 
	The Dependencies are written to Configurations in the Root-Folder (backend or frontend)
</p>
<p>
	At the Moment Composer is supported. Here you can find the resources.
	<ul>
		<li><a target="_blank" href="http://getcomposer.org">Composer</a></li>
		<li><a target="_blank" href="http://getcomposer.org/doc/">Composer Documentation</a></li>
		<li><a target="_blank" href="https://packagist.org">Packagist</a></li>
	</ul>
	<ol>
		<li>Run this Script. After that, you should find (an updated) "composer.json" in backend/</li>
		<li>Install Composer ( we assume here you installed it into backend/ )</li>
		<li>open a Terminal and cd to your Backend-Directory</li>
		<li>
			for the first time ( there is no File called "composer.lock" in your folder ) 
			<br />run <b>php composer.phar install</b> else
		    <br />run <b>php composer.phar update</b>
		</li>
	</ol>
</p>
<hr />
<?php
echo $html;
?>

</body>
</html>
