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
namespace application\modules\cli;

class CliUtils
{
	/**
	 * Extract request params from the cli
	 * 
	 * @return array
	 */
	public static function extractParams ()
	{
		if ($_SERVER['argc'] === 1)
		{
			return array('action' => 'showDoc', 'params' => array('all'));
		}
		if ($_SERVER['argc'] === 2)
		{
			return array('action' => $_SERVER['argv'][1], 'params' => array());
		}
		
		$params = array('action' => '', 'params' => array());
		for ($i = 1; $i < $_SERVER['argc']; $i++)
		{
			if ($i === 1)
			{
				$params['action'] = $_SERVER['argv'][$i];
			}
			else 
			{
				$params['params'][] = $_SERVER['argv'][$i];
			}
		}
		return $params;
	}
}
