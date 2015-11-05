<?php
$filename = 'vendor/donatj/phpuseragentparser/Tests/user_agents.json';
$useFilename = basename(__FILE__);

$result = json_decode(file_get_contents($filename));

$userAgents = [];
foreach ($result as $userAgent => $row) {
    $userAgents[] = $userAgent;
}

return $userAgents;
