<?php
class Rlog_redis extends Rlog
{
	private $instance = null;

	protected function __construct($conf)
	{
		parent::__construct();

		if (!empty($conf)) {
			$this->_config['REDIS_HOST'] = isset($conf['REDIS_HOST']) && !empty($conf['REDIS_HOST']) ? $conf['REDIS_HOST'] : '127.0.0.1';
			$this->_config['REDIS_PORT'] = isset($conf['REDIS_PORT']) && !empty($conf['REDIS_PORT']) ? $conf['REDIS_HOST'] : '6379';
			$this->_config['REDIS_AUTH'] = isset($conf['REDIS_AUTH']) && !empty($conf['REDIS_AUTH']) ? $conf['REDIS_AUTH'] : '';
			$this->_config['REDIS_TIMEOUT'] = isset($conf['REDIS_TIMEOUT']) && !empty($conf['REDIS_TIMEOUT']) ? $conf['REDIS_TIMEOUT'] : 0;
			$this->_config['REDIS_PCONNECT'] = isset($conf['REDIS_PCONNECT']) && strtolower($conf['REDIS_PCONNECT'])==true ? true : false;
			$this->_config['REDIS_DB'] = isset($conf['REDIS_DB']) ? $conf['REDIS_DB'] : 0;
			if (!isset($conf['REDIS_QUEUE_KEY'])) throw new Rlog_exception("redis queue key is not set!");
			$this->_config['REDIS_QUEUE_KEY'] = $conf['REDIS_QUEUE_KEY'];
		}
	}

	protected function context_format($context)
	{
		if (empty($context)) return '';

		if (is_string($context)) {
			return $context;
		} else if (is_object($context)) {
			$arr = $this->object2array($context);
			return $this->array2string($arr);
		} else if (is_bool($context)) {
			return $context ? 'true' : 'false';
		} else {
			return $context;
		}
	}

	/**
	 * [object2array 对象转换数组]
	 * @param  [object] $obj [对象]
	 * @return [array]      [数组]
	 */
	protected function object2array($obj)
	{
	    $_array = is_object($obj) ? get_object_vars($obj) : $obj;

	    foreach ($_array as $key => $value) {
	        $value = (is_array($value) || is_object($value)) ? $this->object2array($value) : $value;
	        $array[$key] = $value;
	    }

	    return $array;
	}

	protected function save($level, $type, $message, $context)
	{
		$Redis = self::get_instance();

		$response = array();
		$response['type'] = $type;
		$response['level'] = $level;
		$response['message'] = $message;
		$response['context'] = $this->context_format($context);
		$response['add_time'] = time();
		$response = json_encode($response);

		return $Redis->lPush($this->_config['REDIS_QUEUE_KEY'], $response);
	}

	public function get_instance()
	{
		if (!empty($this->instance)) return $this->instance;

		try {
			$Redis = new redis();
			if ($this->_config['REDIS_PCONNECT'] == true) {
				$Redis->pconnect($this->_config['REDIS_HOST'], $this->_config['REDIS_PORT'], $this->_config['REDIS_TIMEOUT']);
			} else {
				$Redis->connect($this->_config['REDIS_HOST'], $this->_config['REDIS_PORT'], $this->_config['REDIS_TIMEOUT']);
			}
			if (!empty($this->_config['REDIS_AUTH'])) $Redis->auth($this->_config['REDIS_AUTH']);

			$Redis->select($this->_config['REDIS_DB']);
			
			$this->instance = $Redis;
			return $this->instance;
		}
		catch (Execption $e) {
			echo $e->getMessage();
			exit(0);
		}
	}
}