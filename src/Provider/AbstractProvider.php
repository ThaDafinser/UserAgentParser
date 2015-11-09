<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Result;

abstract class AbstractProvider
{
    /**
     * Return the name of the provider
     * 
     * @return string
     */
    abstract public function getName();

    /**
     * @param string $userAgent
     *
     * @throws Exception\NoResultFoundException
     *
     * @return Result\UserAgent
     */
    abstract public function parse($userAgent);
}
