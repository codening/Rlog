<?php
/**
 * RLog_level
 */
class RLog_level
{
	const EMERG  = 'EMERG';// 严重错误: 导致系统崩溃无法使用
	const ALERT  = 'ALERT';// 警戒性错误: 必须被立即修改的错误
	const CRIT   = 'CRIT';// 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
	const ERROR  = 'ERROR';// 一般错误: 一般性错误
	const WARN   = 'WARN';// 警告性错误: 需要发出警告的错误
	const NOTICE = 'NOTICE';// 通知: 程序可以运行但是还不够完美的错误
	const INFO   = 'INFO';// 信息: 程序输出信息
	const DEBUG  = 'DEBUG';// 调试: 调试信息
}