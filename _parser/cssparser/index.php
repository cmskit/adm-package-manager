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
 * CSS concatenation + compression + theming
 */

include '../../../header.php';
include '../helper.php';

$headstr = (isset($_GET['nocompress']) ? 'Stylesheets concatenated' : 'Stylesheets packed');
$headline = '/* AUTO CREATED FILE (built at ' . date('Y-m-d H:i:s', time()) . ") do not edit */\n";


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>css packer</title>
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
<h3>CSS parser</h3>

<?php

// draw the forms
if($superroot) echo buildFileSelectList('Backend', $backend, 'pack_css.json');
echo buildFileSelectList('Project '.$projectName, $frontend, 'pack_css.json');

if(!empty($_POST['path']) && in_array($_POST['path'], $fileList)) {


    // grab the parameters of all jQuery-UI - styles
    $uiFolders = glob($backend . '/../vendor/cmskit/jquery-ui/themes/*', GLOB_ONLYDIR);
    $styles = array();
    foreach ($uiFolders as $uiFolder) {
        if (@$paramstr = file_get_contents($uiFolder . '/parameter.txt')) {
            parse_str($paramstr, $styles[basename($uiFolder)]);
        }
    }

    // define some variables
    $dir = dirname($_POST['path']);
    $arr = json_decode(file_get_contents($_POST['path']), true);

    if (empty($_POST['lang'])) $_POST['lang'] = 'en';

    // base-path (usually the template directory, otherwise the directory with the json file)
    if(empty($arr['base'])) {
        $arr['base'] = $dir;
    }else {
        $arr['base'] = str_replace('DIR', $dir, $arr['base']);
    }


    $msg = (isset($_POST['debug']) ? 'Styles concatenated' : 'Styles packed') . ' (' . time() . ')';
    $headline = '/* AUTO-CREATED FILE (built at ' . date('Y-m-d H:i:s', time()) . ") do not edit! */\n";



    $str = $headline;
    foreach ($arr['src'] as $src) {
        $p = str_replace(
            array('DIR', 'VENDOR', 'BACKEND', 'FRONTEND'),
            array($dir, dirname($backend).'/vendor', $backend, $frontend),
            $src['path']
        );
        if (!file_exists($p)) {
            exit('<p>' . $p . ' is missing!</p>');
        }
        $s = file_get_contents($p);

        $str .= ((!$src['compress'] || !empty($_POST['debug'])) ? $s : compress($s, $src['no_commenthead']));
        $str .= "\n";

        // should we replace placeholders with UI-values?
        if ($arr['lessify']) {
            foreach ($styles as $k => $v) {
                $out = str_replace(
                    array('DIR', 'BACKEND', 'FRONTEND', 'UI'),
                    array($arr['base'], $backend, $frontend, $k),
                    $arr['out']
                );

                // build relative path pointing to the UI-Directory
                $v['BASEPATH'] = relativePath(dirname($o), $backend . '/../vendor/cmskit/jquery-ui/themes/' . $k);

                // save file with replacements
                putFile($out, strtr($str, $v));
            }
        } else {
            $out = str_replace(
                array('DIR', 'BACKEND'),
                array($arr['base'], $backend),
                $arr['out']
            );
            putFile($out, $str);
        }



    }

    echo $links;

}
?>
</body>

</html>
