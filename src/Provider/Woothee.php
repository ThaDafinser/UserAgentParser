<?php
namespace UserAgentParser\Provider;

use DeviceDetector\DeviceDetector as PiwikDeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract as PiwikDeviceParserAbstract;
use Doctrine\Common\Cache;
use Woothee\Classifier;

class Woothee extends AbstractProvider
{

    private $parser;

    public function getName()
    {
        return 'Woothee';
    }

    /**
     *
     * @return \Woothee\Classifier
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }
        
        $parser = new Classifier();
        
        $this->parser = $parser;
        
        return $this->parser;
    }

    public function parse($userAgent)
    {
        $parser = $this->getParser();
        
        $raw = $parser->parse($userAgent);
        
        return $this->returnResult([
            'raw' => $raw
        ]);
    }
}