<?php
/**
 * Copyright (C) 2010 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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
namespace Framework\Libs;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class MessageException extends \Exception { }

class Message
{	
	public static function add (Session $session, $category, $value)
	{
		if ($session->getNamespace() != 'message')
		{
			throw new MessageException ('Session Namespace is not "message"');
		}
		
		$session[$category] = $value;
	}
	
	public static function get (Session $session, $category)
	{
		if ($session->getNamespace() != 'message')
		{
			throw new MessageException ('Session Namespace is not "message"');
		}
		
		return $session[$category];
	}
	
	public static function clearCategory (Session $session, $category)
	{
		if ($session->getNamespace() != 'message')
		{
			throw new MessageException ('Session Namespace is not "message"');
		}
		
		unset($session[$category]);
	}
	
	public static function clearAll (Session $session)
	{
		if ($session->getNamespace() != 'message')
		{
			throw new MessageException ('hSession Namespace is not "message"');
		}
		
		$session->destroy();
	}
}