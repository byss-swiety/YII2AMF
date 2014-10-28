YII2AMF
=======

The AMF module for Yii2

Usage:
1) Install this module in vendors folder using composer.
2) Add module in yii2 config:
 'modules' => [
        'amf' => [
            'class' => 'amf\AmfModule',
            'serviceFolders' => 'services',
        ],
    ],
3) Create folder "services"
4) Write a service.
5) Done
