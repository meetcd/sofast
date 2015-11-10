<?php
use System\Core\Config;
if (! function_exists('append_config')) {
    /**
     * Assign high numeric IDs to a config item to force appending.
     *
     * @param  array  $array
     * @return array
     */
    function append_config(array $array)
    {
        $start = 9999;

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $start++;

                $array[$start] = Arr::pull($array, $key);
            }
        }

        return $array;
    }
}

if (! function_exists('getmicrotime')) {
    /**
     * Assign high numeric IDs to a config item to force appending.
     *
     * @param  array  $array
     * @return array
     */
    function getmicrotime() {
        list ( $usec, $sec ) = explode ( " ", microtime () );
        return (( float ) $usec + ( float ) $sec);
    }
}

if (! function_exists('include_dir')) {
    /**
     * Assign high numeric IDs to a config item to force appending.
     *
     * @param  array  $array
     * @return array
     */
    function include_dir($dir) {
        $handle = @dir($dir);
        while (($file = $handle->read()) !== false)
        {
            if(is_file($dir.'/'.$file)) include_once($dir.'/'.$file);
        }
        $handle->close();
    }
}

if (! function_exists('processVariables')) {
    /**
     * Assign high numeric IDs to a config item to force appending.
     *
     * @param  array  $var
     * @param  string  $key
     * @return array
     */
    function processVariables(&$var, $key)
    {
        if(!get_magic_quotes_gpc())
        {
            if(is_array($var))
            {
                foreach($var as $k => $v)
                    processVariables($var[$k], $k);
            }else $var = addslashes($var);
            
            //$str = str_replace("_", "\_", $str); // 把 '_'过滤掉     
            //$str = str_replace("%", "\%", $str); // 把' % '过滤掉   
        }
    }
}

if (! function_exists('site_url')) {
    function site_url($uri='',$route = true,$real_uri='')
    {
        $site_url = $real_uri ? trim($real_uri,"/") : trim(config::get("base_url"),"/");
        if(config::get("index_page"))
            $site_url .= "/".config::get("index_page");
        if(config::get('token_open',false))
            $site_url .= "/".getToken();
        $site_url .= "/".($route ? router::create_routes($uri) : trim($uri,"/"));
        return trim($site_url);
    }
}

if (! function_exists('site_path')) {
    function site_path($uri='',$real_uri='')
    {
        return trim($real_uri ? $real_uri : config::get("base_url"),'/')."/".trim($uri,'/');
    }
}

if (! function_exists('base_url')) {
    function base_url()
    {
        return trim(config::get("base_url"),'/')."/";
    }
}

if (! function_exists('link_to')) {
    function link_to($uri='',$name='link',$opt=array())
    {
        foreach((array)$opt as $key => $val){
            $a_opt .= " ".$key.'="'.$val.'"';
        }
        return '<a href="'.site_url($uri).'" '.$a_opt.' >'.$name.'</a>';
    }
}

if (! function_exists('getFromUrl')) {
    function getFromUrl($debarUrl='',$targetUrl='')
    {
        $fromUrl = $_POST['fromUrl'] ? $_POST['fromUrl'] : $_SERVER['HTTP_REFERER'];
        if($debarUrl && $targetUrl && ($debarUrl == $fromUrl)) return $targetUrl;
        else return $fromUrl;
    }
}

if (! function_exists('getColumnStr')) {
    function getColumnStr($subject='',$orderfield='')
    {
        $result = router::get();
        $o_orderfield = $result['orderfield'];
        $result['ordermode'] = ($result['ordermode'] == 'DESC') ? 'ASC' : 'DESC';
        $result['orderfield'] = $orderfield;
        
        if(config::get('token_open',false)){
            array_shift($result);
            unset($_GET['token']);
            unset($result['token']);
        }
        
        $str  = array_shift($result);
        $str .= '/'.array_shift($result);
        
        foreach($result as $key => $val) $str .= '/'.$key.'/'.$val;
        //为排序字段加上标记
        if($o_orderfield == $orderfield) $class = array('class'=>$result['ordermode']);
        else $class = array();
        
        return link_to($str,$subject,$class);
    }
}

if (! function_exists('getToken')) {
    function getToken()
    {
        return substr('TK'.md5(session_id() . $_SESSION['userid']),0,6);    
    }
}

if (! function_exists('_include')) {
    function _include($file='')
    {
        return include(config::get("view_dir").$file);
    }
}

if (! function_exists('M')) {
    function M($m)
    {
        return sf::getModel($m);    
    }
}
