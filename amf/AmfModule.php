<?php
/**
 * AmfModule
 * project:     AmfModule
 * file:        AmfModule.php
 * created:     15.10.2014 14:18
 * @author      Paweł Święcicki
 */


namespace amf;

use Yii;


class AmfModule extends \yii\base\Module
{
    public $controllerNamespace = 'amf\controllers';

    public $serviceFolders='services';
    public $serviceNames2ClassFindInfo = [];
    public $checkArgumentCount = true;
    public $disabledPlugins = [];


    public function init()
    {

        \Yii::setAlias('@amf', dirname(__DIR__));
        define( 'AMFPHP_ROOTPATH', __DIR__ . '/');
        define( 'AMFPHP_VERSION', '2.2.1');
        parent::init();

    }
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules([
                $this->id => $this->id . '/default/index',
                $this->id . '/<id:\w+>' => $this->id . '/default/view',
                $this->id . '/<controller:\w+>/<action:\w+>' => $this->id . '/<controller>/<action>',
            ], false);
        } elseif ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->id] = [
                'class' => 'yii\gii\console\GenerateController',
                'generators' => array_merge($this->coreGenerators(), $this->generators),
                'module' => $this,
            ];
        }
    }

    public function getConfig()
    {
        $config = new \amf\core\Config();
        $config->requireSignIn = !YII_DEBUG;
        $config->serviceFolders = [Yii::getAlias('@app') . DIRECTORY_SEPARATOR . $this->serviceFolders . DIRECTORY_SEPARATOR];
        //$config->serviceFolders[] = [];
        $config->checkArgumentCount = $this->checkArgumentCount;
        $config->pluginsFolders = [AMFPHP_ROOTPATH . 'plugins'];
        $config->disabledPlugins = ['AmfphpAuthentication','AmfphpLogger'];
//        $config->pluginsConfig['AmfphpVoConverter'] = array('voFolders' => array(
//            //Yii::getAlias('@app') .DIRECTORY_SEPARATOR. $this->serviceFolders . DIRECTORY_SEPARATOR . 'vo'
//
//
//        ));

        return $config;
    }



}