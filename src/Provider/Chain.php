<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;

class Chain extends AbstractProvider
{
    /**
     *
     * @var AbstractProvider[]
     */
    private $providers = [];

    /**
     *
     * @param AbstractProvider[] $providers
     */
    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    public function getName()
    {
        return 'Chain';
    }

    public function getComposerPackageName()
    {
        return;
    }

    /**
     *
     * @return AbstractProvider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    public function parse($userAgent, array $headers = [])
    {
        foreach ($this->getProviders() as $provider) {
            /* @var $provider \UserAgentParser\Provider\AbstractProvider */

            try {
                return $provider->parse($userAgent, $headers);
            } catch (Exception\NoResultFoundException $ex) {
                // just catch this and continue to the next provider
            }
        }

        throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
    }
}
