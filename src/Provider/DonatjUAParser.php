<?php
namespace UserAgentParser\Provider;

use UAParser\Parser;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class DonatjUAParser extends AbstractProvider
{

    public function getName()
    {
        return 'DonatjUAParser';
    }

    /**
     *
     * @param array $resultRaw            
     * @return boolean
     */
    private function hasResult(array $resultRaw)
    {
        if ($resultRaw['platform'] === null && $resultRaw['browser'] === null && $resultRaw['version'] === null) {
            return false;
        }
        
        return true;
    }

    public function parse($userAgent)
    {
        $resultRaw = parse_user_agent($userAgent);
        
        if ($this->hasResult($resultRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }
        
        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);
        
        /*
         * Bot detection - is currently not possible!
         */
        
        /*
         * Browser
         */
        $browser = $result->getBrowser();
        
        if ($resultRaw['browser'] !== null) {
            $browser->setName($resultRaw['browser']);
        }
        
        if ($resultRaw['version'] !== null) {
            $browser->getVersion()->setComplete($resultRaw['version']);
        }
        
        /*
         * operatingSystem
         * 
         * @todo $resultRaw['platform'] has sometimes informations about the OS or the device
         * ... maybe split it or how do that?
         */
        $operatingSystem = $result->getOperatingSystem();
        
        if ($resultRaw['platform'] !== null) {
            //$operatingSystem->setName($resultRaw['platform']);
        }
        
        return $result;
    }
}
