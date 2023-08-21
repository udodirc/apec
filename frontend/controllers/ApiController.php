<?php

namespace frontend\controllers;

use common\services\Curl;
use yii\web\Controller;

class ApiController extends Controller
{
    const URL = 'https://api.apec-uae.com/api/';

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetOrderById()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $url = self::URL.'status/'.\Yii::$app->request->queryParams['id'];

        return  \Yii::$app->apiComponent->getOrder($url, 'GET', true);
    }

    public function actionGetOrderByCustomerNum()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $url = self::URL.'statusbycustordernum/'.\Yii::$app->request->queryParams['orderNumber'];

        return  \Yii::$app->apiComponent->getOrder($url, 'GET', true);
    }

    public function actionGetOrders()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $url = self::URL.'getorders/'
            .\Yii::$app->request->queryParams['isActive']
            .'/'.\Yii::$app->request->queryParams['limit'];

        return  \Yii::$app->apiComponent->getOrder($url, 'GET', true);
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
        $url = self::URL.'order';

        $response = \Yii::$app->apiComponent->call('POST', $url, $params);

        return [
            'response' => $response,
        ];
    }
}