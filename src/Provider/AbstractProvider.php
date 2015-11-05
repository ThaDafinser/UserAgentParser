<?php
namespace UserAgentParser\Provider;

abstract class AbstractProvider
{
    private $userAgent;

    abstract public function parse($userAgent);

    abstract public function getName();

    protected function returnResult(array $data)
    {
        return array_merge($this->getDefaults(), $data);
    }

    private function getDefaults()
    {
        return [
            'browser' => [
                'family' => null,
                'version' => null
            ],
            
            'operatingSystem' => [
                'family' => null,
                'version' => null,
                'platform' => null
            ],
            
            'device' => [
                'brand' => null,
                'model' => null,
                'type' => null,
                
                'isMobile' => null
            ],
            
            'bot' => [
                'isBot' => null,
                
                'name' => null,
                'type' => null
            ]
        ];
    }
}
