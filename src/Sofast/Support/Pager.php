<?php
namespace Sofast\Support;
use Sofast\Core\Lang;
use Sofast\Core\Config;
use Sofast\Support\Collection;

class Pager extends Collection
{
	private $maxnum;
	private $navchar	   = array();
	private $form_vars     = array();
	private $key;
    private $totalnum;
    private $totalpage;
    private $startnum;
    private $endnum;
    private $pagenum;
    private $shownum;
    private $field;
    private $linkhead;
	private $cur_row		= 0;
	private $total_row		= 0;
	private $configs = array();

    public function __construct($totalnum='', $maxnum='',$key="",$form_vars=array())
    {
		$this->totalnum = $totalnum;
		$this->maxnum   = $maxnum;
		$this->key      = $key;
		$has_post		= false;
		$this->navchar  = array(lang::get('page_first'),'[<]','[>]',lang::get('page_last'));
		$querystring 	= array($_GET["controller"]."/".$_GET["method"]);
		$form_vars && $this->setFormVars($form_vars);

		if (count($this->form_vars) > 0)
		{
			foreach ($this->form_vars as $val){
				if($_POST[$val]){
					$querystring[] = $val."/".urlencode($_POST[$val]);
					$has_post = true;
				}
			}
		}

		if (count($_GET) > 0 && !$has_post)
		{
			foreach ($_GET as $key => $val)
			{
				if (!in_array($key,array("totalnum".$this->key,"pagenum".$this->key,"controller","method",'_pjax')))
					$querystring[] = $key."/".urlencode($val);
			}
		}

		if (isset($_GET["maxnum".$this->key]) && $_GET["maxnum".$this->key] > 0)
		{
			$this->maxnum = sprintf('%d',$_GET["maxnum".$this->key]);
		}

		if ($this->maxnum < 1 ) $this->maxnum = $this->totalnum;

		if ($this->totalnum < 1)
		{
			$this->totalnum  = 0 ;
			$this->totalpage = 0 ;
			$this->pagenum   = 0 ;
			$this->startnum  = 0 ;
			$this->endnum    = 0 ;
			$this->shownum   = 0 ;
		}else{
			$this->totalpage = ceil($this->totalnum/$this->maxnum);
			$this->pagenum   = (isset($_GET["pagenum".$this->key]) && $_GET["pagenum".$this->key] > 0)
                                   ? sprintf('%d',$_GET["pagenum".$this->key])
                                   : 1;
			if ($this->pagenum > $this->totalpage) $this->pagenum = $this->totalpage;
			$this->startnum = max(($this->pagenum - 1) * $this->maxnum,0);
			$this->endnum   = min($this->startnum + $this->maxnum, $this->totalnum);
			$this->shownum  = $this->endnum - $this->startnum;
		}
		$querystring[] = "totalnum" . $this->key . "/" . $this->totalnum;
		if (isset($_GET["maxnum" . $this->key])) $querystring[] = "maxnum" . $this->key . "/" . $this->maxnum;
		$this->linkhead = site_url(implode("/",$querystring));
		$this->configs = Config::get('pagerule');
	}

	public function total()
	{
		return sprintf($this->configs['config.total'],$this->totalpage,$this->totalnum);
	}

	public function fromto()
	{
		$startnum = $this->startnum + 1;
		if ($this->totalnum==0) $startnum = 0;
		return sprintf($this->configs['page']['from_to'],$startnum,$this->endnum);
	}

	public function navbar($num_size=5,$nolink_show=false,$nolink_color="#ff0000")
	{
		if ($this->totalpage <= 1) return;
		$str_first = $str_pre = $str_frontell = $str_num = $str_backell = $str_next = $str_last = '';
		if ($num_size>0)
		{
			$tmpnum    = floor($num_size/2);
			$startpage = max(min($this->pagenum - $tmpnum, $this->totalpage - $num_size + 1), 1);
            $endpage   = min($startpage + $num_size - 1, $this->totalpage);

            if ($startpage > 1) $str_frontell = sprintf($this->configs['page']['frontell'],' … ');

            if ($endpage < $this->totalpage) $str_backell  = sprintf($this->configs['page']['backell'],' … ');

            $str_num = "";

            for ($i = $startpage; $i <= $endpage; $i++)
            {
            	if ($i == $this->pagenum) $str_num .= sprintf($this->configs['page']['nowpage'],$i);
            	else $str_num .= sprintf($this->configs['page']['str_num'],$this->linkhead,$this->key,$i,$i);
            }
        }

        if ($this->pagenum > 1)
        {
        	$str_first = sprintf($this->configs['page']['first'],$this->linkhead,$this->key,$this->navchar[0]);
        	$str_pre = sprintf($this->configs['page']['pre'],$this->linkhead,$this->key,($this->pagenum-1),$this->navchar[1]);
        }else if ($nolink_show){
        	$str_first = sprintf($this->configs['page']['first'],'','',$this->navchar[0]);
            $str_pre = sprintf($this->configs['page']['pre'],'','',$this->navchar[1]);
        }

        if ($this->pagenum < $this->totalpage)
        {
        	$str_next  = sprintf($this->configs['page']['next'],$this->linkhead,$this->key,($this->pagenum+1),$this->navchar[2]);
        	$str_last  = sprintf($this->configs['page']['last'],$this->linkhead,$this->key,$this->totalpage,$this->navchar[3]);
        }else if ($nolink_show){
        	$str_next  = sprintf($this->configs['page']['next'],'','','',$this->navchar[2]);
            $str_last  = sprintf($this->configs['page']['last'],'','','',$this->navchar[3]);
        }
        $strs = $str_first.$str_pre.$str_frontell.$str_num.$str_backell.$str_next.$str_last;
        return sprintf($this->configs['page']['div'],$strs);
	}

	public function pagejump()
	{
		if ($this->totalpage <= 1) return;
		$name  = "pagenum".$this->key;
		$write = "<select name='".$name."' id='pagejump' ";
		$write .= "onchange='javascript:location.href=this.options[this.selectedIndex].value'>";
		for ($i = 1; $i <= $this->totalpage; $i++)
		{
			$write .= "<option value=".$this->linkhead."/".$name."/".$i;
			if ($this->pagenum == $i) $write .= " selected";
			$write .= ">".$i."</option>";
        }
		$write .= "</select>";
		return sprintf($this->configs['page']['jump'],$write,$this->totalpage);
	}

	public function maxnum()
	{
		return sprintf($this->configs['page']['maxnum'],$this->maxnum);
	}
	
	public function getStartNum()
	{
		return $this->startnum;
	}
	
	public function getShowNum()
	{
		return $this->shownum;
	}
	
	public function setFormVars($v=array())
	{
		if(is_array($v))
			$this->form_vars = $v;
	}
	
}
?>