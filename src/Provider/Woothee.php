<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;
use Woothee\Classifier;
use Woothee\DataSet;

class Woothee extends AbstractProvider
{
    protected $defaultValues = [
        DataSet::VALUE_UNKNOWN,
    ];

    private $parser;

    public function getName()
    {
        return 'Woothee';
    }

    public function getComposerPackageName()
    {
        return 'woothee/woothee';
    }

    /**
     *
     * @param Classifier $parser
     */
    public function setParser(Classifier $parser = null)
    {
        $this->parser = $parser;
    }

    /**
     *
     * @return Classifier
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $this->parser = new Classifier();

        return $this->parser;
    }

    /**
     *
     * @param array $resultRaw
     *
     * @return bool
     */
    private function hasResult(array $resultRaw)
    {
        foreach ($resultRaw as $value) {
            if ($this->isRealResult($value) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param  array   $resultRaw
     * @return boolean
     */
    private function isBot(array $resultRaw)
    {
        if (isset($resultRaw['category']) && $resultRaw['category'] === DataSet::DATASET_CATEGORY_CRAWLER) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Bot $bot
     * @param array     $resultRaw
     */
    private function hydrateBot(Model\Bot $bot, array $resultRaw)
    {
        $bot->setIsBot(true);

        if (isset($resultRaw['name']) && $this->isRealResult($resultRaw['name']) === true) {
            $bot->setName($resultRaw['name']);
        }
    }

    /**
     *
     * @param Model\Browser $browser
     * @param array         $resultRaw
     */
    private function hydrateBrowser(Model\Browser $browser, array $resultRaw)
    {
        if (isset($resultRaw['name']) && $this->isRealResult($resultRaw['name']) === true) {
            $browser->setName($resultRaw['name']);
        }

        if (isset($resultRaw['version']) && $this->isRealResult($resultRaw['version']) === true) {
            $browser->getVersion()->setComplete($resultRaw['version']);
        }
    }

    /**
     *
     * @param Model\Device $device
     * @param array        $resultRaw
     */
    private function hydrateDevice(Model\Device $device, array $resultRaw)
    {
        if (isset($resultRaw['category']) && $this->isRealResult($resultRaw['category']) === true) {
            $device->setType($resultRaw['category']);
        }

        if ($this->isMobile($resultRaw) === true) {
            $device->setIsMobile(true);
        }
    }

    /**
     *
     * @param  array $resultRaw
     * @return bool
     */
    private function isMobile(array $resultRaw)
    {
        /*
         * Available types...
         *
         * const DATASET_CATEGORY_PC = 'pc';
         * const DATASET_CATEGORY_SMARTPHONE = 'smartphone';
         * const DATASET_CATEGORY_MOBILEPHONE = 'mobilephone';
         * const DATASET_CATEGORY_CRAWLER = 'crawler';
         * const DATASET_CATEGORY_APPLIANCE = 'appliance';
         * const DATASET_CATEGORY_MISC = 'misc';
         */
        if (isset($resultRaw['category']) && $resultRaw['category'] === DataSet::DATASET_CATEGORY_SMARTPHONE) {
            return true;
        }

        if (isset($resultRaw['category']) && $resultRaw['category'] === DataSet::DATASET_CATEGORY_MOBILEPHONE) {
            return true;
        }

        return false;
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        $resultRaw = $parser->parse($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($resultRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection
         */
        if ($this->isBot($resultRaw) === true) {
            $this->hydrateBot($result->getBot(), $resultRaw);

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultRaw);
        // renderingEngine not available
        // operatingSystem filled OS is mixed! Examples: iPod, iPhone, Android...
        $this->hydrateDevice($result->getDevice(), $resultRaw);

        return $result;
    }
}
