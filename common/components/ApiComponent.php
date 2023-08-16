<?php
namespace common\components;

use common\services\Curl;
use yii\base\Component;

class ApiComponent extends Component
{
    const username = 'test';
    const password = 'test';
    const grantType = 'test';
    const url = 'https://api.apec-uae.com/token';
    const method = 'POST';

    public $token = '';
    public $tokenType = '';
    public $expires = '';

    public function token()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params =
        [
           'username' => self::username,
           'password' => self::password,
           'grant_type' => self::grantType,
        ];
        $response = json_decode(\Yii::$container->get(Curl::class)->call(
            self::method,
            self::url,
            $params
        ), true);

        $this->token = (!empty($response['access_token'])) ? $response['access_token'] : '';
        $this->tokenType = (!empty($response['token_type'])) ? $response['token_type'] : '';
        $this->expires = (!empty($response['expires_in'])) ? $response['expires_in'] : '';

        return $this;
    }
}