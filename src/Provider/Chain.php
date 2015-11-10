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

    /**
     *
     * @return AbstractProvider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    public function parse($userAgent)
    {
        foreach ($this->getProviders() as $provider) {
            /* @var $provider \UserAgentParser\Provider\AbstractProvider */

            try {
                return $provider->parse($userAgent);
            } catch (Exception\NoResultFoundException $ex) {
            }
        }

        throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
    }
}
