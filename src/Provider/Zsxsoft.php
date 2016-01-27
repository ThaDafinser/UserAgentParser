<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

class Zsxsoft extends AbstractProvider
{

    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'Zsxsoft';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://github.com/zsxsoft/php-useragent';

    /**
     * Composer package name
     *
     * @var string
     */
    protected $packageName = 'zsxsoft/php-useragent';

    public function parse($userAgent, array $headers = [])
    {
        $parser = new \UserAgent();
        $parser->analyze($userAgent);
        
        var_dump($parser->browser);
        var_dump($parser->os);
        var_dump($parser->platform);
        var_dump($parser->device);
    }
}
