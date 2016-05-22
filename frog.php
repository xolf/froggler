#!/usr/bin/env php
<?php

require 'prepare.php';

$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,4}(\/\S*)?/";

if(isset($argv[1]))
{
    if(preg_match($reg_exUrl, $argv[1], $url))
    {
        $io->table('to-crawl')->document($url[0])->write(['url' => $url[0], 'submitted' => date('c')]);
    }
    else
    {
        die($argv[1] . ' is not a valid url');
    }
}

foreach ($io->table('to-crawl')->info()->document as $document)
{
    $doc = $io->table('to-crawl')->document($document->name);
    $ch= curl_init();
    try{
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $doc->url,
            CURLOPT_USERAGENT => 'xolf Froggler Bot - v0.1',
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false
        ));
        $resp = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($resp)
        {
            echo $doc->url . ' => ';
            echo $status . ' :';
            $urls = getUrls($resp);
            $i = 0;
            if($urls)
            {
                foreach ($urls as $uri)
                {
                    $io->table('to-crawl')->document($uri)->write(['url' => $uri, 'submitted' => date('c')]);
                    $i++;
                }
            }
            echo ' Found ' . $i . ' urls';
            $doc->write(['crawled' => date('c'), 'status' => $status, 'urls' => $i]);
            $doc->moveTo($io->table('crawled'));
            echo PHP_EOL;
        }
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        echo 'Ouh: ' . $e->getMessage();
        $doc->flush();
    }
}
