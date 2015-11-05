<?php
$fixtureData = \Spyc::YAMLLoad('vendor/piwik/device-detector/Tests/fixtures/portable_media_player.yml');

$userAgents = array_column($fixtureData, 'user_agent');

return $userAgents;
