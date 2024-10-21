<?php

namespace UserAgentParser\Model;

/**
 * User agent model.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class UserAgent
{
    /**
     * Provider name.
     *
     * @var string
     */
    private $providerName;

    /**
     * Provider version.
     *
     * @var string
     */
    private $providerVersion;

    /**
     * @var Browser
     */
    private $browser;

    /**
     * @var RenderingEngine
     */
    private $renderingEngine;

    /**
     * @var OperatingSystem
     */
    private $operatingSystem;

    /**
     * @var Device
     */
    private $device;

    /**
     * @var Bot
     */
    private $bot;

    /**
     * @var mixed
     */
    private $providerResultRaw;

    /**
     * @param string     $provider
     * @param null|mixed $providerName
     * @param null|mixed $providerVersion
     */
    public function __construct($providerName = null, $providerVersion = null)
    {
        $this->providerName = $providerName;
        $this->providerVersion = $providerVersion;

        $this->browser = new Browser();
        $this->renderingEngine = new RenderingEngine();
        $this->operatingSystem = new OperatingSystem();
        $this->device = new Device();
        $this->bot = new Bot();
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @return string
     */
    public function getProviderVersion()
    {
        return $this->providerVersion;
    }

    public function setBrowser(Browser $browser)
    {
        $this->browser = $browser;
    }

    /**
     * @return Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    public function setRenderingEngine(RenderingEngine $renderingEngine)
    {
        $this->renderingEngine = $renderingEngine;
    }

    /**
     * @return RenderingEngine
     */
    public function getRenderingEngine()
    {
        return $this->renderingEngine;
    }

    public function setOperatingSystem(OperatingSystem $operatingSystem)
    {
        $this->operatingSystem = $operatingSystem;
    }

    /**
     * @return OperatingSystem
     */
    public function getOperatingSystem()
    {
        return $this->operatingSystem;
    }

    public function setDevice(Device $device)
    {
        $this->device = $device;
    }

    /**
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    public function setBot(Bot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @return Bot
     */
    public function getBot()
    {
        return $this->bot;
    }

    /**
     * @return bool
     */
    public function isBot()
    {
        if ($this->getBot()->getIsBot() === true) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        if ($this->getDevice()->getIsMobile() === true) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $providerResultRaw
     */
    public function setProviderResultRaw($providerResultRaw)
    {
        $this->providerResultRaw = $providerResultRaw;
    }

    /**
     * @return mixed
     */
    public function getProviderResultRaw()
    {
        return $this->providerResultRaw;
    }

    /**
     * @param mixed $includeResultRaw
     *
     * @return array
     */
    public function toArray($includeResultRaw = false)
    {
        $data = [
            'browser' => $this->getBrowser()->toArray(),
            'renderingEngine' => $this->getRenderingEngine()->toArray(),
            'operatingSystem' => $this->getOperatingSystem()->toArray(),
            'device' => $this->getDevice()->toArray(),
            'bot' => $this->getBot()->toArray(),
        ];

        // should be only used for debug
        if ($includeResultRaw === true) {
            $data['providerResultRaw'] = $this->getProviderResultRaw();
        }

        return $data;
    }
}
