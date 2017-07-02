<?php namespace LikeDislike;
include_once ('log.php');
/**
 * Class LikeDislike
 * @package LikeDislike
 */
class Model {
    protected $modx = null;
    protected $logger = null;
    protected $table = 'likedislike';

    /**
     * LikeDislike constructor.
     * @param \DocumentParser $modx
     */
    public function __construct(\DocumentParser $modx) {
        $this->modx = $modx;
        $this->logger = new Log($modx);
        $this->table = $modx->getFullTableName($this->table);
    }

    /**
     * @param int $resourceId
     * @param string $classKey
     * @return $this
     */
    public function like($resourceId, $classKey = 'modResource') {
        $resourceId = (int)$resourceId;
        if ($resourceId
            && !empty($classKey)
            && is_scalar($classKey)
            && !$this->isLogged($resourceId, $classKey)
        ) {
            $classKey = $this->modx->db->escape($classKey);
            $this->modx->db->query("INSERT INTO {$this->table} (`rid`, `classKey`, `like`, `dislike`) VALUES ({$resourceId}, '{$classKey}', 1, 0) ON DUPLICATE KEY UPDATE `like` = `like` + 1");
            $this->saveLog($resourceId, $classKey);
        }

        return $this;
    }

    /**
     * @param $resourceId
     * @param string $classKey
     * @return $this
     */
    public function dislike($resourceId, $classKey = 'modResource') {
        $resourceId = (int)$resourceId;
        if ($resourceId
            && !empty($classKey)
            && is_scalar($classKey)
            && !$this->isLogged($resourceId, $classKey)
        ) {
            $classKey = $this->modx->db->escape($classKey);
            $this->modx->db->query("INSERT INTO {$this->table} (`rid`, `classKey`, `like`, `dislike`) VALUES ({$resourceId}, '{$classKey}', 0, 1) ON DUPLICATE KEY UPDATE `dislike` = `dislike` + 1");
            $this->saveLog($resourceId, $classKey);
        }

        return $this;
    }

    /**
     * @param $resourceId
     * @param string $classKey
     * @return array
     */
    public function stat($resourceId, $classKey = 'modResource') {
        $out = array("like" => 0, "dislike" => 0);
        $resourceId = (int)$resourceId;
        if ($resourceId && !empty($classKey) && is_scalar($classKey)) {
            $classKey = $this->modx->db->escape($classKey);
            $q = $this->modx->db->query("SELECT * FROM {$this->table} WHERE `rid`={$resourceId} AND `classKey` = '{$classKey}'");
            if ($this->modx->db->getRecordCount($q)) {
                $data = $this->modx->db->getRow($q);
                $out["like"] = $data['like'];
                $out["dislike"] = $data['dislike'];
            }
        }

        return $out;
    }

    /**
     * @param $resourceId
     * @param string $classKey
     */
    public function reset($resourceId, $classKey = 'modResource') {
        $resourceId = (int)$resourceId;
        if ($resourceId
            && !empty($classKey)
            && is_scalar($classKey)
        ) {
            $classKey = $this->modx->db->escape($classKey);
            $this->modx->db->query("DELETE FROM {$this->table} WHERE `rid`={$resourceId} && `classKey`='{$classKey}'");
            $this->logger->set('rid',$resourceId)->set('classKey',$classKey)->reset();
        }
    }

    /**
     * @param $resourceId
     * @param $classKey
     * @return bool|int
     */
    public function isLogged($resourceId, $classKey) {
        return $this->logger->set('rid',$resourceId)->set('classKey',$classKey)->isLogged();
    }

    /**
     * @param $resourceId
     * @param $classKey
     * @return bool|null
     */
    public function saveLog($resourceId, $classKey) {
        return $this->logger->create(array(
            'rid' => $resourceId,
            'classKey' => $classKey
        ))->save();
    }

    public function createTable() {
        $q = "CREATE TABLE IF NOT EXISTS {$this->table} (
            `rid` INT(10) NOT NULL UNIQUE,
            `classKey` VARCHAR(20) NOT NULL DEFAULT '',
            `like` INT(10) NOT NULL DEFAULT 0,
            `dislike` INT(10) NOT NULL DEFAULT 0,
            UNIQUE KEY `resource`(`rid`, `classKey`)
            ) Engine=MyISAM
            ";
        $this->modx->db->query($q);
        $this->logger->createTable();
    }
}
