<?php
/**
 * site_content controller
 * @see http://modx.im/blog/addons/374.html
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>, kabachello <kabachnik@hotmail.com>
 */
include_once (MODX_BASE_PATH . 'assets/snippets/DocLister/core/controller/site_content.php');
class likedislike_moduleDocLister extends site_contentDocLister
{
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
        $table = "{$ld_table} `ld` LEFT JOIN {$table} ON `ld`.`rid`=`c`.`id` AND `ld`.`classKey` = '{$classKey}'";

        return $table;
    }
}
