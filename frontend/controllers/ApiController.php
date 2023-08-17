<?php

namespace frontend\controllers;

use common\services\Curl;
use yii\web\Controller;

class ApiController extends Controller
{
    const url = 'https://api.apec-uae.com/api/';

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetOrderById()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = \Yii::$app->request->queryParams['id'];
        $url = self::url.'status/'.$id;
        $response = \Yii::$app->apiComponent->call('GET', $url);

        return [
            'response' => $response,
        ];
    }

    public function actionGetOrderByCustomerNum()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $number = \Yii::$app->request->queryParams['orderNumber'];
        $url = self::url.'statusbycustordernum/'.$number;
        $response = \Yii::$app->apiComponent->call('GET', $url);

        return [
            'response' => $response,
        ];
    }

    public function actionGetOrders()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $isActive = \Yii::$app->request->queryParams['isActive'];
        $limit = \Yii::$app->request->queryParams['limit'];
        $url = self::url.'getorders/'.$isActive.'/'.$limit;

        $response = \Yii::$app->apiComponent->call('GET', $url);

        return [
            'response' => $response,
        ];
    }

    public function actionCreateOrder()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params =
         [
           'CustOrderNum' => "!!!ТЕСТОВЫЙ API!!!",
           'OrderNotes' => "!!!ТЕСТОВЫЙ API!!!",
           'ValidationType' => 1,
           'IsTest' => true,
           'OrderHeadLines' => [[
               "Count" => 1,
               "Price" => 6.5,
               "Reference" => "!!!ТЕСТОВЫЙ API DP: 1!!!",
               "ReactionByCount" => 0,
               "ReactionByPrice" => 0,
               "StrictlyThisNumber" => true,
               "Brand" => "DENSO",
               "PartNumber" => "IK16",
               "SupplierID" => 9553
           ]],
           'DeliveryPointID' => 0,
         ];
        $url = self::url.'order';

        $response = \Yii::$app->apiComponent->call('POST', $url, $params);

        return [
            'response' => $response,
        ];
    }
}