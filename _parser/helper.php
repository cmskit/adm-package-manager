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
 * compress javascript + css
 *
 * @param string $str uncompressed String
 * @param bool $noCommentHead no comment header (usually containing some licence infos)
 * @return compressed String
 */
function compress($str, $noCommentHead = false)
{
    //grab+hold the first comment-block
    $arr = explode('*/', $str);
    $commentHead = array_shift($arr) . "*/\n";

    $str = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", '', $str); // remove comments
    $str = preg_replace('/(\\t|\\r|\\n)/', '', $str); // remove tabs + line-feeds ( agressive method )

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

$links = '';
/**
 * @param $path
 * @param $str
 */
function putFile($path, $str)
{
    global $links;
    if (@file_put_contents($path, $str)) {
        @chmod($path, 0766);
        $rel = relativePath(__DIR__, dirname($path));
        //$links .= '<p><a target="_blank" href="' . $rel . '">' . $path . '</a></p>';
        $links .= '<p>' . $path . '</p>';
    } else {
        exit('<p>"' . $path . '" could not be written!</p>');
    }
}

function collectFiles($src) {

    global $dir, $backend, $frontend;

    $p = str_replace(
        array('DIR', 'VENDOR', 'BACKEND', 'FRONTEND'),
        array($dir, dirname($backend).'/vendor', $backend, $frontend),
        $src['path']
    );

    $s = '';

    // grab the file(s) by glob (respecting wildcards within the path)
    $files = glob($p);

    foreach($files as $file) {

        // if a filename is excluded skip it
        if(!isset($src['exclude']) || !in_array(basename($file), $src['exclude'])) {
            $s .= file_get_contents($file);
        }
    }
    return $s;
}


/**
 * @param $pattern
 * @param int $flags
 * @param string $path
 * @return array
 */
$fileList = array();
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
function buildFileSelectList($name, $path, $str)
{
    global $projectName, $fileList;
    $html = '<form method="post" action="index.php?project='.$projectName.'">';
    $html .= '<h4>'.$name.'</h4>';
    $files = rglob($str, GLOB_MARK, $path);
    $html .= '<select name="path"><option value="">Select file ('.count($files).' file(s) found)</option>';
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