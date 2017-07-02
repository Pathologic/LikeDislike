<?php
define('MODX_API_MODE', true);

include_once(__DIR__."/../../../index.php");
$modx->db->connect();
if (empty ($modx->config)) {
    $modx->getSettings();
}

$modx->invokeEvent("OnWebPageInit");

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') || strpos($_SERVER['HTTP_REFERER'],$modx->config['site_url']) !== 0){
    $modx->sendErrorPage();
}

$action = isset($_POST['action']) ? $_POST['action'] : 'stat';
$rid = isset($_POST['rid']) ? (int)$_POST['rid'] : 0;
$out = $modx->runSnippet('LikeDislike',array("action"=>$action, "rid"=>$rid));

echo json_encode($out);
