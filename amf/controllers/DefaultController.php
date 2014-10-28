<?php
/**
 * Short desc.
 * project:     byss template
 * file:        DefaultController.php
 * created:     10/14/14 4:03 PM
 * @author      Paweł Święcicki
 */


namespace amf\controllers;

use Yii;
use amf\AmfModule;
use yii\web\Controller;
use amf\core\HttpRequestGatewayFactory;

class DefaultController extends Controller
{


    public $enableCsrfValidation = false;

    public function actionBrowser()
    {
        Yii::$app->assetManager->forceCopy = true;

        return $this->render('browser');
    }
    public function actionGateway()
    {
         $this->layout = false;
        $config = \amf\AmfModule::getInstance()->getConfig();

//        print_r($config);  exit;

        $gateway = \amf\core\HttpRequestGatewayFactory::createGateway($config);
        $gateway->service();
        $gateway->output();
    }
} 