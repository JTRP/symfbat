<?php

/**
 * Service permettant de vérifier si un Recaptcha Google est valide
 */
namespace App\Recaptcha;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RecaptchaValidator {

    private $params;


    public function __construct(ParameterBagInterface $params) {

        $this->params = $params;

    }


    /**
     * Méthode qui retounera "true" si le code recaptcha envoyé est un recaptcha valide; sinon false
     */
    public function verify(?string $code, ?string $ip = null) : bool
    {
        if(empty($code)) {
            return false;
        }

        $params = [
            'secret'    => $this->params->get('google_recaptcha.private_key'),
            'response'  => $code
        ];

        if($ip) {
            $params['remoteip'] = $ip;
        }

        $url = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query($params);

        if( function_exists('curl_version') ) {

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);

        } else {

            $response = file_get_contents($url);

        }

        if( empty($response) || is_null( $response ) ) {
            return false;
        }

        $json = json_decode($response);

        return $json->success;
    }

}