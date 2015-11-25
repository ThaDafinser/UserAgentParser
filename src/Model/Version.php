<?php
namespace UserAgentParser\Model;

class Version
{
    /**
     * 
     * @var integer
     */
    private $major;

    /**
     * 
     * @var integer
     */
    private $minor;

    /**
     * 
     * @var integer
     */
    private $patch;

    /**
     * 
     * @var string
     */
    private $complete;

    /**
     *
     * @param integer $major
     */
    public function setMajor($major)
    {
        if ($major !== null) {
            $major = (int) $major;
        }

        $this->major = $major;

        $this->calculateComplete();
    }

    /**
     *
     * @return integer
     */
    public function getMajor()
    {
        return $this->major;
    }

    /**
     *
     * @param integer $minor
     */
    public function setMinor($minor)
    {
        if ($minor !== null) {
            $minor = (int) $minor;
        }

        $this->minor = $minor;

        $this->calculateComplete();
    }

    /**
     *
     * @return integer
     */
    public function getMinor()
    {
        return $this->minor;
    }

    /**
     *
     * @param integer $patch
     */
    public function setPatch($patch)
    {
        if ($patch !== null) {
            $patch = (int) $patch;
        }

        $this->patch = $patch;

        $this->calculateComplete();
    }

    /**
     *
     * @return integer
     */
    public function getPatch()
    {
        return $this->patch;
    }

    /**
     * Set from the complete version string.
     *
     * @param string $complete
     */
    public function setComplete($complete)
    {
        // check if the version is all 0 -> so wrong result
        $left = preg_replace('/[0.]/', '', $complete);
        if ($left === '') {
            $complete = null;
        }

        $this->complete = $complete;

        $this->hydrateVersionParts($complete);
    }

    /**
     *
     * @return string
     */
    public function getComplete()
    {
        if ($this->complete === null) {
            $this->calculateComplete();
        }

        return $this->complete;
    }

    /**
     *
     * @param string $completeVersion
     */
    private function hydrateVersionParts($completeVersion)
    {
        $parts = $this->getParts($completeVersion);

        $this->setMajor($parts['major']);
        $this->setMinor($parts['minor']);
        $this->setPatch($parts['patch']);
    }

    /**
     *
     * @param string $version
     *
     * @return array
     */
    private function getParts($version)
    {
        $parts = explode('.', $version);

        $versionParts = [
            'major' => null,
            'minor' => null,
            'patch' => null,
        ];

        if (isset($parts[0]) && $parts[0] != '') {
            $versionParts['major'] = (int) $parts[0];
        }
        if (isset($parts[1]) && $parts[1] != '') {
            $versionParts['minor'] = (int) $parts[1];
        }
        if (isset($parts[2]) && $parts[2] != '') {
            $versionParts['patch'] = (int) $parts[2];
        }

        return $versionParts;
    }

    /**
     *
     * @return string
     */
    private function calculateComplete()
    {
        if ($this->getMajor() === null) {
            return;
        }

        $version = $this->getMajor();

        if ($this->getMinor() !== null) {
            $version .= '.' . $this->getMinor();
        }

        if ($this->getPatch() !== null) {
            $version .= '.' . $this->getPatch();
        }

        $this->complete = $version;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'major' => $this->getMajor(),
            'minor' => $this->getMinor(),
            'patch' => $this->getPatch(),

            'complete' => $this->getComplete(),
        ];
    }
}
