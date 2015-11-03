<?php
namespace UserAgentParser\Provider;

class UAParser2 extends AbstractProvider
{

    private $parser;
    
    public function getName()
    {
        return 'UAParser2';
    }
    
    private function getParser()
    {
        if($this->parser !== null){
            return $this->parser;
        }
    
    
        $this->parser = $parser;
    
        return $this->parser;
    }

    public function parse($userAgent)
    {

    }
}
