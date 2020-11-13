<?php
require __DIR__ . '/vendor/autoload.php';

use BrunoMoraisTI\JwtToken;



$jwtToken = new JwtToken("12345","localhost");

$objJson = array(
    "id" => 1,
    "name" => "Test"
);

$qtdHoras = 3;

echo $jwtToken->encode($objJson,$qtdHoras);