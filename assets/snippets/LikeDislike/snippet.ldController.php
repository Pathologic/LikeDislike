<?php
include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');   
    
$_prepare = explode(",", $prepare);
$prepare = array();
$prepare[] = \APIhelpers::getkey($modx->event->params, 'BeforePrepare', '');
$prepare = array_merge($prepare,$_prepare);
$prepare[] = 'DLldController::prepare';
$prepare[] = \APIhelpers::getkey($modx->event->params, 'AfterPrepare', '');
$modx->event->params['prepare'] = trim(implode(",", $prepare), ',');

$params = array_merge(array(
    "controller"    =>  "likedislike",
    "dir"        =>  "assets/snippets/LikeDislike/DocLister/"
), $modx->event->params);
if(!class_exists("DLldController", false)){
    class DLldController{
        public static function prepare(array $data = array(), DocumentParser $modx, $_DocLister, prepare_DL_Extender $_extDocLister){
            $allowLD = $_DocLister->getCFGDef('allowLD',0);
            if ($allowLD) {
                $enabledTpl = $_DocLister->getCFGDef('enabledTpl', '');
                $disabledTpl = $_DocLister->getCFGDef('disabledTpl', '');
                $data['likedislike'] = $_DocLister->parseChunk($data['ldDisabled'] ? $disabledTpl : $enabledTpl, $data);
            }
            return $data;
        }
    }
}
return $modx->runSnippet("DocLister", $params);
?>
