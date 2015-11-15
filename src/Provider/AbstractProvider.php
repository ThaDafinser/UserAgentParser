<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Result;

abstract class AbstractProvider
{
    private $version;

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
        if ($content === false) {
            return;
        }

        $content = json_decode($content);

        return $content->packages;
    }

    /**
     *
     * @param string $userAgent
     * @param array  $headers
     *
     * @throws Exception\NoResultFoundException
     *
     * @return Result\UserAgent
     */
    abstract public function parse($userAgent, array $headers = []);
}
