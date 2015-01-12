<?php
class Rlog_udp extends Rlog
{
	private $instance = null;

	protected function __construct($conf)
	{
		parent::__construct();

		if (!empty($conf)) {
			$this->_config['UDP_HOST'] = isset($conf['UDP_HOST']) && !empty($conf['UDP_HOST']) ? $conf['UDP_HOST'] : '127.0.0.1';
			$this->_config['UDP_PORT'] = isset($conf['UDP_PORT']) && !empty($conf['UDP_PORT']) ? $conf['UDP_PORT'] : '10001';
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
		$UDP = self::get_instance();

		$response = array();
		$response['type'] = $type;
		$response['level'] = $level;
		$response['message'] = $message;
		$response['context'] = $this->context_format($context);
		$response['add_time'] = time();
		$response = json_encode($response);

		$len = strlen($response);
		fwrite($UDP, $response);
	    fclose($UDP);
	    return true;
	}

	public function get_instance()
	{
		if (!empty($this->instance)) return $this->instance;

		$fp = stream_socket_client("udp://".$this->_config['UDP_HOST'].":".$this->_config['UDP_PORT'], $errno, $errstr);
		if (!$fp) throw new Rlog_exception(sprintf('udp connect error: %s', $errstr));
		
		$this->instance = $fp;
		return $this->instance;
	}
}