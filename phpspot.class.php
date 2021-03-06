<?php

/* phpspot 转发路由 */
class phpspot{
	static $cfg = array(
		'default_index' => 'index', //默认执行文件
		'default_page_dir' => 'page',
		'script_suffix'=> '.php', //执行文件后缀
		'request_suffix' => array('html', 'htm', 'json'),
		'alias'=>array(),
		'rewrite'=>array('pattern'=>array(), 'replacement'=>array()),
		);
	var $app_dir = '';
	
	public function set_app_dir($dir){
		if (!is_dir($dir)){
			trigger_error(sprintf('dir "%s" is exist!', $dir));
			return;
		}
		$dir = rtrim(realpath($dir), '/');
		$this->app_dir = $dir;
	}
	
	public function get_request_path(){
	    return page_base::get_request_path();
	}
	
	
	
	/**
	 * 网站入口
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	function run($request_path=null){
	    if (empty($request_path)){
	        $request_path = $this->get_request_path();
	    }
		
		list($request_path, $suffix) = $this->split_suffix($request_path);
		$real_path = $this->route_page_path($request_path);
		if (!is_file($real_path)){
			page_base::page_404(sprintf('"%s" in not found!', $real_path));
			return;
		}
		
		if (substr($real_path, - strlen(self::$cfg['script_suffix'])) != self::$cfg['script_suffix']){
			$this->send_file($real_path);
			return;
		}
		
		require_once $real_path;
		$class_name = $this->path2classname($real_path);
		if (!class_exists($class_name)){
			page_base::page_error(sprintf('class "%s" is not found in "%s"', $class_name, $real_path));
			return;
		}
		$page = __new__($class_name);
		$page->suffix = $suffix;
		$page->run();
	}
	

	/**
	 * 路由定向
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	function route_page_path($request_path){
		$request_path = trim($request_path, '/');
		
		$real_path = $request_path ?  $this->app_dir . '/' .self::$cfg['default_page_dir']. '/' . $request_path : $this->app_dir . '/'. self::$cfg['default_page_dir'];
		if (is_dir($real_path)){
			$real_path .= '/' . self::$cfg['default_index'] . '.class.php';
		}else{
			$real_path .= '.class.php';
		}
		return $real_path;
	}
	
	/**
	 * 由路径反推类名
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	function path2classname($path){
		$class_name = trim(substr($path, strlen($this->app_dir)), '/');
		$class_name = str_replace('/', '_', substr($class_name, 0, strlen($class_name) - strlen('.class.php')));
		return $class_name;
	}
	
	/**
	 * 发送非执行文件
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	function send_file($path){
		readfile($path);
	}
	
	/**
	 * split 出有效的suffix
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	
	function split_suffix($request_path){
		$suffix = '';
		if ($request_path){
			$pos = strrpos($request_path, '.');
			if ($pos !== false){
				$suffix = substr($request_path, $pos+1);
				if (in_array($suffix, self::$cfg['request_suffix'])){
					$request_path = substr($request_path, 0, $pos);
				}else{
					$suffix = '';
				}
			}
		}
		
		return array($request_path, $suffix);
	}
}

/**
 * page_bash 类, 负责参数传递, 和返回
 **/
class page_base{
	var $suffix = '';
	
	/**
	 * page 类入口
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	
	function run(){
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		if (!method_exists($this, $method)){
			$this->page_403(sprintf('method "%s" is not allow!', $method));
			return;
		}
		$this->$method();
	}
	
	/**
	 * get 方法虚函数
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	function get(){
		$this->page_403('function get not found'); //forbidden
	}
	
	/**
	 * post 方法虚函数
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/	
	function post(){
		$this->page_403('function get not found'); //forbidden
	}
	
	static function page_404($msg=null){
		header('HTTP/1.1 404 Not Found', true, 404);
		if (!empty($msg)){
			echo '<h2>' . $msg . '</h2>';
		}
	}
	
	static function page_403($msg=null){
		header('HTTP/1.1 403 Forbidden', true, 403);
		if (!empty($msg)){
			echo '<h2>' . $msg . '</h2>';
		}
	}
	
	/**
	 *  重定向
	 * @param
	 * @return
	 * @author zhangwenjin
	 **/
	function page_302( $url = '')
	{
		if ( $url == '' ) {
			$url = $this->get_request_path();
		}
		header( 'Location: ' . $url, true, 302 );
	}
	
	function page_error($msg){
		echo '<h2>' . $msg . '</h2>';
	}
	
	static function get_request_path()
    {
		$request_path = !empty($_SERVER['REDIRECT_SCRIPT_URL'])?$_SERVER['REDIRECT_SCRIPT_URL']:(!empty($_SERVER["PATH_INFO"])?$_SERVER["PATH_INFO"]:"");
		//兼容nginx
		if (empty($request_path) || $request_path == '/'){
			if (!empty($_GET['__path__'])){
				$request_path = $_GET['__path__'];
			}
		}
		
		return $request_path;
    }
}


/**
 * 创建一个单例函数
 * @param
 * @return
 * @author zhangwenjin
 **/
function __new__($class_name){
	static $obj_pool = array();
	
	$argv = func_get_args();
	$class_name = array_shift($argv);
	if (empty($argv)){
		//不带参数
		$key = $class_name;
	}else{
		$key = $class_name . '_' . md5(var_export($argv, true));
	}
	
	if (!isset($obj_pool[$key])){
		if (!class_exists($class_name)){
			$path = str_replace('_', '/', $class_name) . '.class.php';
			if (is_file($path)){
				require_once $path;
			}else{
				trigger_error(sprintf('"%s" can\'t found, please require it first!', $class_name));
			}
		}
		
		if (empty($argv)){
			$obj_pool[$key] = new $class_name();
		}else{
			$reflection = new ReflectionClass($class_name);
	        $obj_pool[$key] = call_user_func_array(array($reflection, 'newInstance'), $argv);
		}
	}
	
	return $obj_pool[$key];
}
?>