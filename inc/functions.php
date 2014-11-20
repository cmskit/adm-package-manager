<?php



/**
 * recursive glob
 * http://snipplr.com/view.php?codeview&id=16233
 * @param $pattern
 * @param int $flags
 * @param string $path
 * @return array
 */
function rglob($pattern, $flags = 0, $path = '')
{

    $fn = basename($path);
    if (file_exists($path . '.nomedia') || file_exists($path . '.no' . substr($pattern, 2)) || $fn == 'doc' || $fn == 'config') return array();

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
 * @param $str
 */
function createAlertIcon($str)
{
    global $html;
    $html .= '<a href="#"><img src="inc/styles/warning.png" /><span>' . $str . '</span></a>';
}

/**
 * @param $projectName
 * @param $m
 * @param $mainpath
 * @return string
 */
function getPackageList($projectName, $m, $mainpath)
{
    global $root;


    $dirs = glob($mainpath[2] . '*', GLOB_ONLYDIR);
    $html = '<h3>' . L('Packages') . '</h3>
	<div>
	';

    foreach ($dirs as $dir) {
        //
        if ($root == 2 || !file_exists($dir . '/.superadmin')) {
            $n = basename($dir);
            $pluginNames[] = $n;
            $highlight = (isset($_GET['ext']) && $_GET['ext'] == $n) ? ' style="font-weight:bold" ' : '';
            $html .= '<button ' . $highlight . 'type="button" onclick="location=\'?m=' . $m . '&project=' . $projectName . '&ext=' . $n . '\'">' . str_replace('_', ' ', $n) . '</button>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * collect informations from composer.json and show as image+tooltip
 * @param $extname
 * @param $mainpath
 * @param $current_ext_path
 * @return string
 */
function getPackageInfo($extname, $mainpath, $current_ext_path)
{
    $html = '<span class="ttips">
	    <span style="font-size:1.5em;font-weight:bold;vertical-align:top;">'
        . str_replace('_', ' ', $extname)
        . '</span> ';

    // try to get basic infos from composer.json
    if ($jstr = @file_get_contents($current_ext_path . '/composer.json')) {

        // try to decode the thing
        if ($infoson = json_decode($jstr, true)) {

            // if we have version-infos
            if (isset($infoson['version'])) {

                $status = 'unproductive';
                // see http://php.net/version_compare
                if(version_compare($infoson['version'], '0.0.1') >= 0) $status = 'alpha';
                if(version_compare($infoson['version'], '0.4.0') >= 0) $status = 'beta';
                if(version_compare($infoson['version'], '1.0.0') >= 0) $status = 'stable';

                $authors = isset($infoson['authors'])
                    ? '<br />Created by: ' . json_encode($infoson['authors'])
                    : '';
                $web = isset($infoson['homepage'])
                    ? '<br />Web: ' . $infoson['homepage']
                    : '';


                $html .= '<a href="#"><img src="inc/styles/status_' . $status . '.png" />
								<span>
								Version: ' . $infoson['version'] . ' (' . $status . ')' . $authors . $web . '
								</span>
							</a>';
            }

            /*
            // check installation-path if necessary
            if (isset($infoson['extra']['installer-paths'])) {

                createAlertIcon('your installation-path should be: ' . $infoson['extra']['installer-paths']);
            }

            // check availibility of wizards + other extensions
            foreach (array('wizards', 'extensions') as $what) {
                if (isset($infoson['system']['requirements'][$what])) {
                    foreach ($infoson['system']['requirements'][$what] as $wn => $wno) {
                        if ($wstr = file_get_contents('../../wizards/' . $wn . '/doc/info.php')) {
                            $warr = explode('EOD', $wstr);
                            $wson = json_decode($warr[1], true);
                            if ($wson['system']['version'] < $wno) createAlertIcon($wn . ' is too old!');
                        } else {
                            createAlertIcon($wn . ' is not available');
                        }
                    }
                }
            }*/
        }
    } else {
        $html .= '<img title="no informations available!!" src="inc/styles/warning.png" />';
    }
    $html .= '</span>';

    return $html;
}

//
/**
 * collect recursively all documentation-files from a folder
 * based on http://www.php.net/manual/en/function.glob.php#87221
 * @param $path
 * @return array
 */
function docGlob($path)
{
    $paths = glob($path . '/*', GLOB_ONLYDIR);
    $files = glob($path . '/{*.md,*.txt,*.htm,*.html}', GLOB_BRACE);
    foreach ($paths as $path) {
        $files = array_merge($files, docGlob($path));
    }

    return $files;
}

/**
 * create the list of documentation files
 * @param $lang
 * @param $current_ext_path
 * @param bool $sub_path
 * @return string
 */
function getDocList($lang, $current_ext_path, $sub_path = false)
{
    $html = '';
    if (!$sub_path) {
        $path = $current_ext_path . '/doc';
        $in_lang = '';
        $langFolderFound = file_exists($current_ext_path . '/doc/' . $lang);

        if ($langFolderFound) {
            $path = $current_ext_path . '/doc/' . $lang;
            $in_lang = ' (' . $lang . ') ';
        } // default language is "en"
        else {
            $path = $current_ext_path . '/doc/en';
            $in_lang = ' (en) ';
        }
    } else {
        $path = $sub_path;
        $in_lang = ' (' . $lang . ') ';
    }

    $docs = glob($path . '/{*.md,*.html}', GLOB_BRACE); //docGlob($path);
    if (count($docs) > 0) {

        $list = array();
        foreach ($docs as $doc) {
            $n = $doc;
            $n = basename($n, '.txt');
            $n = basename($n, '.md');
            $n = basename($n, '.html');

            $add = ",'&edit_me=" . substr($doc, strlen($current_ext_path) + 1) . "'";

            // dont show internal/hidden Files beginning with "."
            if (substr($n, 0, 1) != '.') {
                $list[] = '<button type="button" onclick="setFrame(\'showDoc\',\'' . $doc . '\'' . $add . ')">' .
                    preg_replace(array('/^[0-9]+/', '/_/'), array('', ' '), $n) . // replace Numbers at the Beginning and Underscores
                    //$n .
                    '</button>';
            }
        }

        if (count($list) > 0) {
            $html .= '<h3>'
                . ($sub_path
                    ? L('Doc') . ': ' . preg_replace(array('/^[0-9]+/', '/_/'), array('', ' '), basename($sub_path))
                    : L('Doc')
                )
                . ' ['
                . count($docs)
                . ']'
                . $in_lang
                . '</h3><div>'
                . implode('', $list)
                . '</div>';
        }

    }

    $dpaths = glob($path . '/*', GLOB_ONLYDIR);
    foreach ($dpaths as $dpath) {
        $html .= getDocList($lang, '', $dpath);
    }

    // If we have another language, we (try to) collect additional all documentations in english
    if (!$sub_path && $lang != 'en' && $langFolderFound) {
        $html .= getDocList('en', $current_ext_path);
    }
    return $html;
}

/**
 * @param $current_ext_path
 * @return string
 */
function getConfigList($current_ext_path)
{
    $html = '';
    $configs = glob($current_ext_path . '/config/*.php');

    if(file_exists($current_ext_path . '/composer.json')) $configs[] = $current_ext_path . '/composer.json';

    if (count($configs) > 0) {
        $html .= '<h3>' . L('Configuration') . ' (' . count($configs) . ')</h3>
		<div>';

        foreach ($configs as $config) {
            $n = basename($config, '.php');
            $html .= '<button type="button" onclick="setFrame(\'showConfig\',\'' . $n . '\')">'
                . str_replace('_', ' ', $n) . '</button>';
        }
        $html .= '</div>';
    }
    return $html;
}

/**
 * @param $type
 * @param $imp
 * @param $interfaces
 * @return string
 */
function getFileList($type, &$imp, &$interfaces)
{
    global $projectName, $current_ext_path, $interfaces;
    $interfaces[$type] = array();
    $fileList = rglob('*.' . $type, GLOB_MARK, $current_ext_path . '/');

    if (count($fileList) == 0) return '';

    //
    $importFiles = array( //see substr($n, -9);
        'odel.json' => 'model',
        'dumps.sql' => 'sql',
        'hooks.php' => 'hooks'
    );
    $c = 0;
    $ihtml = '';
    $html = '';
    foreach ($fileList as $file) {
        $n = substr($file, (strlen($current_ext_path) + 1)); // shortened pathname
        $fn = basename($n); // filename without path
        $fc = substr($fn, 0, 1);
        if ($fc != '_') {
            if ($fc != '.') {
                //
                $ihtml .= '<button type="button" title="' . $n . '" onclick="setFrame(\'showFile\',\'' . $n . '\')">'
                    . substr(basename($n), 0, 20) . '</button>';

                // we try to collect importable Files as well
                $sn = substr($n, -9);
                if (isset($importFiles[$sn])) {
                    $imp[] = '<button type="button" onclick="importFile(\'' . $importFiles[$sn] . '\',\'' . $n . '\')">'
                        . $n . '</button>';
                }
                $c++;
            }
        } else // we assume, this is a wizard
        {
            $a = explode('.', substr($fn, 1));
            $interfaces[$type][] = '<button type="button" onclick="frameTo(\'' . $file . '?project=' . $projectName . '\')">'
                . str_replace('_', ' ', array_shift($a)) . '</button>';
        }

    }
    // there are Files with this Type =>
    if ($c > 0) {
        $html = '<button type="button" onclick="$(\'#' . $type . '_list\').toggle(\'slow\')"><strong>' . strtoupper($type) . ' (' . $c . ')</strong></button>
		<div id="' . $type . '_list" style="display:none">' . $ihtml . '</div>';
    }

    return $html;
}

/**
 * @param $interfaces
 * @return string
 */
function getWizardList($interfaces)
{
    $html = '';
    $ints = array_merge($interfaces['html'], $interfaces['php']);
    if (count($ints) > 0) {

        $html .= '<h3>' . L('WIZARDS') . '</h3>
		<div>';
        // array(name,path)
        foreach ($ints as $int) {
            $html .= $int;
        }
        $html .= '
		</div>
		';
    }
    return $html;
}

/**
 * @param $imp
 * @return string
 */
function getImportList($imp)
{
    $html = '';
    if (count($imp) > 0) {
        $html .= '<h3>' . L('IMPORT') . '</h3>
		<div>';
        foreach ($imp as $i) {
            $html .= $i;
        }
        $html .= "</div>\n";
    }
    return $html;
}
