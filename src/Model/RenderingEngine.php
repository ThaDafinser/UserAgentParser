<?php
namespace UserAgentParser\Model;

final class RenderingEngine
{

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var Version
     */
    private $version;

    public function __construct()
    {
        $this->version = new Version();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param Version $version            
     */
    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    /**
     *
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'version' => $this->getVersion()->toArray()
        ];
    }
}
