<?php
if (!defined('APP_ROOT')){define('APP_ROOT', dirname(__FILE__));}
define('FRAMEWORK', dirname(__FILE__));

class phpspot{
	static var $cfg = array(
		'alias'=>array(),
		);
	
	
	/**
	 * 网站入口
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	function run(){
		$url = !empty($_SERVER['REDIRECT_SCRIPT_URL'])?$_SERVER['REDIRECT_SCRIPT_URL']:(!empty($_SERVER["PATH_INFO"])?$_SERVER["PATH_INFO"]:"");
		
		$real_path = $this->route_page_path();
		if (!is_file($real_path)){
			echo $real_path . 'is not found!';
			return;
		}
		if (substr($real_path, -4) != '.php'){
			readfile($readfile);
		}
		require_once $real_path;
		$class_name = substr($real_path, strlen(APP_ROOT));
		$class_name = str_replace('/', '_', substr($class_name, 0, strlen($class_name) - strlen('.class.php')));
		if (!class_exists($class_name)){
			return;
		}
		$page = new $class_name();
		$page->get();
	}
	
	/**
	 * 虚拟目录
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	function alias($from, $to){
		$from = trim($from, '/');
		$to = trim($to, '/');
		
		$from_len = strlen($from);
		$to_len = strlen($to);
		
		$safe_str = 'abcdefghijklmnopqrstuvwxyz0123456789/.';
		if ($from_len == 0 || $from_len != strspn($from, $safe_str)){
			trigger_error(sprintf('"%s" is invalid charset!', $from));
		}
		if ($to_len || $to_len != strspn($from, $safe_str)){
			trigger_error(sprintf('"%s" is invalid charset!', $to));
		}
		
		$path = APP_ROOT . $to;
		if (is_dir($path)){
			self::$cfg['alias'][] = array('alias'=> '/' . $from . '/', 'real'=> '/' . $to  . '/', 'type'=>'dir');
		}elseif(is_file($path)){
			self::$cfg['alias'][] = array('alias'=> '/' . $from , 'real'=> '/' . $to , 'type'=>'file');
		}else{
			trigger_error(sprintf('"%s" is invalid dir or file!', $to));
		}
	}
	
	function route_page_path($request_path){
		$real_path = $request_path;
		//check alias
		foreach(self::$cfg['alias'] as $v){
			$alias_len = strlen($v['alias']);
			if (strncmp($request_path, $v['alias'], $alias_len) == 0){
				//命中
				$real_path = $v['real'] . substr($request_path, $alias_len);
				if ($v['type'] == 'file'){
					return APP_ROOT . $real_path;
				}
				break;
			}
		}
		
		return APP_ROOT . $real_path . '.class.php';
	}
}

?>