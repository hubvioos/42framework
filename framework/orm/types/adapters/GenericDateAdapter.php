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
 * GenericDateAdapter
 * Handles the convertion from "generic types" (i.e. string, int) to \DateTime object
 */
abstract class GenericDateAdapter implements IAdapter
{
    public function __construct ()
    {

    }

    /**
     * Try to convert a string to a \DateTime object
     * @param string $value
     * @return \DateTime
     * @throws AdapterException
     */
    protected function stringToDate($value)
    {
        if(\preg_match("#^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}:[0-9]+$#", $value))
        {
            return \DateTime::createFromFormat('Y-m-d H:i:s:u', $value);
        }

        if(\preg_match("#^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$#", $value))
        {
            return \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        }

        if(\preg_match("#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#", $value))
        {
            return \DateTime::createFromFormat('Y-m-d', $value);
        }

        if(\preg_match('#^[0-9]{14}$#', $value))
        {
            return \DateTime::createFromFormat('YmdHis', $value);
        }

        if(\preg_match('#^[0-9]{12}$#', $value))
        {
            return \DateTime::createFromFormat('ymdHis', $value);
        }

        if(\preg_match('#^[0-9]{8}$#', $value))
        {
            return \DateTime::createFromFormat('Ymd', $value);
        }

        if(\preg_match('#^[0-9]{6}$#', $value))
        {
            return \DateTime::createFromFormat('ymd', $value);
        }

        try
        {
            return \DateTime::createFromFormat('U', $value);
        }
        catch(\Exception $e)
        {
            try
            {
                $d = new \DateTime();
                $d->setTimestamp(\strtotime($value));

                return $d;
            }
            catch(\Exception $ex)
            {
                throw new AdapterException('Unable to convert value '.$value.' to PHP DateTime');
            }
        }
    }

    public function convertToPHP ($value)
    {
        if($value instanceof \DateTime)
        {
            return $value;
        }
        elseif(\is_string($value))
        {
            return $this->stringToDate($value);
        }
        else
        {
            try
            {
                $date = new \DateTime();
                $date->setTimestamp($value);

                return $date;
            }
            catch(\Exception $e)
            {
                throw new \framework\orm\types\adapters\AdapterException('Invalid timestamp '.$value.'.');
            }
        }
    }


}