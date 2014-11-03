<?php
class Rlog_file extends Rlog
{
	protected function __construct($conf)
	{
		parent::__construct();

		$this->_config = $conf;
	}

	protected function date_format()
	{
		return '['.date("Y-m-d H:s:i").']';
	}

	protected function context_format($context)
	{
		if (empty($context)) return '';

		if (is_string($context)) {
			return $context;
		} else if (is_array($context)) {
			return $this->array2string($context);
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

	/**
	 * [array2string 数组转换成字符串]
	 * @param  [array] $context [数组]
	 * @return [string]          [字符串]
	 */
	private function array2string($context)
    {
        $export = '';
        foreach ($context as $key => $value) {
            $export .= "{$key}: ";
            $export .= preg_replace(array(
                '/=>\s+([a-zA-Z])/im',
                '/array\(\s+\)/im',
                '/^  |\G  /m',
            ), array(
                '=> $1',
                'array()',
                '    ',
            ), str_replace('array (', 'array(', var_export($value, true)));
            $export .= PHP_EOL;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    /**
     * [check_dir 检测目录权限或生成目录]
     * @param  [string] $dir [目录]
     * @return [bool]      [是否校验成功]
     */
	protected function check_dir($dir)
	{
		if (is_dir($dir)) {
			if (!is_writable($dir)) throw new Rlog_exception('mkdir ['.$dir.'] is not writable.');
		} else {
			if (!mkdir($dir, '0777', true)) throw new Rlog_exception('mkdir ['.$dir.'] create faild.');
		}
		return true;
	}

	protected function save($level, $type, $message, $context)
	{

		// 判断是否需要多个log文件
		if ($this->_config['LOG_MULTI']) {
			$file_path = $this->_config['LOG_PATH'].DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.date('Ym').DIRECTORY_SEPARATOR.date("d");
		} else {
			$file_path = $this->_config['LOG_PATH'].DIRECTORY_SEPARATOR.$type;
		}
		$this->check_dir($file_path);
		$file = $file_path.DIRECTORY_SEPARATOR.'log_'.date('Ymd').'.log';

		$date = $this->date_format();
		$context = $this->context_format($context);
		$msg = $date.' ['.$level.'] '.$message.PHP_EOL;
		if ($context != '') $msg .= $context.PHP_EOL;

		file_put_contents($file, $msg, FILE_APPEND);
	}
}