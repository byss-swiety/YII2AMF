<?php
/**
 *  browser view
 * @var $this \yii\web\View
 */

use yii\helpers\Html;

amf\AmfAsset::register($this);

$isAccessGranted = true;
$config = amf\AmfModule::getInstance()->getConfig();
?>

<?php $this->head() ?>
<script type="text/javascript">

    var amfcaller = "<?=(Yii::getAlias('@web'). '/flash/AmfCaller.swf');?>";
    var amfphpVersion = "<?=AMFPHP_VERSION;?>";
    var amfphpEntryPointUrl = "<?=Yii::getAlias('@web') . '/amf/default/gateway';?>";

</script>

<div class="page-wrap">


    <div id="main">
        <div id="statusMessage" class="warning"></div>
        <table id="twoColumnLayout">
            <tr>
                <td class="left">

                    <div id="services">
                        <h2>Serwisy</h2>
                        <ul id='serviceMethods'>
                            <p>Ładuję</p>
                             </ul>
                    </div>
                </td>
                <td id="methodCaller" class="right">

                    <div id="methodDescription" class="chosen">
                        <h4 id="serviceHeader"></h4>
                        <span id="methodComment"></span>
                    </div>

                    <div id="methodParameters">

                        <table id="paramDialogs">
                            <tbody></tbody>
                        </table>

                        <span id="noParamsIndicator">Ta metoda nie posiada parametrów.</span>
                    </div>

                    <div id="methodCall">
                        <input type="submit" value="Call" onclick="makeJsonCall()"/>
                        <input type="submit" value="Call JSON" onclick="makeJsonCall()"/>
                        <input type="submit" value="Call AMF" onclick="makeAmfCall()"/>

                    </div>
                    <div id="amfCallerContainer"> Flash Player is needed to make AMF calls.>
                        <a target="_blank" href="http://www.adobe.com/go/getflashplayer">
                            <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif"
                                 alt="Get Adobe Flash player"/>
                        </a>
                    </div>
<!--     </div> -->

    <div id="methodCallResult">

        <div class="showResultView">
            <a id="tree" class="tab">Tree</a>
            <a id="print_r"  class="tab">print_r</a>
            <a id="json"  class="tab">JSON</a>
            <a id="php"  class="tab">PHP Serialized</a>
            <a id="raw"  class="tab">Raw</a>
        </div>
        <div id="dataView">
            <div id="tree" class="resultView"></div>
            <div id="print_r" class="resultView"></div>
            <div id="json" class="resultView"></div>
            <div id="php" class="resultView"></div>
            <div id="raw" class="resultView"></div>
        </div>
    </div>

    </td>
                </tr>
            </table>
        </div>
    </div>

