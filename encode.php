<?php
require __DIR__ . '/vendor/autoload.php';

use BMorais\JwtToken;



$jwtToken = new JwtToken();

echo $jwtToken->encode();