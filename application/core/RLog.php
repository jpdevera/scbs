<?php

/**
 * A simple standalone logging facility
 *
 * Usage:
 * 	RLog::write('<content>', RLog::<LEVEL>, <FILE>, <LINE>);
 * 	RLog::debug('<content>');
 * 	RLog::info('<content>');
 * 	RLog::notice('<content>');
 * 	RLog::warning('<content>');
 * 	RLog::error('<content>');
 * 	RLog::critical('<content>');
 * 	RLog::alert('<content>');
 * 	RLog::emergency('<content>');
 *
 * To change the location of logs:
 * 	RLog::location('/path/to/log/files', <mode=0777>);
 *
 * To switch on and off:
 * 	RLog::enable(<TRUE / FALSE>);
 *
 */

class RLog
{

	/**
	 * Log Levels
	 * 
	 * LOG_EMERG; // Emergencies - system is unusable.
	 * LOG_ALERT; // Action must be taken immediately.
	 * LOG_CRIT; // Critical Conditions.
	 * LOG_ERR; // Error conditions.
	 * LOG_WARNING; // Warning conditions.
	 * LOG_NOTICE; // Normal but significant condition.
	 * LOG_INFO; // Informational.
	 * LOG_DEBUG; // Debug-level messages
	 */

	/**
	 * Log Level Labels
	 */
	const LOG_LABEL_EMERG = 'EMERGENCY';
	const LOG_LABEL_ALERT = 'ALERT';
	const LOG_LABEL_CRIT = 'CRITICAL';
	const LOG_LABEL_ERR = 'ERROR';
	const LOG_LABEL_WARNING = 'WARNING';
	const LOG_LABEL_NOTICE = 'NOTICE';
	const LOG_LABEL_INFO = 'INFO';
	const LOG_LABEL_DEBUG = 'DEBUG';

	/**
	 * To determine if writing of log will be made. Disabled by default.
	 */
	private static $_enabled = FALSE;
	
	/**
	 * Allowed level to print
	 */
	private static $_level = LOG_WARNING;

	/**
	 * Location of the logs
	 */
	private static $_directory = '.';

	/**
	 * Permission of the location
	 */
	private static $_mode = 0777;

	/**
	 * Setter and Getter of the location
	 *
	 * @param string $directory
	 * @param int $mode
	 * @return int or void
	 */
	public static function location($directory = NULL, $mode = NULL)
	{
		if (is_null($directory)) return self::$_directory;

		self::$_directory = $directory;
		self::mode($mode);
	}

	/**
	 * Setter and getter of mode
	 *
	 * @param int $mode
	 * @return int or void
	 */
	public static function mode($mode = NULL)
	{
		if (is_null($mode)) return self::$_mode;

		self::$_mode = $mode;
	}
	
	/**
	 * Sets the allowed level to print
	 * 
	 * @param int $level
	 * @return void
	 */
	public static function level($level = LOG_WARNING)
	{
		self::$_level = $level;
	}

	/**
	 * Turn the logging on/off and/or make RLog as the Error Handler
	 *
	 * @param boolean $enable
	 * @return void
	 */
	public static function enable($enable = FALSE)
	{
		self::$_enabled = $enable;
	}
	
	/**
	 * Make/unmake RLog as the Error Handler
	 * 
	 * Note: RLog must be enabled before setting this on.
	 * 
	 * @param boolean $enable
	 * @return void
	 */
	public static function setErrorHandler($enable = TRUE)
	{
		if (!self::$_enabled) return;
		if (!is_writable(self::$_directory)) return;
		
		if ($enable)
		{
			register_shutdown_function(sprintf('%s::%s', get_class(), 'shutdownHandler'));
			set_error_handler(sprintf('%s::%s', get_class(), 'errorHandler'));
			set_exception_handler(sprintf('%s::%s', get_class(), 'exceptionHandler'));
		}
		else
		{
			restore_error_handler();
			restore_exception_handler();
		}
	}
	
	/**
	 * Shutdown Handler
	 * 
	 * @return void
	 */
	public static function shutdownHandler()
	{
		$error = error_get_last();
		if (!empty($error)) static::write($error['message'], LOG_CRIT, $error['file'], $error['line']);
	}
	
	/**
	 * Error Handler
	 * 
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 * @return void
	 */
	public static function errorHandler($errno, $error, $file, $line)
	{
		switch ($errno)
		{
			case E_NOTICE:
			case E_USER_NOTICE:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
			case E_STRICT:
				static::write($error, LOG_NOTICE, $file, $line);
				break;	
			case E_WARNING:
			case E_USER_WARNING:
				static::write($error, LOG_WARNING, $file, $line);
				break;	
			case E_ERROR:
			case E_USER_ERROR:
				static::write($error, LOG_ERR, $file, $line);
				exit("FATAL error $error at $file:$line");	
			default:
				static::write($error, LOG_CRIT, $file, $line);
				exit("Unknown error at $file:$line");
		}
	}
	
	/**
	 * Exception Handler
	 * 
	 * @param object $exception
	 * @return void
	 */
	public static function exceptionHandler($exception)
	{
		$level = LOG_ERR;
		$content = sprintf("Uncaught exception: [%s] %s\n%s", $exception->getCode(), $exception->getMessage(), $exception->getTraceAsString());
		$content = static::_generate(
			$content,
			$level,
			$exception->getFile(),
			$exception->getLine()
		);
		self::_write_to_file($content);
	}

