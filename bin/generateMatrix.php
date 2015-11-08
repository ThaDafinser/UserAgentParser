<?php
include_once 'bootstrap.php';

/*
 * per cli parameter
 */
if (! isset($filename)) {
    if (! isset($argv[1])) {
        throw new \Exception('parameter missing, usage: php generateMatrix.php myFile.php');
    }
    
    $filename = $argv[1];
    if (! file_exists($filename)) {
        throw new \Exception('file does not exists: ' . $filename);
    }
}

if (! isset($userAgents)) {
    $userAgents = include $filename;
    if (! is_array($userAgents)) {
        throw new \Exception('the data file does not return an array!');
    }
}

if (isset($useFilename)) {
    $reportName = $useFilename;
} else {
    $reportName = basename($filename);
}
$reportName = str_replace(['.json', '.php'], '.html', $reportName);

/*
 * include different datasets!
 */

/*
 * do always the same - generate a matrix
 */
use UserAgentParser\Provider;
use UserAgentParser\GetMatrix;

/*
 * Providers
 */
$browscapLite = new Provider\BrowscapPhp();
$browscapFull = new Provider\BrowscapPhp();
$browscapFull->setCachePath('.tmp/browscap_full');

$chain = new Provider\Chain([
    $browscapFull,
    new Provider\DonatjUAParser(),
    new Provider\PiwikDeviceDetector(),
    new Provider\UAParser(),
    new Provider\WhichBrowser(),
    new Provider\Woothee(),
    new Provider\YzalisUAParser()
]);
$chain->setExecuteAll(true);

$matrix = new GetMatrix();
$matrix->setUserAgents($userAgents);
$matrix->setProvider($chain);
$table = $matrix->toHtml();

$html = <<<END
<html>
<head>
<link
	href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" />
	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</head>

<body>
    $table
</body>
</html>
END;

file_put_contents('data/results/' . $reportName, $html);
