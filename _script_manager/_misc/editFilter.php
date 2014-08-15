<?php
/*
 * 
 * extract availabe Language-Files to their locations (if location exists and is writable)
 * */
require '../../../header.php';
$filterpath = '../../../../../projects/'.$projectName.'/objects/__filter.php';

// save the thing
if (!empty($_POST['json']))
{

$php = '<?php
$stringified_filter = <<<EOD
'
.trim($_POST['json']).
'
EOD;
$filter = json_decode($stringified_filter, true);
';

file_put_contents($filterpath, $php);
}

require $filterpath;
?>

<!DOCTYPE html>
<html>
<head>
	<!--
		based on: http://jsoneditoronline.org
	-->
	<link rel="stylesheet" type="text/css" href="../../../../wizards/jsoneditor/jsoneditor/jsoneditor.css" />
	<link rel="stylesheet" type="text/css" href="../../../../wizards/jsoneditor/add/add.css" />
	<script type="text/javascript" src="../../../../wizards/jsoneditor/jsoneditor/jsoneditor.js"></script>
	
</head>
<body>

<button onclick="save()" class="jsoneditor-addbuttons jsoneditor-save" title="Save"></button>

<form id="hiddenForm" method="post" action="editFilter.php?project=<?php echo $projectName?>">
<input id="loaded" name="json" type="hidden" value='<?php echo $stringified_filter?>' />
</form>

<div id="jsoneditor" style="width: 100%; height: 95%;">

</div>

<script type="text/javascript" >
	
	// create the editor. Optionally, parameters json and options can be
	// specified in the constructor.
	var container = document.getElementById("jsoneditor");
	var editor = new jsoneditor.JSONEditor(container);
	editor.set( JSON.parse(document.getElementById("loaded").value) );
	
	// save json
	function save() {
		var json = editor.get();
		document.getElementById("loaded").value = JSON.stringify(json, null, 2);
		document.getElementById("hiddenForm").submit();
		//alert(JSON.stringify(json, null, 2));
		// save JSON to parent Field
		//parent.$('#'+parent.targetFieldId).val( JSON.stringify(json, null, 2) );
		//parent.saveContent("");
		//parent.$('#dialog2').dialog('close');
		//parent.hasCanged = true;
	}
</script>
</body>
</html>

