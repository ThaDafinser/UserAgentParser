<?php
namespace UserAgentParser\Provider;

use Woothee\Classifier;

class Woothee extends AbstractProvider
{
    private $parser;

    public function getName()
    {
        return 'Woothee';
    }

    /**
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

        if ($raw['category'] == 'crawler') {
            return $this->returnResult([
                'bot' => [
                    'isBot' => true,

                    'name' => null,
                    'type' => null,
                ],

                'raw' => $raw,
            ]);
        }

        $browserFamily = null;
        if (isset($raw['name']) && $raw['name'] != 'UNKNOWN') {
            $browserFamily = $raw['name'];
        }

        $browserVersion = null;
        if (isset($raw['version']) && $raw['version'] != 'UNKNOWN') {
            $browserVersion = $raw['version'];
        }

        $osFamily = null;
        if (isset($raw['os']) && $raw['os'] != 'UNKNOWN') {
            $osFamily = $raw['os'];
        }

        $osVersion = null;
        if (isset($raw['os_version']) && $raw['os_version'] != 'UNKNOWN') {
            $osVersion = $raw['os_version'];
        }

        return $this->returnResult([
            'browser' => [
                'family'  => $browserFamily,
                'version' => $browserVersion,
            ],

            'operatingSystem' => [
                'family'   => $osFamily,
                'version'  => $osVersion,
                'platform' => null,
            ],

            'raw' => $raw,
        ]);
    }
}
