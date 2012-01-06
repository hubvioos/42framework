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

/**
 * Class Model.
 * Base class that every model can extend. It extends the \framework\core\FrameworkObject class 
 * to provide access to convenient mthods like $this->getComponent(), $this->getConfig(), ... ; 
 * and also implements the \framework\orm\models\IAttachableModel interface so it can be
 * managed by a \framework\orm\mappers\Mapper.
 *
 * @author mickael
 */

namespace framework\libs;

abstract class Model extends \framework\core\FrameworkObject implements \framework\orm\models\IAttachableModel
{
	
}