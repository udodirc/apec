<?php
namespace common\components;

use common\services\Curl;
use yii\base\Component;

class ApiComponent extends Component
{
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_OK = 200;
    public const ORDER_STATUS = [
        1 => 'новый',
        2 => 'в работе',
        3 => 'завершён',
    ];
    public const CURRENT_STATUS = [
        10 => 'Новый заказ',
        40 => 'Заказ принят',
        50 => 'Отправлен поставщику',
        70 => 'Изменение количества',
        80 => 'Задержка',
        90 => 'Переход номера',
        100 => 'Изменение цены',
        120 => 'Поступил на склад',
        140 => 'Готово к выдаче',
        160 => 'Получен клиентом',
        180 => 'Поставка невозможна',
        190 => 'Выдача невозможна',
        200 => 'Отказ клиент',
        240 => 'Новый заказ',
    ];
    const URL = 'https://api.apec-uae.com/api/';
    const username = '';
    const password = '';
    const grantType = 'password';
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

    public function call($method, $url, $data = false)
    {
        $response = \Yii::$container->get(Curl::class)->call(
            $method,
            $url,
            $data,
            $this->token()->token,
            true
        );

        if(!$response){
            return [
                'error' => 'Internal server error',
                'status' => self::HTTP_INTERNAL_SERVER_ERROR
            ];
        } else {
            $response = json_decode($response, true);
            $result = $this->response($response);
        }

        return $result;
    }

    public function response($response)
    {
        if($response == 'Order not found'){
            $response = [
                'error' => 'Order not found',
                'status' => self::HTTP_NOT_FOUND
            ];
        } else {
            if(isset($response["Message"]) && $response["Message"] == 'The request is invalid.'){
                $response = [
                    'error' => 'The request is invalid.',
                    'status' => self::HTTP_BAD_REQUEST
                ];
            } elseif (isset($response["Message"]) && $response["Message"] == 'Authorization has been denied for this request.') {
                $response = [
                    'error' => 'Authorization has been denied for this request.',
                    'status' => self::HTTP_BAD_REQUEST
                ];
            }
        }

        return $response;
    }

    public function getStatus($response)
    {
        if(isset($response['OrderID'])) {
            if (isset($response['Status'])) {
                $response['Status'] = self::ORDER_STATUS[$response['Status']] ?? $response['Status'];
            }
        } else {
            foreach ($response as $i => $data){
                $response[$i]['Status'] = self::ORDER_STATUS[$data['Status']] ?? $data['Status'];
                $response[$i]['OrderLines'][0]['CurrentStatus'] = self::CURRENT_STATUS[$data['OrderLines'][0]['CurrentStatus']] ?? $data['OrderLines'][0]['CurrentStatus'];
            }
        }

        if (isset($response['OrderLines'][0]['CurrentStatus'])){
            $response['OrderLines'][0]['CurrentStatus'] = self::CURRENT_STATUS[$response['OrderLines'][0]['CurrentStatus']] ?? $response['OrderLines'][0]['CurrentStatus'];
        }

        return $response;
    }

    public function getOrder($url, $method)
    {
        $response = $this->call($method, $url);

        if(!isset($response['error'])){
            $result['success'] = $this->getStatus($response);
            $result['status'] = self::HTTP_OK;
        } else {
            $result = $response;
        }

        return $result;
    }
}