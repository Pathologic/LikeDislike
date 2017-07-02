<?php
include_once(MODX_BASE_PATH . 'assets/snippets/LikeDislike/model/ld.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
$action = isset($action) ? $action : 'stat';
$rid = isset($rid) ? $rid : (isset($modx->documentIdentifier) && $modx->documentIdentifier ? $modx->documentIdentifier : 0);
$classKey = isset($classKey) ? $classKey : 'modResource';
$enabledTpl = isset($enabledTpl) ? $enabledTpl : '';
$disabledTpl = isset($disabledTpl) ? $disabledTpl : '';
$onlyUsers = isset($onlyUsers) ? (bool)$onlyUsers : false;
$ld = new \LikeDislike\Model($modx);
$uid = $modx->getLoginUserID('web');
if (($uid && $onlyUsers) || !$onlyUsers) {
    switch ($action) {
        case 'like':
            $ld->like($rid, $classKey);
            break;
        case 'dislike':
            $ld->dislike($rid, $classKey);
            break;
        default:
            break;
    }
}
$stat = $ld->stat($rid, $classKey);
$modx->setPlaceholder('like',$stat['like']);
$modx->setPlaceholder('dislike',$stat['dislike']);
$tpl = ($ld->isLogged($rid, $classKey) || ($onlyUsers && !$uid)) ? $disabledTpl : $enabledTpl;
if ($tpl) {
    return DLTemplate::getInstance($modx)->parseChunk($tpl,array(
        "rid" => $rid,
        "like" => $stat['like'],
        "dislike" => $stat['dislike']
    ));
} else {
    return $stat;
}
