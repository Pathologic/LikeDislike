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
$modx->setPlaceholder($classKey.'.like.'.$rid,$stat['like']);
$modx->setPlaceholder($classKey.'.dislike.'.$rid,$stat['dislike']);
$modx->setPlaceholder($classKey.'.ld_rating.'.$rid,$stat['ld_rating']);
$disabled = $ld->isLogged($rid, $classKey) || ($onlyUsers && !$uid);
$modx->setPlaceholder($classKey.'.disabled.'.$rid,(int)$disabled);
$tpl = $disabled ? $disabledTpl : $enabledTpl;
if ($tpl) {
    return DLTemplate::getInstance($modx)->parseChunk($tpl,array(
        "rid"       => $rid,
        "like"      => $stat['like'],
        "dislike"   => $stat['dislike'],
        "ld_rating" => $stat['ld_rating'],
        "disabled"  => $disabled
    ));
} else {
    return $stat;
}
