<?php
require __DIR__ . '/vendor/autoload.php';

use BMorais\JwtToken;

$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOm51bGwsInN1YiI6ImEzNzBkNzk5ZWUxYmVmODNjYTE1NDdhYmFjMWRlZDljMGU4MTdkMzQiLCJqdGkiOiJjOTRlZTkzZWQzYmU0OTQ2MjZjNTcyMDZkM2RjMGY1MTJjZWQyMzY3IiwiaWF0IjoxNjA1Mjc5ODYwLCJleHAiOjE2MDUyODM4NjAsImRhdGEiOnsianNvbiI6bnVsbH19.fcaTEocUnMSo07zeA_D3U0FgiaPEfzoFCI3tB5E-jjw";

$jwtToken = new JwtToken();

var_dump($jwtToken->decode($token));