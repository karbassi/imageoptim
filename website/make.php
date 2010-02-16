#!/bin/env php
<?php

$dest = "public_html";

require_once "PHPTAL.php";
require_once "PHPTAL/Prefilter.php";
require_once "PHPTAL/Prefilter/Normalize.php";


class UnSpace extends PHPTAL_PreFilter {
    function filter($src)
    {
        return preg_replace(array('/[\t ]+/','/ ($|<tal:)/'),array(' ','$1'),$src);
    }
}

function data_encode($file)
{
    $b64 = "base64,".base64_encode($file);
    $urlenc = strtolower(rawurlencode($file));
    
    if (strlen(gzencode($b64)) < strlen(gzencode($urlenc))) return $b64; else return $urlenc; 
}

try
{
    foreach(glob("*.{png,jpg,ico}",GLOB_BRACE | GLOB_NOSORT) as $file)
    {
        copy($file,"$dest/$file");
    }
    
    $styles = array();
    foreach(glob("*.css") as $file)
    {
        $tmpnam = tempnam(sys_get_temp_dir(),"imageoptim");
        
        $data = preg_replace_callback('/\/\*\s*inline\s*\*\/\s*url\(\s*["\']?([^)\'"]*)[\'"]?\s*\)/', function($m){
            if (file_exists($m[1])) 
                return "url(data:image/png;".data_encode(file_get_contents($m[1])).")";
            else return "url(".$m[1].")";
        }, file_get_contents($file));
        
        file_put_contents($tmpnam,$data);
        
        exec("java -jar ./yuicompressor/build/yuicompressor-2.4.2.jar --charset UTF-8 --type css -v -o ".escapeshellarg("$dest/$file")." ".escapeshellarg($tmpnam));
        unlink($tmpnam);
        $styles[] = "$dest/$file";
    }

    $file = '';

    $tpl = new PHPTAL();
    $tpl->setOutputMode(PHPTAL::HTML5);
    $tpl->latest = "1.2.4";
    $tpl->beta = NULL;//"1.2.4";
    $tpl->styles = $styles;
    $tpl->addPreFilter(new UnSpace);
    $tpl->addPreFilter(new PHPTAL_PreFilter_Normalize);

    foreach(glob("*.html") as $file)
    {
        $tpl = new PHPTAL();
        $tpl->setOutputMode(PHPTAL::HTML5);
        $tpl->latest = "1.2.4";
        $tpl->beta = NULL;//"1.2.4";
        $tpl->styles = $styles;
        $tpl->addPreFilter(new UnSpace);
        $tpl->addPreFilter(new PHPTAL_PreFilter_Normalize);
        
        $tpl->setTemplate($file);
        $tpl->file = basename($file,".html");
        file_put_contents("$dest/$file",$tpl->execute());
    }
}
catch(Exception $e)
{
    fwrite(STDERR,"$file: ".$e->getMessage()."\n");
    exit(1);
}
