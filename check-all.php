<?php
/**
 * check-all.php for deb-repo-check
 *
 * @author Piotr Rybałtowski <piotrek@rybaltowski.pl>
 */

spl_autoload_register(function ($class) {
    $path = explode('\\', $class);
    array_unshift($path, __DIR__, 'php');
    $path = implode(DIRECTORY_SEPARATOR, $path) . '.php';
    require_once $path;
});

/**
 * @param string $message
 */
function writeLine($message = '')
{
    echo $message, PHP_EOL;
}

/**
 * @param string $message
 * @param int $exitCode
 */
function writeLineAndExit($message = '', $exitCode = 0)
{
    writeLine($message);
    exit($exitCode);
}

/**
 * @param bool $error
 */
function usageExit($error = false)
{
    global $argv;
    writeLineAndExit('Usage: ' . $argv[0] . ' [source-list-dir=/etc/apt/sources.list.d]',
      $error ? 1 : 0);
}


if (2 < $argc) {
    usageExit(true);
}

if (2 == $argc) {
    $path = $argv[1];
} else {
    $path = '/etc/apt/sources.list.d';
}

if ('-h' == $path || '--help' == $path) {
    usageExit();
}
if (!is_dir($path) || !is_readable($path)) {
    writeLineAndExit($path . ' is not a valid directory.', 100);
}

writeLine('Checking directory: ' . $path);

$dir = new DirectoryIterator($path);
$ext = '.list';
foreach ($dir as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $filename = $file->getFilename();
    if (substr($filename, -5) != $ext) {
        continue;
    }
    $repo = new \DebRepoCheck\ConfigFile($file);
    $repo->check();

    writeLine();
    writeLine($repo->filename() . ':');
    foreach ($repo->lines() as $line) {
        if ($line->isRem()) {
            writeLine('-- REM LINE --');
            continue;
        }
        if (($error = $line->isFault())) {
            if (is_string($error)) {
                writeLine('-- ERROR: ' . $error . ' --');
            } else {
                writeLine('-- ERROR --');
            }
            continue;
        }
//        var_dump([
//            $line->line(),
//            $line->uri(),
//            $line->release(),
//            $line->section(),
//        ]);
    }
}

