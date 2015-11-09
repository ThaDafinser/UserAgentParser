<?php
namespace UserAgentParser\Model;

final class UserAgent
{
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

    public function __construct()
    {
        $this->browser         = new Browser();
        $this->renderingEngine = new RenderingEngine();
        $this->operatingSystem = new OperatingSystem();
        $this->device          = new Device();
        $this->bot             = new Bot();
    }

    /**
     * @param Browser $browser
     */
    public function setBrowser(Browser $browser)
    {
        $this->browser;
    }

    /**
     * @return Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * @param RenderingEngine $renderingEngine
     */
    public function setRenderingEngine(RenderingEngine $renderingEngine)
    {
        $this->renderingEngine;
    }

    /**
     * @return RenderingEngine
     */
    public function getRenderingEngine()
    {
        return $this->renderingEngine;
    }

    /**
     * @param OperatingSystem $operatingSystem
     */
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

    /**
     * @param Device $device
     */
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

    /**
     * @param Bot $bot
     */
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
     * Special function to detect if it's a bot
     * If it's a bot only getBot() part has values in UserAgent.
     *
     * @return bool
     */
    public function isBot()
    {
        if ($this->getBot()->getName() !== null || $this->getBot()->getType() !== null) {
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
     * @return array
     */
    public function toArray($includeResultRaw = false)
    {
        $data = [
            'browser'          => $this->getBrowser()->toArray(),
            'renderingEnginge' => $this->getRenderingEngine()->toArray(),
            'operatingSystem'  => $this->getOperatingSystem()->toArray(),
            'device'           => $this->getDevice()->toArray(),
            'bot'              => $this->getBot()->toArray(),
        ];

        // should be only used for debug
        if ($includeResultRaw === true) {
            $data['providerResultRaw'] = $this->getProviderResultRaw();
        }

        return $data;
    }
}
