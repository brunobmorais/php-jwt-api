<?php

namespace BMorais;

use Firebase\JWT\JWT;

class JwtToken
{
    private $key;
    private $domain;

    public function __construct($key="123456789", $domain="localhost")
    {
        $this->key = $key;
        $this->domain;

    }

    /**
     * FUNÇÃO PARA GERAR UM TOKEN
     *
     * @return false|string
     */
    private function gerarToken($length = 20)
    {
        return bin2hex(random_bytes($length));
    }

    public function encode($json = null, $addHora=1)
    {

        $payload = array(
            "iss" => $this->domain, //O domínio da aplicação geradora do token
            "sub" => $this->gerarToken(), //É o assunto do token, mas é muito utilizado para guarda o ID do usuário
            "jti" => $this->gerarToken(), //O id do token
            "iat" => $this->converteDataEmTimestamp($this->pegarDataAtualBanco()),
            "exp" => $this->converteDataEmTimestamp($this->somaHoraDataBanco($this->pegarDataAtualBanco(), $addHora)),
            'data' => [                  // Data related to the signer user
                'json' => $json,// userid from the users table
            ]
        );

        $jwt = JWT::encode($payload, $this->key);

        return $jwt;
    }

    public function decode($token)
    {

        try {
            JWT::$leeway = 60; // $leeway in seconds
            $decoded = JWT::decode($token, $this->key, array('HS256'));
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

        // print_r($decoded->data->ID);
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