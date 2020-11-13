<?php

namespace BrunoMoraisTI;

use Firebase\JWT\JWT;

class JwtToken
{
    private $key;
    private $domain;

    public function __construct($key="12345",$domain="localhost")
    {
        $this->setKey($key);
        $this->setDomain($domain);
    }

    /**
     * @return mixed|string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed|string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Função para inser o domínio
     * @param string $domain
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Função para gerar ID ramdomico
     * @param int $length
     * @return false|string
     */
    private function getId($length = 20)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Função para gerar ID ramdomico
     * @param array $json
     * @param int $addHora
     * @return false|string
     */
    public function encode($json = null, $qtdHour=1)
    {

        $payload = array(
            "iss" => "{$this->getDomain()}", //O domínio da aplicação geradora do token
            "sub" => $this->getId(), //É o assunto do token, mas é muito utilizado para guarda o ID do usuário
            "jti" => $this->getId(), //O id do token
            "aud" => 0, //Define quem pode usar o token
            "iat" => $this->converteDataEmTimestamp($this->pegarDataAtualBanco()), // Data de criação do Token
            "nbf" => $this->converteDataEmTimestamp($this->pegarDataAtualBanco()), // Data que o token não pode ser aceito antes dela
            "exp" => $this->converteDataEmTimestamp($this->somaHoraDataBanco($this->pegarDataAtualBanco(), $addHora)), // Data e Horario que o token expira
            'data' => [                  // Data related to the signer user
                'json' => $json,// userid from the users table
            ]
        );

        $jwt = JWT::encode($payload, $this->getKey());

        return $jwt;
    }

    public function decode($token)
    {

        try {
            JWT::$leeway = 60; // $leeway in seconds
            $decoded = JWT::decode($token, $this->getKey(), array('HS256'));
            if (!empty($decoded->exp)) {
                $dataToken = $this->converteTimestampEmData($decoded->exp);
                if (strtotime($dataToken) > strtotime($this->pegarDataAtualBanco())) {
                    return $decoded;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $ex) {
            //echo "Erro: ".$ex;
            return false;
        }

    }

    public function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    private function converteDataEmTimestamp($date)
    {
        return strtotime($date);
    }

    private function converteTimestampEmData($timestamp)
    {
        return date('Y-m-d H:i', $timestamp);
    }

    /**
     * FUNÇÃO PEGAR DATA ATUAL NO FORMATO DO BANCO DE DADOS
     *
     * @return false|stringgit
     */
    private function pegarDataAtualBanco()
    {
        date_default_timezone_set("America/Araguaina");
        return date("Y-m-d H:i:s");
    }

    /**
     * FUNÇÃO PARA SOMAR UM DIA A DATA
     *
     * @param $data
     * @return false|string
     */
    private function somaHoraDataBanco($data, $addHora)
    {
        $ano = substr($data, 0, 4);
        $mes = substr($data, 5, 2);
        $dia = substr($data, 8, 2);
        $hora = substr($data, 11, 2);
        $minutos = substr($data, 14, 2);
        return date("Y-m-d H:m", mktime($hora + $addHora, $minutos, 0, $mes, $dia, $ano));
    }
}