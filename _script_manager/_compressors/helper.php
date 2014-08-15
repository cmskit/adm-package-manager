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

/**
 * compress javascript + css
 *
 * @param string $str uncompressed String
 * @param bool $noCommentHead no comment header (usually containing some licence infos)
 * @return compressed String
 */
function compress($str, $noCommentHead = false)
{
    //grab the first comment-block
    $arr = explode('*/', $str);
    $commentHead = array_shift($arr) . "*/\n";

    $str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $str); // remove comments
    $str = preg_replace('/(\\t|\\r|\\n)/', '', $str); // remove tabs + line-feeds ( agressive Method )

    //// temorary methods ( less agressive )
    //// $str = preg_replace('/(\\t)/','', $str); // remove only the tabs
    //// $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $str); // remove blank lines

    // replace multiple blanks with one
    $str = preg_replace('/( )+/', ' ', $str);

    // replace blanks/line-feeds between some characters
    $str = str_replace(
        array(' = ', ') {', '( ', ' )', ': ', "}\n}", ";\n}"),
        array('=', '){', '(', ')', ':', '}}', ';}'),
        $str
    );
    if ($noCommentHead) {
        $str = "\n" . $commentHead . "\n" . $str;
    }
    return $str;
}


/**
 * @param $what
 * @return array
 */
function getPaths($what, $m)
{
    global $frontend, $backend;
    $basepath = ($m==0?$frontend:$backend) . '/templates';
    $paths = array();

    $templateFolders = glob($basepath . '/*', GLOB_ONLYDIR);

    foreach ($templateFolders as $templateFolder) {
        $name = basename($templateFolder);
        $configPath = $templateFolder . '/config/packScripts.php';
        if (file_exists($configPath)) {
            $config = '';
            include $configPath;
            if ($j = json_decode($config, true)) {
                $paths[$name] = $j[$what];
            }
            $paths[$name]['base'] = $templateFolder;
        }
    }
    return $paths;
}

/**
 * @param $templatename
 * @param $o
 * @param $str
 */
function putFile($templatename, $o, $str)
{
    global $links;
    if (@file_put_contents($o, $str)) {
        @chmod($o, 0766);
        $rel = relativePath(dirname(__FILE__), $o);
        $links .= '<p><a target="_blank" href="' . $rel . '">' . $templatename . ' => ' . basename($o) . '</a></p>';
    } else {
        exit('<p>"' . $o . '" could not be written!</p>');
    }
}


