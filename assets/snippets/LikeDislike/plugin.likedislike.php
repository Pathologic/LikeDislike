<?php
if (IN_MANAGER_MODE != 'true') die();
if ($e->name == 'OnEmptyTrash') {
    if (empty($ids)) return;
    $where = implode(',', $ids);
    $ld_table = $modx->getFullTableName('likedislike');
    $log_table = $modx->getFullTableName('likedislike_log');
    $modx->db->delete($ld_table, "`rid` IN ($where) AND `classKey`='modResource'");
    $modx->db->delete($log_table, "`rid` IN ($where) AND `classKey`='modResource'");
    $modx->db->query("ALTER TABLE {$ld_table} AUTO_INCREMENT = 1");
    $modx->db->query("ALTER TABLE {$log_table} AUTO_INCREMENT = 1");
}
