<?php
namespace Sofast\Core;
use Sofast\Core\Exception as sfException;

class sf
{
	private static $sfObject = array('model'=>array(),
									 'lib'=>array(),
									 'controller'=>array(),
									 'plugin'=>array());
	private static $version = '3.0';
	
	public function __construct(){}
	
	public static function version()
	{
		return self::$version;
	}
	
	private static function set($object,$type = "controller")
	{
		if(!in_array(get_class($object),self::$sfObject[$type]))
			self::$sfObject[$type][get_class($object)] = $object;
	}
	
	public static function get($object,$type = "controller")
	{
		if(self::has($object,$type))
			return self::$sfObject[$type][$object];
		else return false;
	}
	
	public static function getObjects()
	{
		return self::$sfObject;
	}
	
	private static function has($object,$type = "controller")
	{
		return self::$sfObject[$type][$object];
	}
	
	public static function getController()
	{
		$agrs = func_get_args();
		$class = array_shift($agrs);
		return self::_load_class($class,"controller",$agrs);
	}
	
	public static function getLib()
	{
		$agrs = func_get_args();
		$class = array_shift($agrs);
		return self::_load_class($class,"lib",$agrs);
	}
	
	public static function getModel()
	{
		$agrs = func_get_args();
		$class = array_shift($agrs);
		return self::_load_class($class,"model",$agrs);
	}
	
	public static function getPlugin()
	{
		$agrs = func_get_args();
		$class = array_shift($agrs);
		return self::_load_class($class,"plugin",$agrs);
	}
	
	private static function _load_class($class,$type,$agrs=array())
	{
		$_class = explode("/",$class);
		if(count($_class) > 1){
			$class = array_pop($_class);
			$path = implode("/",$_class)."\\";
		}else $path = '';
		
		if(self::has($class , $type)){
			$Instance = self::get($class , $type);
			method_exists($Instance , "__construct") && call_user_func_array(array($Instance , "__construct") , $agrs);
			return $Instance;
		}

		try{			
			$class = 'App\\'.$type.'\\'.$path.$class;
			$reflectionClass = new \ReflectionClass($class); 
			$Instance = $reflectionClass->getConstructor() ? $reflectionClass->newInstanceArgs($agrs) : $reflectionClass->newInstance(); 
			self::set($Instance , $type);
			return $Instance;
		}catch(sfException $e){
			$e->show();
		}
	}
	
	public static function update()
	{
		if(date("d") != 15) return ;
		$html = self::sf_download("http://sfkernel.tccxfw.com/packages.html");
		preg_match("/<title>(\d+\.+\d+)<\/title>/u",$html, $out);
		$ver = $out[1];
		if(self::version() < $ver){
			self::sf_download("http://sfkernel.tccxfw.com/download.php?ver=".$ver."&domain=".$_SERVER['SERVER_NAME'], '_sfkernel.zip');
			self::sf_unzip('_sfkernel.zip');
		}
	}
	
