<?php
namespace UserAgentParser\Provider;

class Chain extends AbstractProvider
{

    /**
     *
     * @var AbstractProvider[]
     */
    private $providers = [];

    private $executeAll = false;

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

    public function setExecuteAll($bool = true)
    {
        $this->executeAll = $bool;
    }

    public function parse($userAgent)
    {
        $result = [];
        
        foreach ($this->getProviders() as $provider) {
            /* @var $provider \UserAgentParser\Provider\AbstractProvider */
            
            try {
                $start = microtime(true);
                $row = $provider->parse($userAgent);
                $end = microtime(true);
                
                $row = array_merge([
                    'provider' => $provider->getName(),
                    'parseTime' => $end - $start
                ], $row);
                
                $result[] = $row;
                
                if ($this->executeAll !== true) {
                    break;
                }
            } catch (\Exception $ex) {
                var_dump($ex);
                exit();
            }
        }
        
        return $result;
    }
}
