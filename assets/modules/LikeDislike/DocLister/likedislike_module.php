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
    public function __construct ($modx, $cfg = array(), $startTime = null)
    {
        parent::__construct($modx, $cfg, $startTime);
        $classKey = $this->getCFGDef('classKey', 'modResource');
        $this->setFiltersJoin(" LEFT JOIN {$this->getTable('likedislike', 'ld')} ON `ld`.`rid`=`c`.`id` AND `ld`.`classKey` = '{$classKey}'");
    }
}
