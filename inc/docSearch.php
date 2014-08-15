<?php
/**
 * super-simple string search for Documentation
 *
 */
// Start the timer - will be used with stats switch only
$startTime = microtime(true);

// recursive docGlob function based on http://www.php.net/manual/en/function.glob.php#87221
function docGlob($path = '')
{
    $paths = glob($path . '/*', GLOB_ONLYDIR);
    $files = glob($path . '/{*.md,*.txt,*.htm,*.html}', GLOB_BRACE);
    foreach ($paths as $path) {
        $files = array_merge($files, docGlob($path));
    }

    return $files;
}

// echo $_GET['file'];
$ppos = strpos($_GET['file'], '/doc/');
if ($ppos === false) {
    exit('Filepath "' . $_GET['file'] . '" is not valid!');
}

// call the glob-function
$files = docGlob('../' . $_GET['file']);
$phrase = strtolower(strip_tags($_GET['str']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>result</title>

    <meta charset="utf-8"/>

    <link rel="stylesheet" href="styles/showDoc.css"/>
</head>
<body>
<a href="javascript:history.back()">back</a>

<div id="wrapper" style="width:300px; border:1px solid #ccc; margin:30px auto">
    <h3>search for "<?php echo $phrase ?>"</h3>


    <?php
    $i = 0;
    foreach ($files as $file) {
        $str = strtolower(strip_tags(file_get_contents($file)));
        $pos = strpos($str, $phrase);
        if ($pos !== false) {
            // strip path + mime from filename
            $n = basename($file, '.txt');
            $n = basename($n, '.md');
            $n = basename($n, '.html');

            echo '<p><a href="../showDoc.php?file=' . substr($file, 3) . '">'
                . preg_replace(array('/^[0-9]+/', '/_/'), array('', ' '), $n)
                . '</a></p>';

            // Increase counter
            $i++;
        }
    }

    if ($i == 0) {
        echo '<p>no results</p>';
    }
    ?>
</div>
</body>
</html>
