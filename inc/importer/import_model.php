<?php
/**
* 
* 
* 
* this File is loadad as Javascript via JsonP, so every output should be JS-Syntax (alert etc.)
*/

//load the general function-header
require '../../../header.php';

$src = ($_GET['m'] ? $backend : $frontend) . '/extensions/' . $_GET['ext'] . '/' . $_GET['file'];

$draft = $frontend.'/objects/__draft.php';


if ( is_writable($draft) && is_readable($src) )
{
	$existingObjects = array();
	$changes = false;
	
	require ($draft);
	if (!$draftmodel = json_decode($model, true)) exit('alert("could not decode draft");');
	
	// fill a check-array with the names of existing objects
	foreach ($draftmodel['object'] as $o) $existingObjects[] = $o['name'];
	
	
	$newstr = file_get_contents($src);
	if (!$srcmodel = json_decode($newstr, true)) exit('alert("could not decode src");');
	
	foreach($srcmodel['object'] as $n)
	{
		// if the object already exists, we have to check the fields
		if ( ($k = array_search($n['name'], $existingObjects)) !== false )
		{
			// fill check-array
			$existingFields = array();
			foreach ($draftmodel['object'][$k]['fields']['field'] as $f) $existingFields[] = $f['name'];
			
			// loop fields in new model
			foreach ($n['fields']['field'] as $f)
			{
				// if a field doesn't exist
				if ( ($k2 = array_search($f['name'], $existingFields)) === false )
				{
					$draftmodel['object'][$k]['fields']['field'][] = $f;
					$changes = true;
				}
			}
		}
		// object does not exist, we can simply add the whole object
		else
		{
			$draftmodel['object'][] = $n;
			$changes = true;
		}
	}
	
	if ($changes)
	{
		$str = 
'<?php
$model = <<<EOD
'.json_encode($draftmodel).'
EOD;
?>
';
		if (file_put_contents($draft,$str))
		{
			echo 'alert("changes were written to draft-model, please execute via modeling");';
		}
		else
		{
			echo 'alert("could not write to draft-model!");';
		}
		
	}
	else
	{
		echo 'alert("no changes detected");';
	}
	
}
else
{
	echo 'alert("could not load/save Model");';
}

?>