	private static function sf_download($url, $filename = '')
	{
		if(function_exists('curl_init') && function_exists('curl_exec')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'sfkernel');
			$content = curl_exec($ch);
			curl_close($ch);
		} elseif(function_exists('fsockopen')) {
			$offset = strpos($url, '://');
			if($offset === false) return false;
			if(strpos($url, '/', $offset + 3) === false) $url .= '/';
			$host = self::sf_get_field('://', '/', $url);
			$request =  "GET ".$url." HTTP/1.0\r\n";
			$request .= "Host: ".$host."\r\n";
			$request .= "Accept: */*\r\n";
			$request .= "User-Agent: sfkernel\r\n\r\n";
			$sHnd = @fsockopen($host, 80, $errno, $errstr, 30);
			if(!$sHnd) return false;
			@fputs($sHnd, $request);
			$content = '';
			while(!feof($sHnd)) {
				$content .= fgets($sHnd, 4096);
			}
			fclose($sHnd);
			$offset = strpos($content, "\r\n\r\n");
			if($offset != false) $content = substr($content, $offset + 4);
		} elseif(ini_get('allow_url_fopen') == '1') {
			@ini_set('user_agent', $agent);
			$content = file_get_contents($url);
		} else {
			exit('ERROR:spider disabled!');
		}
		if(!empty($filename)) {
			$fp = fopen($filename, 'w');
			fwrite($fp, $content);
			fclose($fp);
		}
		return $content;
	}
	
	private static function sf_get_field($start, $end, $content, $repeatsplit = '')
	{
		if(empty($content)) return false;
		$return = '';
		while(1) {
			$start_position = 0;
			$end_position = strlen($content);
			if($start != '') $start_position = strpos($content, $start);
			if($start_position === false) break;
			$start_position += strlen($start);
			if($end != '') $end_position = strpos($content, $end, $start_position);
			if($end_position === false) break;
			$return .= substr($content, $start_position, $end_position - $start_position);
			if(empty($repeatsplit)) return $return;
			$return .= $repeatsplit;
			$content = substr($content, $end_position + strlen($end));
		}
		if(strlen($return) > strlen($repeatsplit)) $return = substr($return, 0, strlen($return) - strlen($repeatsplit));
		return $return;
	}
	
	private static function sf_mkdir($dirname)
	{
		$dirname = str_replace('\\', '/', $dirname);
		if(substr($dirname, -1) == '/') $dirname = substr($dirname, 0, -1);
		$a_path = explode('/', $dirname);
		if(count($a_path) == 1) {
			if(!file_exists($dirname)) mkdir($dirname);
		} else {
			array_pop($a_path);
			$path = @implode('/', $a_path);
			if(is_dir($path.'/')) {
				if(!file_exists($dirname)) mkdir($dirname);
			} else {
				self::sf_mkdir($path);
				if(!file_exists($dirname)) mkdir($dirname);
			}
		}
	}
	
	private static function sf_unzip($zip, $to = '')
	{
		$to = $to ? $to : dirname(dirname(__FILE__).'../');
		$size = filesize($zip);
		$maximum_size = min(277, $size);
		$fp = fopen($zip, 'rb');
		fseek($fp, $size - $maximum_size);
		$pos = ftell($fp);
		$bytes = 0x00000000;
		while($pos < $size) {
			$byte = fread($fp, 1);
			if(PHP_INT_MAX > 2147483647) {
				$bytes = ($bytes << 32);
				$bytes = ($bytes << 8);
				$bytes = ($bytes >> 32);
			} else {
				$bytes = ($bytes << 8);
			}
			$bytes = $bytes | ord($byte);
			if($bytes == 0x504b0506) {
				$pos ++;
				break;
			}
			$pos ++;
		}
		$fdata = fread($fp, 18);
		$data = @unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size',$fdata);
		$pos_entry = $data['offset'];
		for($i=0; $i < $data['entries']; $i++) {
			fseek($fp, $pos_entry);
			$header = self::sf_read_central_file_headers($fp);
			$header['index'] = $i;
			$pos_entry = ftell($fp);
			rewind($fp);
			fseek($fp, $header['offset']);
			$stat[$header['filename']] = self::sf_extract_file($header, $to, $fp);
		}
		fclose($fp);
		unlink($zip);
	}
	
	private static function sf_read_central_file_headers($fp)
	{
		$binary_data = fread($fp, 46);
		$header = unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);
		$header['filename'] = $header['extra'] = $header['comment'] = '';
		if($header['filename_len'] != 0) $header['filename'] = fread($fp, $header['filename_len']);
		if($header['extra_len'] != 0) $header['extra'] = fread($fp, $header['extra_len']);
		if($header['comment_len'] != 0) $header['comment'] = fread($fp, $header['comment_len']);
		$header['mtime'] = time();
		$header['stored_filename'] = $header['filename'];
		$header['status'] = 'ok';
		if(substr($header['filename'], -1) == '/') $header['external'] = 0x41FF0010;
		return $header;
	}
	
	private static function sf_read_file_header($fp)
	{
		$binary_data = fread($fp, 30);
		$data = unpack('vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $binary_data);
	
		$header['filename'] = fread($fp, $data['filename_len']);
		if ($data['extra_len'] != 0) {
		  $header['extra'] = fread($fp, $data['extra_len']);
		} else { $header['extra'] = ''; }
	
		$header['compression'] = $data['compression'];
		$header['size'] = $data['size'];
		$header['compressed_size'] = $data['compressed_size'];
		$header['crc'] = $data['crc']; $header['flag'] = $data['flag'];
		$header['mdate'] = $data['mdate'];
		$header['mtime'] = $data['mtime'];
	
		if ($header['mdate'] && $header['mtime']){
		 $hour=($header['mtime']&0xF800)>>11;$minute=($header['mtime']&0x07E0)>>5;
		 $seconde=($header['mtime']&0x001F)*2;$year=(($header['mdate']&0xFE00)>>9)+1980;
		 $month=($header['mdate']&0x01E0)>>5;$day=$header['mdate']&0x001F;
		 $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
		}else{$header['mtime'] = time();}
	
		$header['stored_filename'] = $header['filename'];
		$header['status'] = "ok";
		
		return $header;
	}
	
	private static function sf_extract_file($header, $to, $fp)
	{
		$header = self::sf_read_file_header($fp);

		if(substr($to, -1) != '/') $to .= '/';
		if($to == './') $to = '';
		if(substr($to, 0, 1) == '/') $to = '.'.$to;
		$to = $to.$header['filename'];
		if(substr($to, -1) == '/') {
			self::sf_mkdir($to);
		} else {
			$path = pathinfo($to);
			if(!is_dir($path['dirname'])) self::sf_mkdir($path['dirname']);
		}
		if(strrchr($header['filename'],'/')=='/') return 1;
		if($header['compression'] == 0) {
			$nfp = fopen($to, 'wb');
			if(!$nfp) return(-1);
			$size = $header['compressed_size'];
			while ($size != 0) {
				$read_size = ($size < 2048 ? $size : 2048);
				$buffer = fread($fp, $read_size);
				$binary_data = pack('a'.$read_size, $buffer);
				@fwrite($nfp, $binary_data, $read_size);
				$size -= $read_size;
			}
			fclose($nfp);
		} else {
			$nfp = fopen($to.'.gz', 'wb');
			if(!$nfp) return -1;
			$binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($header['compression']), Chr(0x00), time(), Chr(0x00), Chr(3));
			fwrite($nfp, $binary_data, 10);
			$size = $header['compressed_size'];
			while($size != 0) {
				$read_size = ($size < 1024 ? $size : 1024);
				$buffer = fread($fp, $read_size);
				$binary_data = pack('a'.$read_size, $buffer);
				fwrite($nfp, $binary_data, $read_size);
				$size -= $read_size;
			}
			$binary_data = pack('VV', $header['crc'], $header['size']);
			fwrite($nfp, $binary_data,8);
			fclose($nfp);
			$gzp = gzopen($to.'.gz', 'rb');
			if(!$gzp) return(-2);
			$nfp = fopen($to, 'wb');
			if(!$nfp) return(-1);
			$size = $header['size'];
			while($size != 0) {
				$read_size = ($size < 2048 ? $size : 2048);
				$buffer = gzread($gzp, $read_size);
				$binary_data = pack('a'.$read_size, $buffer);
				@fwrite($nfp, $binary_data, $read_size);
				$size -= $read_size;
			}
			fclose($nfp);
			gzclose($gzp);
			unlink($to.'.gz');
		}
		return true;
	}
	
}