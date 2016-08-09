<?php
namespace UserAgentParser\Provider;

use DateTime;
use PackageInfo\Exception\PackageNotInstalledException;
use PackageInfo\Package;
use UserAgentParser\Exception;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for all providers
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
abstract class AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name;

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage;

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName;

    /**
     * Per default the provider cannot detect anything
     * Activate them in $detectionCapabilities
     *
     * @var array
     */
    protected $allDetectionCapabilities = [
        'browser' => [
            'name'    => false,
            'version' => false,
        ],

        'renderingEngine' => [
            'name'    => false,
            'version' => false,
        ],

        'operatingSystem' => [
            'name'    => false,
            'version' => false,
        ],

        'device' => [
            'model'    => false,
            'brand'    => false,
            'type'     => false,
            'isMobile' => false,
            'isTouch'  => false,
        ],

        'bot' => [
            'isBot' => false,
            'name'  => false,
            'type'  => false,
        ],
    ];

    /**
     * Set this in each Provider implementation
     *
     * @var array
     */
    protected $detectionCapabilities = [];

    protected $defaultValues = [
        'general' => [],
    ];

    /**
     * Return the name of the provider
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the homepage
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Get the package name
     *
     * @return string null
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * Return the version of the provider
     *
     * @return string null
     */
    public function getVersion()
    {
        try {
            $package = new Package($this->getPackageName());

            return $package->getVersion();
        } catch (PackageNotInstalledException $ex) {
            return;
        }
    }

    /**
     * Get the last change date of the provider
     *
     * @return DateTime null
     */
    public function getUpdateDate()
    {
        try {
            $package = new Package($this->getPackageName());

            return $package->getVersionReleaseDate();
        } catch (PackageNotInstalledException $ex) {
            return;
        }
    }

    /**
     * What kind of capabilities this provider can detect
     *
     * @return array
     */
    public function getDetectionCapabilities()
    {
        return array_merge($this->allDetectionCapabilities, $this->detectionCapabilities);
    }

    /**
     *
     * @throws PackageNotLoadedException
     */
    protected function checkIfInstalled()
    {
        if (! Package::isInstalled($this->getPackageName())) {
            throw new PackageNotLoadedException('You need to install the package ' . $this->getPackageName() . ' to use this provider');
        }
    }

    /**
     *
     * @param  mixed   $value
     * @param  string  $group
     * @param  string  $part
     * @return boolean
     */
    protected function isRealResult($value, $group = null, $part = null)
    {
        $value = (string) $value;
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        $regexes = $this->defaultValues['general'];

        if ($group !== null && $part !== null && isset($this->defaultValues[$group][$part])) {
            $regexes = array_merge($regexes, $this->defaultValues[$group][$part]);
        }

        foreach ($regexes as $regex) {
            if (preg_match($regex, $value) === 1) {
                return false;
            }
        }

        return true;
    }

    protected function getRealResult($value, $group = null, $part = null)
    {
        if ($this->isRealResult($value, $group, $part) === true) {
            return $value;
        }

        return;
    }

    /**
     * Parse the given user agent and return a result if possible
     *
     * @param string $userAgent
     * @param array  $headers
     *
     * @throws Exception\NoResultFoundException
     *
     * @return Model\UserAgent
     */
    abstract public function parse($userAgent, array $headers = []);
}
