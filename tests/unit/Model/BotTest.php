<?php
namespace UserAgentParserTest;

use PHPUnit_Framework_TestCase;
use UserAgentParser\Model\Bot;

/**
 * @covers UserAgentParser\Model\Bot
 */
class BotTest extends PHPUnit_Framework_TestCase
{
    public function testIsBot()
    {
        $bot = new Bot();

        $this->assertFalse($bot->getIsBot());

        $bot->setIsBot(true);
        $this->assertTrue($bot->getIsBot());

        $bot->setIsBot(false);
        $this->assertFalse($bot->getIsBot());
    }

    public function testName()
    {
    }
}
