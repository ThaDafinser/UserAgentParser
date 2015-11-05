<?php
$fixtureData = \Spyc::YAMLLoad('vendor/piwik/device-detector/Tests/fixtures/phablet.yml');

$userAgents = array_column($fixtureData, 'user_agent');

return $userAgents;
