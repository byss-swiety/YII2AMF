<?php
/**
 * AmfModule assets
 * project:     AmfModule
 * file:        AmfAsset.php
 * created:     15.10.2014 14:18
 * @author      Paweł Święcicki
 */

namespace amf;

use yii\web\AssetBundle;

/**
 * @author Paweł Święcicki <swiety@byss.pl>
 * @since 2.0
 */
class AmfAsset extends AssetBundle
{
    /**
     * @var string
     */
//    public $sourcePath = '@amf/assets';

    /**
     * @var array
     */
    public $css = [
        'css/jquery-ui.css',
        'css/style.css',
    ];
    /**
     * @var array
     */
    public $js = [
        'js/jquery-ui.js',
        'js/jquery.hotkeys.js',
        'js/jquery.jstree.js',
        'js/dataparse.js',
        'js/ace/ace.js',
        'js/amfphp_updates.js',
        'js/swfobject.js',
        'js/jquery.cookie.js',
        'js/services.js',
        'js/sb.js',

    ];
    /**
     * @var array
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets';
    }
}

