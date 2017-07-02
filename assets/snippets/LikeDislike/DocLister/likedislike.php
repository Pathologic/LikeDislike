<?php
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/core/controller/site_content.php');

/**
 * Class likedislikeDocLister
 */
class likedislikeDocLister extends site_contentDocLister
{
    /**
     * @absctract
     */
    public function getDocs($tvlist = '')
    {
        $docs = parent::getDocs($tvlist);

        $ids = array_keys($docs);
        $classKey = $this->getCFGDef('classKey', 'modResource');
        $onlyUsers = $this->getCFGDef('onlyUsers', 0);
        $uid = $this->modx->getLoginUserID('web');
        $allowLD = $this->getCFGDef('allowLD',0);
        foreach ($ids as $key => $id) {
            $docs[$id]['like'] = (int)$docs[$id]['like'];
            $docs[$id]['dislike'] = (int)$docs[$id]['dislike'];
            $docs[$id]['rid'] = $id;
            $docs[$id]['classKey'] = $classKey;
            if ($allowLD) {
                $cookieName = md5($classKey . $id);
                $docs[$id]['ldDisabled'] = 0;
                if ($onlyUsers && !$uid) {
                    $docs[$id]['ldDisabled'] = 1;
                } else {
                    if (isset($_COOKIE[$cookieName])) {
                        if ($docs[$id]['like'] == 0 && $docs[$id]['dislike'] == 0) {
                            unset($_COOKIE[$cookieName]);
                            setcookie($cookieName, null, -1, '/');
                        } else {
                            $docs[$id]['ldDisabled'] = 1;
                            unset($ids[$key]);
                        }
                    }
                }
            } else {
                $docs[$id]['ldDisabled'] = 1;
            }
        }
        if ($allowLD && $ids) {
            $ids = $this->sanitarIn($ids);
            $ip = \APIhelpers::getUserIP();

            $where = array();
            $where[] = "`rid` IN ({$ids})";
            $where[] = "`classKey`='{$classKey}'";
            $where[] = $uid ? "`uid`={$uid}" : "`ip`='{$ip}'";
            $where = implode(' AND ', $where);
            $q = $this->dbQuery("SELECT `rid` FROM {$this->modx->getFullTableName('likedislike_log')} WHERE {$where}");
            $ids = $this->modx->db->getColumn('rid', $q);
            foreach ($ids as $id) {
                $docs[$id]['ldDisabled'] = 1;
            }
        }
        $this->_docs = $docs;

        return $docs;
    }

    /**
     * Генерация имени таблицы с префиксом и алиасом
     *
     * @param string $name имя таблицы
     * @param string $alias желаемый алиас таблицы
     * @return string имя таблицы с префиксом и алиасом
     */
    public function getTable($name, $alias = '')
    {
        $table = parent::getTable($name, $alias);
        $ld_table = $this->modx->getFullTableName('likedislike');
        $classKey = $this->getCFGDef('classKey','modResource');
        $table .= " LEFT JOIN {$ld_table} `ld` ON `ld`.`rid`=`c`.`id` AND `ld`.`classKey` = '{$classKey}'";

        return $table;
    }
}
