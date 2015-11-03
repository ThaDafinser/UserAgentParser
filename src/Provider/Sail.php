<?php
namespace UserAgentParser\Provider;

use Sail\Useragent;
use Sail\Parser;

class Sail extends AbstractProvider
{

    private $parser;

    public function getName()
    {
        return 'Sail';
    }

    /**
     *
     * @return \Sail\Useragent
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }
        
        $ua = new Useragent();
        $ua->pushParser(new Parser\UAS());
        
        $this->parser = $ua;
        
        return $this->parser;
    }

    public function parse($userAgent)
    {
        $parser = $this->getParser();
        
        $parser->setUA($userAgent);
        
        $raw = $parser->getInfo(true);
        
        return $this->returnResult([
            'raw' => $raw
        ]);
    }
}
