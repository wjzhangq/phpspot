<?php
/**
 * session 抽象类
 *
 */

class plugin_sessionAbstract{
	// default config with support for multiple servers
    // (helpful for sharding and replication setups)
    protected $_config = array(
        // cookie related vars
        'cookie_path' => '/',
        'cookie_domain' => '.mydomain.com', // .mydomain.com

        // session related vars
        'lifetime' => 3600, // session lifetime in seconds
		);
		
	public function __construct($config = array()){
		// set object as the save handler
		session_set_save_handler(
		    array(&$this, 'open'),
		    array(&$this, 'close'),
		    array(&$this, 'read'),
		    array(&$this, 'write'),
		    array(&$this, 'destroy'),
		    array(&$this, 'gc')
		);

		// set some important session vars
		ini_set('session.auto_start', 0);
		ini_set('session.gc_probability', 1);
		ini_set('session.gc_divisor', 100);
		ini_set('session.gc_maxlifetime', $this->_config['lifetime']);
		ini_set('session.referer_check', '');
		ini_set('session.entropy_file', '/dev/urandom');
		ini_set('session.entropy_length', 16);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.use_trans_sid', 0);
		ini_set('session.hash_function', 1);
		ini_set('session.hash_bits_per_character', 5);

		// disable client/proxy caching
		session_cache_limiter('nocache');

        // set the cookie parameters
        session_set_cookie_params(
			$this->_config['lifetime'],
			$this->_config['cookie_path'],
			$this->_config['cookie_domain']
		);

	 	// name the session
        session_name('mongo_sess');

        // start it up
        session_start();
	}
}
