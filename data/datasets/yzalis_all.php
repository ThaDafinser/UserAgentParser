<?php
$folder = 'vendor/yzalis/ua-parser/tests/UAParser/Tests/Fixtures';
$useFilename = basename(__FILE__);

$handle = opendir($folder);
if ($handle === false) {
    throw new \Exception('folder could not get opened');
}

$userAgents = [];

while ($filename = readdir($handle)) {
    if ($filename == '.' || $filename == '..' || strpos($filename, '.yml') === false) {
        continue;
    }
    
    if ($filename == 'custom_regexes.yml') {
        continue;
    }
    
    $fixtureData = \Spyc::YAMLLoad($folder . '/' . $filename);
    
    $userAgents = array_merge($userAgents, array_column($fixtureData, 0));
}
closedir($handle);

return $userAgents;
