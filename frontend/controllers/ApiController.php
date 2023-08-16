<?php

namespace frontend\controllers;

use common\services\Curl;
use yii\web\Controller;

class ApiController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetToken()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $token = \Yii::$app->apiComponent->token()->token;

        return [
            'token' => $token,
        ];
    }
}