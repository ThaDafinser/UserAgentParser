<?php
namespace UserAgentParser\Model;

/**
 * Browser model
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class Browser
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Version
     */
    private $version;

    public function __construct()
    {
        $this->version = new Version();
    }

    /**
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Version $version
     */
    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name'    => $this->getName(),
            'version' => $this->getVersion()->toArray(),
        ];
    }
}
