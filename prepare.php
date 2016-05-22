<?php

require 'vendor/autoload.php';

$io = new Xolf\io\Client(__DIR__ . '/io');

function getUrls($string)
{
    $regex = '/https?\:\/\/[^\" ]+/i';
    preg_match_all($regex, $string, $matches);
    //return (array_reverse($matches[0]));
    return ($matches[0]);
}
