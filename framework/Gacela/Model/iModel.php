<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Model;

interface iModel {

	public function save($data = null);

	public function validate(array $data = null);
}
