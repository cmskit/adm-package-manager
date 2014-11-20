<?php
/**
 *
 */
require '../../../header.php';
require 'class.tpl.php';
include '../helper.php';

$tpl = new kitTpl();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family:sans-serif;
            font-size: .9em;
        }
        .error {
            color: #c33;
        }
        .success {
            color: #3a3;
        }
    </style>
</head>
<body>
<a href="../?project=<?php echo $projectName?>">back</a>
<h3>Template parser</h3>
<?php
    if($superroot) echo buildFileSelectList('Backend', $backend, '*.xhtml');
    echo buildFileSelectList('Project '.$projectName, $frontend, '*.xhtml');
?>

<div class="output">
<?php
if(!empty($_POST['path']) && in_array($_POST['path'], $fileList)) {

    if(!empty($_POST['debug'])) {
        $tpl->debug = true;
        $tpl->br = "\n        ";
        $tpl->formatOutput = true;
    }

    echo '<h3>'.$_POST['path'].'</h3>';
    echo $tpl->parse($_POST['path']);
}
?>
</div>
</body>
</html>
