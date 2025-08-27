<?php
namespace Sofast\Support;

use Sofast\Core\Config;
use Sofast\Core\Sf;

class Template
{
    private $viewData = [];
    private $viewTpl  = [];
    private $content  = '';
    private $tplDir   = '';
    private $fomrs    = [];

    function __construct($dir = '')
    {
        $this->tplDir = $dir;
    }

    public function set($key, $val = '')
    {
        if (is_array($key)) {
            $this->viewData = array_merge_recursive($this->viewData, $key);
        } else {
            $this->viewData[$key] = $val;
        }

    }

    public function apply($name, $tpl)
    {
        $tpl && $this->viewTpl[$name] = $tpl;
    }

    public function setTplDir($dir)
    {
        $this->tplDir = $dir;
    }

    public function getTplDir()
    {
        return $this->tplDir;
    }

    public function getContent($tpl, $key = '')
    {
        //归划模板目录
        $_tpls = explode('/', $tpl);
        $tpl   = implode('/', array_map('strtolower', $_tpls));
        if (! $this->getTplDir()) {
            $this->setTplDir(config::get("view_dir"));
        }

        $key = empty($key) ? $tpl : $key;
        extract($this->viewData);
        ob_start();
        if (is_file($this->getTplDir() . $tpl . ".php")) {
            include $this->getTplDir() . $tpl . ".php";
        } else if (is_file(config::get("view_dir") . $tpl . ".php")) {
            include config::get("view_dir") . $tpl . ".php";
        }
        $this->viewData[$key] = ob_get_contents();
        ob_end_clean();
        return $this->viewData[$key];
    }

    public function parse($tpl)
    {
        foreach ($this->viewTpl as $key => $file) {
            $this->getContent($file, $key);
        }
        $this->content = $this->getContent($tpl);
        //插入csrf
        $this->content = preg_replace_callback('/<\/form>/is', function () {return $this->getCsrf() . "\n</form>";}, $this->content);

        if (config::get("auto_create_html", false)) {
            $file = trim($_SERVER['PATH_INFO'], '/');
            //是否需要获取token
            if (config::get('token_open', false) && config::get('parse_mode', 'PATH_INFO') == 'PATH_INFO') {
                $info  = explode("/", $file);
                $token = array_shift($info);
                $file  = implode('/', $info);
            }

            if ($file) {
                $file = WEBROOT . '/' . $file;
                sf::getLib("Files")->write($file, $this->content);
            }
        }
        return $this->content;
    }

    private static function write($fileName, $content)
    {
        return file_put_contents(trim(config::get("cache_dir", "cache"), "/") . "/" . str_replace("/", "-", $fileName), $content);
    }

    private static function read($fileName)
    {
        $file = trim(config::get("cache_dir", "cache"), "/") . "/" . str_replace("/", "-", $fileName);
        if (is_file($file) && ((time() - filemtime($file)) < config::get("cache_time", "120"))) {
            return file_get_contents($file);
        } else {
            return false;
        }

    }

    public function display($tpl)
    {
        echo $this->parse($tpl);
        exit;
    }

    public function part($tpl)
    {
        return $this->getContent($tpl);
    }

    public function getCsrf()
    {
        $html                         = '';
        $form_id                      = md5(time() . rand(100, 9999));
        $csrf                         = md5($form_id . time() . rand(100, 9999));
        $_SESSION['__csrf'][$form_id] = $csrf; //保存数据用于验证
        $html .= '<input name="' . $form_id . '" type="hidden" value="' . $csrf . '" />';
        return $html;
    }

    /**
     * 验证CSRF
     */
    public function checkCsrf()
    {
        foreach ($_SESSION['__csrf'] as $form_id => $csrf) {
            if (isset($_POST[$form_id]) && $_POST[$form_id] == $csrf) {
                unset($_SESSION['__csrf'][$form_id]); //验证后直接销毁
                return true;
            }
        }
        return false;
    }

}
