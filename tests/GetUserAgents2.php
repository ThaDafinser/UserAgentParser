<?php
chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$file = 'data/vwp-online.de_ua.html';

$doc = new DOMDocument();
$doc->loadHTMLFile($file);
$elements = $doc->getElementsByTagName('tr');

$userAgents = [];
foreach ($elements as $el) {
    /* @var $el \DOMElement */
    
    /* @var $childs \DOMNodeList */
    $childs = $el->childNodes;
    
    $userAgents[] = trim($childs->item(1)->nodeValue);
}

/*
 * To php file
 */
$phpString = '<?php' . "\n";
$phpString .= 'return [' . "\n";
foreach ($userAgents as $ua) {
    $phpString .= '    \'' . addslashes($ua) . '\',' . "\n";
}
$phpString .= '];' . "\n";

file_put_contents('data/userAgents2.php', $phpString);
