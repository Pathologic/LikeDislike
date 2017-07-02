<?php namespace LikeDislike;

include_once(MODX_BASE_PATH . 'assets/lib/MODxAPI/autoTable.abstract.php');

/**
 * Class Log
 * @package LikeDislike
 */
class Log extends \autoTable
{
    protected $table = 'likedislike_log';
    protected $pkName = 'id';
    public $default_field = array(
        'id'        => 0,
        'rid'       => 0, //id ресурса
        'classKey'  => '', //класс ресурса
        'ip'        => '', //ip пользователя
        'uid'       => 0, //id пользователя
        'createdon' => 0
    );

    /**
     * @param $resourceId
     * @param string $classKey
     * @return bool|int
     */
    public function isLogged()
    {
        $out = false;
        $resourceId = (int)$this->get('rid');
        $classKey = $this->get('classKey');
        if ($resourceId && !empty($classKey) && is_scalar($classKey)) {
            if (isset($_COOKIE[md5($classKey . $resourceId)])) {
                $where = array();
                $where[] = "`rid`={$resourceId}";
                $where[] = "`classKey`='{$classKey}'";
                $where = implode(' AND ', $where);
                $q = $this->query("SELECT `id` FROM {$this->makeTable($this->table)} WHERE {$where}");
                if ($this->modx->db->getRecordCount($q)) {
                    $out = true;
                } else {
                    $this->deleteCookie();
                }
            } else {
                $classKey = $this->modx->db->escape($classKey);
                $uid = $this->modx->getLoginUserID('web');
                $ip = \APIhelpers::getUserIP();
                $where = array();
                $where[] = "`rid`={$resourceId}";
                $where[] = "`classKey`='{$classKey}'";
                $where[] = $uid ? "`uid`={$uid}" : "`ip`='{$ip}'";
                $where = implode(' AND ', $where);
                $q = $this->query("SELECT `id` FROM {$this->makeTable($this->table)} WHERE {$where}");
                if ($cnt = $this->modx->db->getRecordCount($q)) {
                    $out = $cnt;
                    $this->set('classKey', $classKey)->set('rid', $resourceId)->setCookie();
                }
            }
        }

        return $out;
    }


    /**
     * @param null $fire_events
     * @param bool $clearCache
     * @return bool|null
     */
    public function save($fire_events = null, $clearCache = false)
    {
        $this->touch('createdon')->set('uid', $this->modx->getLoginUserID('web'))->set('ip',
            \APIhelpers::getUserIP())->setCookie();

        return parent::save($fire_events, $clearCache);
    }

    /**
     * @return $this
     */
    public function setCookie()
    {
        $cookieName = md5($this->get('classKey') . $this->get('rid'));
        $secure = $this->modxConfig('server_protocol') == 'http' ? false : true;
        $cookieValue = time();
        $cookieExpires = time() + 60 * 60 * 24 * 365 * 5;
        setcookie($cookieName, $cookieValue, $cookieExpires, '/', '', $secure, true);

        return $this;
    }
    public function reset() {
        $resourceId = (int)$this->get('rid');
        $classKey = $this->get('classKey');
        if ($resourceId && !empty($classKey) && is_scalar($classKey)) {
            $classKey = $this->modx->db->escape($classKey);
            $this->modx->db->query("DELETE FROM {$this->makeTable($this->table)} WHERE `rid`={$resourceId} && `classKey`='{$classKey}'");
        }
    }

    public function deleteCookie(){
        $cookieName = md5($this->get('classKey') . $this->get('rid'));
        unset($_COOKIE[$cookieName]);
        setcookie($cookieName, null, -1, '/');

        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function touch($field)
    {
        $this->set($field, date('Y-m-d H:i:s', time() + $this->modx->config['server_offset_time']));

        return $this;
    }

    public function createTable()
    {
        $q = "CREATE TABLE IF NOT EXISTS {$this->makeTable($this->table)} (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `rid` int(10) NOT NULL DEFAULT 0,
            `classKey` varchar(20) NOT NULL DEFAULT '',
            `ip` varchar(255) NOT NULL DEFAULT '',
            `uid` int(10) NOT NULL DEFAULT 0,
            `createdon` datetime NOT NULL,
            PRIMARY KEY  (`id`),
            KEY `resource` (`rid`,`classKey`),
            KEY `ip` (`ip`),
            KEY `uid` (`uid`),
            KEY `createdon` (`createdon`)
            ) ENGINE=MyISAM
            ";
        $this->query($q);
    }
}
