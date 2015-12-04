<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;

abstract class AbstractProvider
{
    /**
     *
     * @var string
     */
    private $version;

    protected $defaultValues = [];

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

    /**
     * Return the name of the provider
     *
     * @return string
     */
    abstract public function getName();

    abstract public function getComposerPackageName();

    /**
     * Return the version of the provider
     *
     * @return string null
     */
    public function getVersion()
    {
        if ($this->version !== null) {
            return $this->version;
        }

        if ($this->getComposerPackageName() === null) {
            return;
        }

        $packages = $this->getComposerPackages();

        if ($packages === null) {
            return;
        }

        foreach ($packages as $package) {
            if ($package->name === $this->getComposerPackageName()) {
                $this->version = $package->version;

                break;
            }
        }

        return $this->version;
    }

    /**
     *
     * @return \stdClass null
     */
    private function getComposerPackages()
    {
        if (! file_exists('composer.lock')) {
            return;
        }

        $content = file_get_contents('composer.lock');
        if ($content === false || $content === '') {
            return;
        }

        $content = json_decode($content);

        return $content->packages;
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
     * @param  mixed   $value
     * @param  array   $additionalDefaultValues
     * @return boolean
     */
    protected function isRealResult($value, array $additionalDefaultValues = [])
    {
        if ($value === '' || $value === null) {
            return false;
        }

        $value = (string) $value;

        $defaultValues = array_merge($this->defaultValues, $additionalDefaultValues);

        if (in_array($value, $defaultValues, true) === true) {
            return false;
        }

        return true;
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