	/**
	 * Writes the content with EMERGENCY level
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	public static function emergency($content)
	{
		return self::write($content, LOG_EMERG);
	}

	/**
	 * Writes the content with ALERT level
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	public static function alert($content)
	{
		return self::write($content, LOG_ALERT);
	}

	/**
	 * Writes the content with CRITICAL level
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	public static function critical($content)
	{
		return self::write($content, LOG_CRIT);
	}

	/**
	 * Writes the content with ERROR level
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	public static function error($content)
	{
		return self::write($content, LOG_ERR);
	}

	/**
	 * Writes the content with WARNING level
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	public static function warning($content)
	{
		return self::write($content, LOG_WARNING);
	}

	/**
	 * Writes the content with NOTICE level
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	public static function notice($content)
	{
		return self::write($content, LOG_NOTICE);
	}

	/**
	 * Writes the content with INFO level
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	public static function info($content)
	{
		return self::write($content, LOG_INFO);
	}

	/**
	 * Writes the content with DEBUG level
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	public static function debug($content)
	{
		return self::write($content, LOG_DEBUG);
	}

	/**
	 * Writes the content with any level
	 *
	 * @param mixed $content
	 * @param int $level
	 * @param string $file
	 * @param string $line
	 * @return boolean
	 */
	public static function write($content, $level = NULL, $file = NULL, $line = NULL)
	{
		if (!self::$_enabled) return FALSE;
		if (self::$_level < $level) return FALSE;
		
		$trace = self::_trace();
		$content = static::_generate(
			$content,
			$level,
			(is_null($file)) ? $trace['file'] : $file,
			(is_null($line)) ? $trace['line'] : $line,
			$trace['class'],
			$trace['type'],
			$trace['function']
		);
		
		return self::_write_to_file($content);
	}
	
	/**
	 * Log content generation
	 *
	 * @param mixed $content
	 * @param int $level
	 * @param string $file
	 * @param int $line
	 * @param string $class
	 * @param string $type
	 * @param string $function
	 * @return boolean
	 */
	private static function _generate($content, $level, $file, $line, $class = NULL, $type = NULL, $function = NULL)
	{
		return sprintf(
			"[%s] [%s] [%s%s%s] -> %s, FILE: %s, LINE: %d\n",
			self::_date(),
			self::_log_label($level),
			$class,
			$type,
			$function,
			self::_to_string($content),
			$file,
			$line
		);
	}

	/**
	 * Gets the Level in text format
	 *
	 * @param int $level
	 * @return string
	 */
	private static function _log_label($level)
	{
		switch ($level)
		{
			case LOG_EMERG:
				return self::LOG_LABEL_EMERG;
			case LOG_ALERT:
				return self::LOG_LABEL_ALERT;
			case LOG_CRIT:
				return self::LOG_LABEL_CRIT;
			case LOG_ERR:
				return self::LOG_LABEL_ERR;
			case LOG_WARNING:
				return self::LOG_LABEL_WARNING;
			case LOG_NOTICE:
				return self::LOG_LABEL_NOTICE;
			case LOG_INFO:
				return self::LOG_LABEL_INFO;
			case LOG_DEBUG:
			default:
				return self::LOG_LABEL_DEBUG;
		}
	}

	/**
	 * Trace where the log is called
	 *
	 * @return array
	 */
	private static function _trace()
	{
		$bt = debug_backtrace();
		$t = array(
			'file' => (!empty($bt[3]['file'])) ? $bt[3]['file'] : '',
			'line' => (!empty($bt[3]['file'])) ? $bt[3]['line'] : '',
			'class' => (!empty($bt[3]['class'])) ? $bt[3]['class'] : '',
			'function' => (!empty($bt[3]['function'])) ? $bt[3]['function'] : '',
			'type' => (!empty($bt[3]['type'])) ? $bt[3]['type'] : '',
		);
		
		return $t;
	}
	
	/**
	 * Writes the log into the file
	 *
	 * @param mixed $content
	 * @return boolean
	 */
	private static function _write_to_file($content)
	{
		if (!file_exists(self::$_directory)) mkdir(self::$_directory, self::$_mode, TRUE);
		
		$log = self::_path();
		$success = FALSE;
		
		if (is_writable(self::$_directory))
		{
			$fh = @fopen($log, 'a');
			
			if ($fh !== FALSE)
			{
				$content = self::_to_string($content);
				$success = (fwrite($fh, $content) !== FALSE);
				fclose($fh);
			}
		}
		
		return $success;
	}

	/**
	 * Generate path of the log file
	 *
	 * @return string
	 */
	private static function _date()
	{
		return date('D M d H:i:s Y');
	}

	/**
	 * Format content into a string
	 *
	 * @param mixed $content
	 * @return string
	 */
	private static function _to_string($content)
	{
		return (is_string($content)) ? $content : var_export($content, TRUE);
	}

	/**
	 * Generate path of the log file
	 *
	 * @return string
	 */
	private static function _path()
	{
		return self::$_directory . DIRECTORY_SEPARATOR . 'log_' . date('Y-m-d') . '.txt';
	}

}
