<?php
/**
 * Rlog 日志系统
 * @author  codening@163.com
 * @date    2014-11-03
 */
include_once('Lib/Rlog_exception.php');
include_once('Lib/RLog_level.php');
abstract class Rlog
{
	protected static $_instances = array();
	protected $_config = array();

	protected function __construct($conf=array())
	{
		$this->_config = $conf;
	}

	public function get_config()
	{
		return $this->_config;
	}

	/**
	 * [instance 实例化日志类]
	 * @param  array  $conf 配置文件，设置日志驱动等
	 * @return object       日志类对象
	 */
	public static function instance($conf=array())
	{
		if (empty($conf)) $conf = include_once('Conf'.DIRECTORY_SEPARATOR.'file.php');

		if (!empty(self::$_instances[$conf['DRIVER']])) {
			return self::$_instances[$conf['DRIVER']];
		} else {
			$driver_file = __DIR__.DIRECTORY_SEPARATOR.'Lib'.DIRECTORY_SEPARATOR.'Driver'.DIRECTORY_SEPARATOR.$conf['DRIVER'].'.php';
			if (!is_file($driver_file)) throw new Rlog_exception('Rlog '.$conf['DRIVER'].' Driver is not found!');
			include_once($driver_file);
			$_class = 'Rlog_'.$conf['DRIVER'];
			return self::$_instances[$conf['DRIVER']] = new $_class($conf);
		}
	}

	public function emerg($type, $message, $context='')
	{
		$this->save(RLog_level::EMERG, $type, $message, $context);
	}

	public function alert($type, $message, $context='')
	{
		$this->save(RLog_level::ALERT, $type, $message, $context);
	}

	public function crit($type, $message, $context='')
	{
		$this->save(RLog_level::CRIT, $type, $message, $context);
	}

	public function error($type, $message, $context='')
	{
		$this->save(RLog_level::ERROR, $type, $message, $context);
	}

	public function warn($type, $message, $context='')
	{
		$this->save(RLog_level::WARN, $type, $message, $context);
	}

	public function notice($type, $message, $context='')
	{
		$this->save(RLog_level::NOTICE, $type, $message, $context);
	}

	public function info($type, $message, $context='')
	{
		$this->save(RLog_level::INFO, $type, $message, $context);
	}

	public function debug($type, $message, $context='')
	{
		$this->save(RLog_level::DEBUG, $type, $message, $context);
	}

	public function __call($method, $args)
	{
		throw new Rlog_exception(sprintf('method not allowed: %s', $method));
	}

	abstract protected function save($level, $type, $message, $context);
}