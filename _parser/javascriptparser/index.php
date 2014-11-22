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
 * Javascript concatenation + compression + translation
 */
include '../../../header.php';
include '../helper.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Javascript-Packer</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <style>
        body {
            background: #eee;
            font: .9em "Trebuchet MS", sans-serif;
        }

        a, a:visited {
            text-decoration: underline;
            color: #00f;
        }
    </style>
</head>
<body>
<a href="../?project=<?php echo $projectName?>">back</a>
<h3>Javascript parser</h3>
<?php

// draw the forms
if($superroot) echo buildFileSelectList('Backend', $backend, 'pack_js.json');
echo buildFileSelectList('Project '.$projectName, $frontend, 'pack_js.json');

if(!empty($_POST['path']) && in_array($_POST['path'], $fileList)) {


    $dir = dirname($_POST['path']);
    $arr = json_decode(file_get_contents($_POST['path']), true);

    if (empty($_POST['lang'])) $_POST['lang'] = 'en';

    // base-path (usually the template directory, otherwise the directory with the json file)
    if(empty($arr['base'])) {
        $arr['base'] = $dir;
    }else {
        $arr['base'] = str_replace('DIR', $dir, $arr['base']);
    }


    $msg = (isset($_POST['debug']) ? 'Scripts concatenated' : 'Scripts packed') . ' (' . time() . ')';
    $headline = '// AUTO-CREATED FILE (built at ' . date('Y-m-d H:i:s', time()) . ") do not edit!\n";

    // try to get language-labels
    @include($arr['base'] . '/locales/' . $_POST['lang'] . '.php');

    $str = $headline;
    // loop all paths in "src"
    foreach ($arr['src'] as $src) {

        $s = collectFiles($src);
        $str .= ((!$src['compress'] || !empty($_POST['debug'])) ? $s : compress($s, $src['no_commenthead']));


        // translate language-calls found in the code (the L-function)
        if ($src['translate'] && $LL) {
            $str = preg_replace("/_\('(\w+)'\)/e", "LL('\\1')", $str);
        }
        $str .= "\n";

    }

    $o = str_replace(
        array('DIR', 'BACKEND', 'FRONTEND', 'LANG'),
        array($dir, $backend, $frontend, $_POST['lang']),
        $arr['out']
    );

    putFile($o, $str);
    echo '<p>'.$msg.'</p>';
    echo $links;

}
?>


<script>
    var forms = document.getElementsByTagName('form');
    for(e in forms) {
        forms[e].innerHTML += '<input type="text" name="lang" size="2" value="en" title="language shortcut" />';
    }
</script>
</body>
</html>
