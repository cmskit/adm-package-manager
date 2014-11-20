<?php
require '../header.php';

if (empty($_GET['ext'])) exit('<h3>Select extension first!</h3>');

require 'inc/path.php';
require 'inc/functions.php';
require '../../../vendor/pclzip/pclzip/pclzip.lib.php';

// seach for dirs in subdir "doc"
$docpath = $mainpath[2] . $_GET['ext'] . '/doc';

if (!is_writable($docpath)) exit('<h3>Directory "doc" is not writable!</h3>');

$ddirs = glob($docpath . '/*', GLOB_ONLYDIR | GLOB_NOSORT);

if (count($ddirs) > 0) {
    $zipName = time() . '_' . md5(rand()) . '.zip';
    $zipPath = $docpath . '/' . $zipName;
    $archive = new PclZip ($zipPath);

    foreach ($ddirs as $ddir) {
        $z = $archive->add($ddir, PCLZIP_OPT_REMOVE_PATH, $docpath);
    }
    @chmod($zipPath, 0777);
    $zipSize = filesize($zipPath) . ' B';
    //print_r($ddirs);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>cms-kit Extension-Management</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="inc/styles/style.css"/>
</head>
<body>

<?php
if (count($ddirs) > 0) {
    echo '
	<h3>backup created!</h3>
	<p>A backup copy of all documentations in "' . $_GET['ext'] . '" was created and saved to:</p>
	<p>"' . $_GET['ext'] . '/doc/<b>' . $zipName . '</b>" (size: ' . $zipSize . ')</p>
	<p><a href="' . $zipPath . '">Download zip</a></p>
	';
} else {
    echo '<h3>Nothing to save!</h3>';
}
?>
</body>
</html>
