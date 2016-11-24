<?php

/**
 * @author Dan Cardin<dcardin2007@gmail.com>
 * 
 */

if(!scandir("packages")) {
    die('Run program from root project directory');
}

/**
 * @return boolean
 */
$isXmlFileFunc = function($filename){ return substr($filename, -3) === 'xml'; };

/**
 * @return stdClass
 */
$getPackageFunc = function($filePath, $baseUrl = 'https://raw.githubusercontent.com/yooper/pta_data/gh-pages/packages/')
{
    $defaults = [
        'id' => '',
        'name' => '',
        'languages' => '',
        'unzip' => "1",
        'webpage' => '',
        'url' => '',
        'license' => '',
        'author' => '',
        'copyright' => '',
        'url' => '',
        'checksum' => '',
        'subdir' => ''
    ];
    
    $xmlData = current(simplexml_load_file($filePath)->attributes());
    $xmlData['subdir'] = end(explode("/", dirname($filePath)));
    $zipFileName = substr($filePath, 0, -3)."zip";
    if($xmlData['unzip'] == 1 && file_exists($zipFileName)) {
        $xmlData['checksum'] = md5_file($zipFileName);        
    } 
    
    if(!isset($xmlData['url'])) {
        $xmlData['url'] = $baseUrl .= $xmlData['subdir'] . '/' . basename($zipFileName);
    }
    
    return $xmlData + $defaults;
};

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('packages/'));
$packages = [];

foreach ($rii as $file) 
{
    if ($file->isDir()){ 
        continue;
    }
    if($isXmlFileFunc($file->getRealPath())) {
        $packages[] = $getPackageFunc($file->getRealPath());
    }
}

$xml = new SimpleXMLElement('<pta_data/>');
$packagesXml = $xml->addChild('packages');

foreach($packages as $package)
{
    $childPackagesXml = $packagesXml->addChild('package');
    foreach($package as $key => $value)
    {
        $childPackagesXml->addAttribute($key, $value);
    }
}

$dom = new DOMDocument("1.0");
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml->asXML());

//saves the index xml file
file_put_contents('index.xml', $dom->saveXML());