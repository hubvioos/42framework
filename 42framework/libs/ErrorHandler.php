<?php
namespace framework\libs;

class ErrorHandler extends \Exception
{
	
	const MAX_STRING_LEN = 16;
	
	protected static $logMode;
	
	public function __construct($display_error, $error_reporting, $logMode = 'default') {
		error_reporting($error_reporting);
		ini_set('display_errors', $display_error);
		set_error_handler(array($this, 'handleError'));
		set_exception_handler(array($this, 'handleException'));
		self::$logMode = $logMode;
	}
	
	public static function handleError($no, $str, $file, $line, $context)
	{	
		$lines[] = '<strong>Erreur avec le message suivant :</strong><br />';
		$lines[] = $str . '<br />';
		$lines[] = 'dans le fichier <em>' . $file . '</em> à la ligne <em>' . $line . '</em><br />';

		$stack = debug_backtrace();

		array_shift($stack);
		array_shift($stack);

		$lines = array_merge($lines, self::formatTrace($stack));

		$lines = array_merge($lines, self::codeSample($file, $line));

		$rc = implode('<br />', $lines).'<br />';
		
		self::logError($rc);
		
		if(ini_get('display_errors'))
		{
			if (!headers_sent())
			{
				header('HTTP/1.0 500 Erreur avec le message suivant : '.strip_tags($str));
			}

			echo '<br />'.$rc;
		}
		else
		{
			if($no != E_NOTICE || $no != E_USER_NOTICE)
			{
				echo 'Il semblerait qu\'une erreur soit survenue. Un administrateur a été prévenu.<br />';
			}
		}
	}

	public static function handleException($exception)
	{
		if (!headers_sent())
		{
			header('HTTP/1.0 500 Exception avec le message suivant : '.strip_tags($exception->getMessage()));
		}

		echo $exception;

		exit;
	}
	
	public static function logError($message) {
		switch(self::$logMode)
		{
			case 'email':
				error_log($message, 1, Registry::get('logEmail'));
				break;
			
			case 'file':
				error_log($message, 3, Registry::get('logFile'));
				break;
			
			case 'all':
				error_log($message, 1, Registry::get('logEmail'));
				error_log($message, 3, Registry::get('logFile'));
				break;
				
			case 'none':
				break;
			
			case 'default':
			default:
				error_log($message, 3, APP.DS.'logs'.DS.'error.log');
		}
	}

	public static function formatTrace($stack)
	{
		$lines = array();

		if (!$stack)
		{
			return $lines;
		}

		$root = str_replace('\\', '/', realpath('.'));

		$lines[] = '<strong>Stack trace:</strong><br />';

		foreach ($stack as $i => $node)
		{
			$trace_file = null;
			$trace_line = 0;
			$trace_class = null;
			$trace_type = null;
			$trace_args = null;

			extract($node, EXTR_PREFIX_ALL, 'trace');

			if ($trace_file)
			{
				$trace_file = str_replace('\\', '/', $trace_file);
				$trace_file = str_replace($root, '', $trace_file);
			}

			$params = array();

			if ($trace_args)
			{
				foreach ($trace_args as $arg)
				{
					switch (gettype($arg))
					{
						case 'array': $arg = 'Array'; break;
						case 'object': $arg = 'Object of ' . get_class($arg); break;
						case 'resource': $arg = 'Resource of type ' . get_resource_type($arg); break;

						default:
						{
							if (strlen($arg) > self::MAX_STRING_LEN)
							{
								$arg = substr($arg, 0, self::MAX_STRING_LEN) . '...';
							}
						}
						break;
					}

					$params[] = $arg;
				}
			}

			$lines[] = sprintf
			(
				'#%02d &mdash; %s(%d): %s%s%s(%s)',

				$i, $trace_file, $trace_line, $trace_class, $trace_type,
				$trace_function, htmlentities(join(', ', $params))
			);
		}

		return $lines;
	}

	public static function codeSample($file, $line)
	{
		$lines =  array
		(
			'',
			'<strong>Code sample:</strong>',
			''
		);

		$fh = fopen($file, 'r');

		$i = 0;
		$start = $line - 5;
		$stop = $line + 5;

		while ($str = fgets($fh))
		{
			$i++;

			if ($i > $start)
			{
				$str = htmlentities(rtrim($str));

				if ($i == $line)
				{
					$str = '<ins>' . $str . '</ins>';
				}

				$str = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $str);

				$lines[] = $str;
			}

			if ($i > $stop)
			{
				break;
			}
		}

		fclose($fh);

		return $lines;
	}
}
?>