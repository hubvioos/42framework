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

namespace framework\orm\types\adapters;

/**
 * Description of MongoDBDateAdapter
 *
 * @author mickael
 */
class MongoDBDateAdapter extends GenericDateAdapter
{
    public function __construct ()
    {

    }


    public function convertToPHP ($value)
    {
        if($value instanceof \MongoDate)
        {
            return \DateTime::createFromFormat('U', $value->sec);
        }
        else
        {
            return parent::convertToPHP($value);
        }
    }

    public function convertToStorage ($value)
    {
        if($value instanceof \DateTime)
        {
            return new \MongoDate($value->getTimestamp());
        }
        else
        {
            $date = \strtotime($value);

            if($date !== false)
            {
                return new \MongoDate($date);
            }
        }

        throw new \framework\orm\types\adapters\AdapterException('Unable to convert value to MongoDBDate.');
    }

}
