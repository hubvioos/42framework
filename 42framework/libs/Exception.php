<?php
namespace framework\libs;

class Exception extends \Exception
{
	public function __construct($message, $code = null)
	{
		if ($code)
		{
			header('HTTP/1.0 '.$code.' '.strip_tags($message));
		}

		parent::__construct($message);
	}

	public function __toString()
	{
		$lines = array();

		$lines[] = '<strong>Exception avec le message suivant :</strong><br />';
		$lines[] = $this->getMessage().'<br />';
		$lines[] = 'dans le fichier <em>'.$this->getFile().'</em> à la ligne <em>'.$this->getLine().'</em><br />';

		$stack = $this->getTrace();

		$lines = array_merge($lines, ErrorHandler::formatTrace($stack));

		$rc = implode('<br />' . PHP_EOL, $lines);

		return $rc;
	}
}
?>