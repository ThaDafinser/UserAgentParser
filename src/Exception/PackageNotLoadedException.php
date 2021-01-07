<?php

namespace UserAgentParser\Exception;

use Exception;

/**
 * This is thrown if a composer package is not loaded.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class PackageNotLoadedException extends Exception implements ExceptionInterface
{
}
