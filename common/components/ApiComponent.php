<?php
namespace common\components;

use common\services\Curl;
use yii\base\Component;

class ApiComponent extends Component
{
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_NOT_FOUND = 404;
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
    const username = 'apm3';
    const password = '123456zxc';
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
        return json_decode(\Yii::$container->get(Curl::class)->call(
            $method,
            $url,
            $data,
            $this->token()->token,
            true
        ), true);
    }

    public function response($response, $status = true)
    {
        if($status){
            $result = [
                'success' => $response,
                'status' => self::HTTP_OK
            ];
        } else {
            $result = [
                'error' => $response,
                'status' => self::HTTP_NOT_FOUND
            ];
        }

        return $result;
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

        if($response == 'Order not found'){
            $response = $this->response($response, false);
        } else {
            if(isset($response["Message"]) && $response["Message"] == 'The request is invalid.'){
                $response = $this->response($response, false);
            } else {
                $response = $this->getStatus($response);
                $response = $this->response($response);
            }
        }

        return $response;
    }
}