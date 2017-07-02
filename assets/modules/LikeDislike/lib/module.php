<?php namespace LikeDislike;
include_once (MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once (MODX_BASE_PATH . 'assets/snippets/LikeDislike/model/ld.php');
include_once (MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');


class Module {
    protected $modx = null;
    protected $params = array();
    protected $DLTemplate = null;


    public function __construct(\DocumentParser $modx, $debug = false) {
        $this->modx = $modx;
        $this->params = $modx->event->params;
        $this->DLTemplate = \DLTemplate::getInstance($this->modx);
        $ld = new Model($modx);
        $ld->createTable();
    }

    /**
     * @return bool|string
     */
    public function prerender() {
        $tpl = MODX_BASE_PATH.'assets/modules/LikeDislike/tpl/module.tpl';
        $output = '';
        if(file_exists($tpl)) {
            $output = file_get_contents($tpl);
        }
        return $output;
    }

    /**
     * @return bool|string
     */
    public function render() {
        $output = $this->prerender();
        $ph = array(
            'connector'	    => 	$this->modx->config['site_url'].'assets/modules/LikeDislike/ajax.php',
            'theme' => $this->modx->config['manager_theme'],
            'site_url'		=>	$this->modx->config['site_url'],
            'manager_url'	=>	MODX_MANAGER_URL
        );
        $output = $this->DLTemplate->parseChunk('@CODE:'.$output,$ph);
        return $output;
    }
}
