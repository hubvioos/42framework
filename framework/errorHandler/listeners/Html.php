<?php 
/**
 * Copyright (C) 2011 - K√©vin O'NEILL, Fran√ßois KLINGLER - <contact@42framework.com>
 * 
 * 42framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * 42framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */
namespace framework\errorHandler\listeners;

class Html extends \framework\core\FrameworkObject implements \framework\errorHandler\interfaces\iErrorHandlerListener
{
	const MAX_STRING_LEN = 16;
	
	/**
	 * @param \framework\errorHandler\ErrorHandler $errorHandler
	 */
	public function update (\framework\errorHandler\ErrorHandler $errorHandler)
	{
		$e = $errorHandler->getLastError();
		
		$lines = array();
		
		$lines[] = '<strong>'.get_class($e).' with the following message :</strong><br />';
		$lines[] = $e->getMessage().'<br />';
		$lines[] = 'in file <em>'.$e->getFile().'</em> on line <em>'.$e->getLine().'</em><br />';
		
		$stack = $e->getTrace();
		$code = $this->codeSample($e->getFile(), $e->getLine());
		
		$lines = array_merge($lines, $this->formatTrace($stack), $code);
		
		$rc = implode('<br />'.PHP_EOL, $lines);
		
		$response = $this->getContainer()->getHttpResponse()->status(500);
			
		if (ini_get('display_errors'))
		{
			$response->set($rc);
		}
		else
		{
			$response->set('Oops! An error occured!');
		}
		$this->viewSetGlobal('layout', false);
		$response->stopProcess();
	}
	
	/**
	 * @param array $stack
	 * @return array
	 */
	public function formatTrace ($stack)
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
			$trace_function = null;
			
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
						case 'array':
							$arg = 'Array';
							break;
						case 'object':
							$arg = 'Object of ' . get_class($arg);
							break;
						case 'resource':
							$arg = 'Resource of type ' . get_resource_type($arg);
							break;
						
						default:
							{
								if (strlen($arg) > self::MAX_STRING_LEN)
								{
									$arg = substr($arg, 0, self::MAX_STRING_LEN) .
										 '...';
								}
							}
							break;
					}
					
					$params[] = $arg;
				}
			}
			
			$lines[] = sprintf('#%02d &mdash; %s(%d): %s%s%s(%s)', 
				$i, $trace_file, $trace_line, $trace_class, $trace_type, $trace_function, 
				htmlentities(join(', ', $params)));
		}
		
		return $lines;
	}
	
	/**
	 * @param string $file
	 * @param integer $line
	 * @return array
	 */
	public function codeSample ($file, $line)
	{
		$lines = array('', '<strong>Code sample:</strong>', '');
		
		$fh = fopen($file, 'r');
		
		$i = 0;
		$start = $line - 5;
		$stop = $line + 5;
		
		$str = fgets($fh);
		while ($str)
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
			$str = fgets($fh);
		}
		
		fclose($fh);
		
		return $lines;
	}
}