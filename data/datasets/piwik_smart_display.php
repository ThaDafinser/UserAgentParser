<?php
$fixtureData = \Spyc::YAMLLoad('vendor/piwik/device-detector/Tests/fixtures/smart_display.yml');

$userAgents = array_column($fixtureData, 'user_agent');

return $userAgents;
