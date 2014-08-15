<?php
/**
 *
 */
require '../../../header.php';
require 'class.tpl.php';


$tpl = new kitTpl();
$fileList = array();

/**
 * @param $pattern
 * @param int $flags
 * @param string $path
 * @return array
 */
function rglob($pattern, $flags = 0, $path = '')
{
    if (!$path && ($dir = dirname($pattern)) != '.') {
        if ($dir == '\\' || $dir == '/') $dir = '';
        return rglob(basename($pattern), $flags, $dir . '/');
    }
    $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
    $files = glob($path . $pattern, $flags);
    foreach ($paths as $p) $files = array_merge($files, rglob($pattern, $flags, $p . '/'));
    return $files;
}

/**
 * @param $path
 * @return string
 */
function buildTplList($name, $path)
{
    global $projectName, $fileList;
    $html = '<form method="post" action="index.php?project='.$projectName.'">';
    $html .= '<h4>'.$name.'</h4>';
    $files = rglob("*.xhtml", GLOB_MARK, $path);
    $html .= '<select name="path"><option value="">Select template ('.count($files).' template(s) found)</option>';
    foreach($files as $file) {
        $html .= '<option value="'.$file.'">'.substr($file, strlen($path)).'</option>';
    }
    $html .= '</select>';
    $html .= '<input type="submit" value="parse" />
    <input type="checkbox" name="debug" /> debug
    </form>';

    $fileList = array_merge($fileList, $files);
    return $html;
}

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
<h3>Template parser</h3>
<?php
    if($superroot) echo buildTplList('Backend', $backend);
    echo buildTplList('Project '.$projectName, $frontend);
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
