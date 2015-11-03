<?php
namespace UserAgentParser\Provider;

use UAParser\Parser;

class PhpUserAgent extends AbstractProvider
{

    public function getName()
    {
        return 'PhpUserAgent';
    }

    public function parse($userAgent)
    {
        $result = parse_user_agent($userAgent);
        
        // platform is not useable currently...because it's mixed
        // maybe an idea to split it into OS and model?
        
        return $this->returnResult([
            'browser' => [
                'family' => $result['browser'],
                'version' => $result['version']
            ],
            
            'raw' => $result
            
        ]);
    }
}
