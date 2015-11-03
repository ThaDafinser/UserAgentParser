<?php
namespace UserAgentParser\Provider;

// include 'vendor/whichbrowser/whichbrowser/libraries/whichbrowser.php';
include 'vendor/whichbrowser/whichbrowser/libraries/whichbrowser.php';

class WhichBrowser extends AbstractProvider
{

    private $parser;

    public function getName()
    {
        return 'WhichBrowser';
    }

    /**
     *
     * @return \WhichBrowser
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }
        
        $parser = new \WhichBrowser([]);
        
        $this->parser = $parser;
        
        return $this->parser;
    }

    public function parse($userAgent)
    {
        $parser = $this->getParser();
        $parser->analyseUserAgent($userAgent);

        $raw = $parser->toArray();
        
        return $this->returnResult([
            'raw' => $raw
        ]);
    }
}