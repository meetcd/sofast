<?php
namespace Sofast\Support;
use Sofast\Core\Lang;
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

    public function __construct($totalnum='', $maxnum='',$key="",$form_vars=array())
    {
		$this->totalnum = $totalnum;
		$this->maxnum   = $maxnum;
		$this->key      = $key;
		$has_post		= false;
		$this->navchar  = array(lang::get('first'),'[<]','[>]',lang::get('last'));
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
				if (!in_array($key,array("totalnum".$this->key,"pagenum".$this->key,"controller","method")))
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
	}

	public function total()
	{			
		return sprintf(lang::get('%d page and %d records'),$this->totalpage,$this->totalnum);
	}

	public function fromto()
	{
		$startnum = $this->startnum + 1;
		if ($this->totalnum==0) $startnum = 0;
		return sprintf(lang::get('from %d to %d records'),$startnum,$this->endnum);
	}

	public function navbar($num_size=0,$nolink_show=false,$nolink_color="#ff0000")
	{
		if ($this->totalpage <= 1) return;
		$str_first = $str_pre = $str_frontell = $str_num = $str_backell = $str_next = $str_last = '';
		if ($num_size>0)
		{
			$tmpnum    = floor($num_size/2);
			$startpage = max(min($this->pagenum - $tmpnum, $this->totalpage - $num_size + 1), 1);
            $endpage   = min($startpage + $num_size - 1, $this->totalpage);

            if ($startpage > 1)              $str_frontell = " … ";

            if ($endpage < $this->totalpage) $str_backell  = " … ";

            $str_num = "";

            for ($i = $startpage; $i <= $endpage; $i++)
            {
                if ($i == $this->pagenum) $str_num .= " <font color=\"".$nolink_color."\">".$i."</font> ";
                else $str_num .= " <a href=\"".$this->linkhead."/pagenum".$this->key."/".$i."\">".$i."</a> ";
            }
        }

        if ($this->pagenum > 1)
        {
            $str_first = " <a href=\"".$this->linkhead."/pagenum".$this->key."/1\">".$this->navchar[0]."</a> ";
            $str_pre   = " <a href=\"".$this->linkhead."/pagenum".$this->key."/".($this->pagenum-1)."\">".$this->navchar[1]."</a> ";
        }else if ($nolink_show){
            $str_first = " <font color=\"".$nolink_color."\">".$this->navchar[0]."</font> ";
            $str_pre   = " <font color=\"".$nolink_color."\">".$this->navchar[1]."</font> ";
        }

        if ($this->pagenum < $this->totalpage)
        {
            $str_next  = " <a href=\"".$this->linkhead."/pagenum".$this->key."/".($this->pagenum+1)."\">".$this->navchar[2]."</a> ";
            $str_last  = " <a href=\"".$this->linkhead."/pagenum".$this->key."/".$this->totalpage."\">".$this->navchar[3]."</a> 　";
        }else if ($nolink_show){
            $str_next  =" <font color=\"".$nolink_color."\">".$this->navchar[2]."</font> ";
            $str_last  =" <font color=\"".$nolink_color."\">".$this->navchar[3]."</font> ";
        }

        return $str_first.$str_pre.$str_frontell.$str_num.$str_backell.$str_next.$str_last;
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
		return sprintf(lang::get('go %s /%d pages'),$write,$this->totalpage);
	}

	public function maxnum()
	{
		return sprintf(lang::get("%d records per page"),$this->maxnum);
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