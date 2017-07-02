<?php namespace LikeDislike;
include_once (MODX_BASE_PATH . 'assets/snippets/LikeDislike/model/ld.php');

/**
 * Class Controller
 */
class ModuleController {
    protected $modx = null;
    protected $data = null;
    public $isExit = false;
    public $output = null;
    public $dlParams = array(
        "controller"  => "likedislike_module",
        "dir" => "assets/modules/LikeDislike/DocLister/",
        "table"       => "",
        'idField'     => "",
        "api"         => 1,
        "idType"      => "documents",
        'ignoreEmpty' => 1,
        'makeUrl'     => 0,
        'JSONformat'  => "new",
        'display'     => 10,
        'offset'      => 0,
        'sortBy'      => "",
        'selectFields' => "c.id,c.pagetitle,ld.*",
        'sortDir'     => "desc",
    );


    /**
     * Controller constructor.
     * @param \DocumentParser $modx
     */
    public function __construct(\DocumentParser $modx)
    {
        $this->modx = $modx;
        $this->data = new Model($modx);
        $this->dlInit();
    }

    /**
     *
     */
    public function callExit()
    {
        if ($this->isExit) {
            echo $this->output;
            exit;
        }
    }

    public function reset(){
        $rid = isset($_POST['rid']) ? (int)$_POST['rid'] : 0;
        $this->data->reset($rid);
        return array('success'=>true);
    }

    /**
     * @return string
     */
    public function listing()
    {
        return $this->modx->runSnippet("DocLister", $this->dlParams);
    }

    /**
     *
     */
    public function dlInit()
    {
        if (isset($_POST['rows'])) {
            $this->dlParams['display'] = (int)$_POST['rows'];
        }
        $offset = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $offset = $offset ? $offset : 1;
        $offset = $this->dlParams['display'] * abs($offset - 1);
        $this->dlParams['offset'] = $offset;
        if (isset($_POST['sort'])) {
            $this->dlParams['sortBy'] = '`'.preg_replace('/[^A-Za-z0-9_\-]/', '', $_POST['sort']).'`';
        }
        if (isset($_POST['order']) && in_array(strtoupper($_POST['order']), array("ASC", "DESC"))) {
            $this->dlParams['sortDir'] = $_POST['order'];
        }
        foreach ($this->dlParams as &$param) {
            if (empty($param)) {
                unset($param);
            }
        }
    }
}
