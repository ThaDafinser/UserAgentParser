<?php

namespace UserAgentParserTest\Unit\Provider;

/**
 * A interface with required test methods for each provider.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
interface RequiredProviderTestInterface
{
    public function testGetName();

    public function testGetHomepage();

    public function testGetPackageName();

    public function testVersion();

    public function testUpdateDate();

    public function testDetectionCapabilities();

    public function testParseNoResultFoundException();

    public function testIsRealResult();

    public function testProviderNameAndVersionIsInResult();
}
