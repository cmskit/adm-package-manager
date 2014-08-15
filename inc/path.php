<?php
/**
 *
 */

require_once $backend . '/inc/php/functions.php';

$targets = array($frontend, $backend);


$gets = array('ext', 'm');
foreach ($gets as $k) {
    if (!isset($_GET[$k])) $_GET[$k] = '';
}

// main-path (label, ??, path, superadmin_only)

@$m = intval($_GET['m']);

$mainpaths = array(
    array(L('project_extensions'), 'project', '../../../projects/' . $projectName . '/extensions/', false),
    array(L('global_extensions'), 'global', '../../extensions/', true),
    array(L('wizards'), false, '../../wizards/', true),
    array(L('admin_wizards'), false, '../../admin/', true),
    array(L('backend_templates'), false, '../../templates/', false),
);

$mainpath = $mainpaths[$m];


if (!$superroot) {
    // remove
    $i = 0;
    foreach ($mainpaths as $mm) {
        if ($mm[3]) $mainpaths[$i] = false;
        $i++;
    }
    // stop access if Extension is for Superadmins only!
    if ($mainpath[3]) exit('access for Super-Admin only!');
}
