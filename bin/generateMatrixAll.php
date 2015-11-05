<?php
include_once 'bootstrap.php';

$handle = opendir('data/datasets');
if ($handle === false) {
    throw new \Exception('folder could not get opened');
}

while ($filename = readdir($handle)) {
    if ($filename == '.' || $filename == '..' || strpos($filename, '.php') === false) {
        continue;
    }
    
    $useFilename = str_replace('.php', '.html', $filename);
    
    
    $userAgents = include 'data/datasets/' . $filename;
    if (! is_array($userAgents)) {
        throw new \Exception('could not load array from: ' . $filename);
    }
    
    echo '~~~Now dataset: ' . $filename . '~~~' . PHP_EOL;
    
    
    // generate
    require 'generateMatrix.php';
}
closedir($handle);
