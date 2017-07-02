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
     * @abstract
     */
    public function getChildrenCount()
    {
        $out = 0;
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $q_true = $this->getCFGDef('ignoreEmpty', '0');
            $q_true = $q_true ? $q_true : $this->getCFGDef('idType', 'parents') == 'parents';
            $where = $this->getCFGDef('addWhereList', '');
            $where = sqlHelper::trimLogicalOp($where);
            $where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
            if ($where != '' && $this->_filters['where'] != '') {
                $where .= " AND ";
            }
            $where = sqlHelper::trimLogicalOp($where);

            $where = "WHERE {$where}";
            $whereArr = array();
            if (!$this->getCFGDef('showNoPublish', 0)) {
                $whereArr[] = "c.deleted=0 AND c.published=1";
            }
            else{
                $q_true = 1;
            }

            $tbl_site_content = $this->getTable('site_content', 'c');

            if ($sanitarInIDs != "''") {
                switch ($this->getCFGDef('idType', 'parents')) {
                    case 'parents':
                        switch ($this->getCFGDef('showParent', '0')) {
                            case '-1':
                                $tmpWhere = "c.parent IN (" . $sanitarInIDs . ")";
                                break;
                            case 0:
                                $tmpWhere = "c.parent IN ({$sanitarInIDs}) AND c.id NOT IN({$sanitarInIDs})";
                                break;
                            case 1:
                            default:
                                $tmpWhere = "(c.parent IN ({$sanitarInIDs}) OR c.id IN({$sanitarInIDs}))";
                                break;
                        }
                        if (($addDocs = $this->getCFGDef('documents', '')) != '') {
                            $addDocs = $this->sanitarIn($this->cleanIDs($addDocs));
                            $whereArr[] = "((" . $tmpWhere . ") OR c.id IN({$addDocs}))";
                        } else {
                            $whereArr[] = $tmpWhere;
                        }

                        break;
                    case 'documents':
                        $whereArr[] = "c.id IN({$sanitarInIDs})";
                        break;
                }
            }
            $ld_table = $this->modx->getFullTableName('likedislike');
            $from = "{$ld_table} `ld` LEFT JOIN {$tbl_site_content} ON `ld`.`rid`=`c`.`id` {$this->_filters['join']}";
            $where = sqlHelper::trimLogicalOp($where);

            $q_true = $q_true ? $q_true : trim($where) != 'WHERE';

            if (trim($where) != 'WHERE') {
                $where .= " AND ";
            }

            $whereArr[] = "`ld`.`classKey`='{$this->getCFGDef('classKey','modResource')}'";

            $where .= implode(" AND ", $whereArr);
            $where = sqlHelper::trimLogicalOp($where);

            if (trim($where) == 'WHERE') {
                $where = '';
            }
            $group = $this->getGroupSQL($this->getCFGDef('groupBy', 'c.id'));
            $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
            list($from) = $this->injectSortByTV($from, $sort);

            $q_true = $q_true ? $q_true : $group != 'GROUP BY c.id';

            if ( $q_true ){
                $rs = $this->dbQuery("SELECT count(*) FROM (SELECT count(*) FROM {$from} {$where} {$group}) as `tmp`");
                $out = $this->modx->db->getValue($rs);
            }
            else {
                $out = count($this->IDs);
            }
        }

        return $out;
    }

    /**
     * @return array
     */
    protected function getDocList()
    {
        $out = array();
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $where = $this->getCFGDef('addWhereList', '');
            $where = sqlHelper::trimLogicalOp($where);

            $where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
            $where = sqlHelper::trimLogicalOp($where);

            $tbl_site_content = $this->getTable('site_content', 'c');
            if ($sanitarInIDs != "''") {
                $where .= ($where ? " AND " : "") . "c.id IN ({$sanitarInIDs}) AND `ld`.`classKey`='{$this->getCFGDef('classKey','modResource')}' AND ";
            }
            $where = sqlHelper::trimLogicalOp($where);

            if ($this->getCFGDef('showNoPublish', 0)) {
                if ($where != '') {
                    $where = "WHERE {$where}";
                } else {
                    $where = '';
                }
            } else {
                if ($where != '') {
                    $where = "WHERE {$where} AND ";
                } else {
                    $where = "WHERE {$where} ";
                }
                $where .= "c.deleted=0 AND c.published=1";
            }


            $fields = $this->getCFGDef('selectFields', 'c.*,ld.*');
            $group = $this->getGroupSQL($this->getCFGDef('groupBy', 'c.id'));
            $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
            list($tbl_site_content, $sort) = $this->injectSortByTV($tbl_site_content . ' ' . $this->_filters['join'],
                $sort);

            $limit = $this->LimitSQL($this->getCFGDef('queryLimit', 0));
            $ld_table = $this->modx->getFullTableName('likedislike');
            $rs = $this->dbQuery("SELECT {$fields} FROM {$ld_table} `ld` LEFT JOIN {$tbl_site_content} ON `ld`.`rid`=`c`.`id` {$where} {$group} {$sort} {$limit}");

            $rows = $this->modx->db->makeArray($rs);

            foreach ($rows as $item) {
                $out[$item['id']] = $item;
            }
        }

        return $out;
    }

    /**
     * @return array
     */
    protected function getChildrenList()
    {
        $where = array();
        $out = array();

        $tmpWhere = $this->getCFGDef('addWhereList', '');
        $tmpWhere = sqlHelper::trimLogicalOp($tmpWhere);
        if (!empty($tmpWhere)) {
            $where[] = $tmpWhere;
        }

        $tmpWhere = sqlHelper::trimLogicalOp($this->_filters['where']);
        if (!empty($tmpWhere)) {
            $where[] = $tmpWhere;
        }

        $tbl_site_content = $this->getTable('site_content', 'c');

        $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
        $ld_table = $this->modx->getFullTableName('likedislike');
        list($from, $sort) = $this->injectSortByTV("{$ld_table} `ld` LEFT JOIN {$tbl_site_content} ON `ld`.`rid`=`c`.`id` {$this->_filters['join']}", $sort);

        $sanitarInIDs = $this->sanitarIn($this->IDs);

        $tmpWhere = null;
        if ($sanitarInIDs != "''") {
            switch ($this->getCFGDef('showParent', '0')) {
                case '-1':
                    $tmpWhere = "c.parent IN (" . $sanitarInIDs . ")";
                    break;
                case 0:
                    $tmpWhere = "c.parent IN (" . $sanitarInIDs . ") AND c.id NOT IN(" . $sanitarInIDs . ")";
                    break;
                case 1:
                default:
                    $tmpWhere = "(c.parent IN (" . $sanitarInIDs . ") OR c.id IN({$sanitarInIDs}))";
                    break;
            }
        }
        if (($addDocs = $this->getCFGDef('documents', '')) != '') {
            $addDocs = $this->sanitarIn($this->cleanIDs($addDocs));
            if (empty($tmpWhere)) {
                $tmpWhere = "c.id IN({$addDocs})";
            } else {
                $tmpWhere = "((" . $tmpWhere . ") OR c.id IN({$addDocs}))";
            }
        }
        if (!empty($tmpWhere)) {
            $where[] = $tmpWhere;
        }
        if (!$this->getCFGDef('showNoPublish', 0)) {
            $where[] = "c.deleted=0 AND c.published=1";
        }
        $where[] = "`ld`.`classKey`='{$this->getCFGDef('classKey','modResource')}'";
        if (!empty($where)) {
            $where = "WHERE " . implode(" AND ", $where);
        } else {
            $where = '';
        }
        $fields = $this->getCFGDef('selectFields', 'c.*,ld.*');
        $group = $this->getGroupSQL($this->getCFGDef('groupBy', 'c.id'));

        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $sql = $this->dbQuery("SELECT {$fields} FROM " . $from . " " . $where . " " .
                $group . " " .
                $sort . " " .
                $this->LimitSQL($this->getCFGDef('queryLimit', 0))
            );

            $rows = $this->modx->db->makeArray($sql);

            foreach ($rows as $item) {
                $out[$item['id']] = $item;
            }
        }

        return $out;
    }
}
