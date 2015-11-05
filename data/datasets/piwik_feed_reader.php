<?php
$fixtureData = \Spyc::YAMLLoad('vendor/piwik/device-detector/Tests/fixtures/feed_reader.yml');

$userAgents = array_column($fixtureData, 'user_agent');

return $userAgents;
