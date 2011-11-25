<?php

/**
 * Copyright (C) 2011 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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

namespace framework\orm\types;

/**
 * Abstract class to be inherited by all the Types.
 *
 * @author mickael
 */
abstract class Type extends \framework\core\FrameworkObject
{
	// Unknown type
	const UNKNOWN = 0;
	
	/** 
	 * NUMERIC TYPES 
	 */
	const INTEGER = 'integer';
	const INT = 'int';
	const TINYINT = 'tinyint';
	const SMALLINT = 'smallint';
	const MEDIUMINT = 'mediumint';
	const BIGINT = 'bigint';
	const FLOAT = 'float';
	const DOUBLE = 'double';
	const LONG = 'long';
	const SHORT = 'short';
	const DECIMAL = 'decimal';
	const REAL = 'real';
	
	/**
	 * TEXTUAL TYPES 
	 */
	const STRING = 'string';
	const TEXT = 'test';
	const MEDIUMTEXT = 'mediumtext';
	const TINYTEXT = 'tynitext';
	const CHAR = 'char';
	const VARCHAR = 'varchar';
	const VARCHAR2 = 'varchar';
	const ENUM = 'enum';
	
	/** 
	 * BOOLEAN TYPES 
	 */
	const BOOL = 'bool';
	const BOOLEAN = 'boolean';
	
	/** 
	 * TIMESTAMP TYPES 
	 */
	const TIMESTAMP = 'timestamp';
	
	/**
	 * All the numeric types that don't need to be quoted.
	 * @var array
	 */
	const NUMERIC_TYPES = array(
		self::INTEGER,
		self::INT,
		self::TINYINT,
		self::SMALLINT,
		self::MEDIUMINT,
		self::BIGINT,
		self::FLOAT,
		self::DOUBLE,
		self::LONG,
		self::SHORT,
		self::DECIMAL,
		self::REAL
	);
	
	/**
	 * All the textual types that should be properly quoted.
	 * @var array
	 */
	const TEXTUAL_TYPES = array(
		self::STRING,
		self::TEXT,
		self::MEDIUMTEXT,
		self::TINYTEXT,
		self::CHAR,
		self::VARCHAR,
		self::VARCHAR2,
		self::ENUM
	);
	
	/**
	 * All the boolean types.
	 * @var array
	 */
	const BOOLEAN_TYPES = array(
		self::BOOL,
		self::BOOLEAN
	);
	
	
	protected $adapter = NULL;
	
	public function __construct(\framework\orm\types\adapters\IAdapter $adapter)
	{
		$this->adapter = $adapter;
	}
	
	/**
	 * Convert a value to a PHP friendly format / type.
	 * @param mixed $value
	 * @return mixed 
	 */
	public function convertToPHP($value)
	{
		return $this->adapter->convertToPHP($value);
	}
	
	/**
	 * Convert a value to a datasource friendly format / type.
	 * @param mixed $value
	 * @return mixed 
	 */
	public function convertToStorage($value)
	{
		return $this->adapter->convertToStorage($value);
	}
	
	/**
	 * Get the type adapter used for the conversions.
	 * @return \framework\orm\types\adapters\IAdapter 
	 */
	public function getAdapter ()
	{
		return $this->adapter;
	}

	/**
	 * Set the type adapter to use for the conversions.
	 * @param \framework\orm\types\adapters\IAdapter $adapter 
	 */
	public function setAdapter (\framework\orm\types\adapters\IAdapter $adapter)
	{
		$this->adapter = $adapter;
	}

	
	public function __destruct()
	{
		$this->adapter = NULL;
	}

}

