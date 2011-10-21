<?php
/** 
 * @author Noah Goodrich
 * @date May 7, 2011
 * @brief
 * 
*/

namespace Gacela\DataSource;

abstract class DataSource extends \framework\core\FrameworkObject implements iDataSource {

	protected $_config = array();

	protected $_resources = array();

	abstract protected function _driver();

	protected function _cache($name, $key, $data = null)
	{
		$instance = $this->_singleton();

		$version = $instance->cache($name . '_version');

		if (is_null($version) || $version === false) {
			$version = 0;
			$instance->cache($name . '_version', $version);
		}

		$key = 'query_' . $version . '_' . $key;

		$cached = $instance->cache($key);

		if (is_null($data)) {
			return $cached;
		}

		if ($cached === false) {
			$instance->cache($key, $data);
		} else {
			$instance->cache($key, $data, true);
		}
	}

	protected function _incrementCache($name)
	{
		$instance = $this->_singleton();

		$cached = $instance->cache($name.'_version');

		if($cached === false) {
			return;
		}

		$instance->incrementCache($name.'_version');
	}

	protected function _singleton()
	{
		return $this->getComponent('gacela');
	}

	public function beginTransaction()
	{
		return false;
	}

	public function commitTransaction()
	{
		return false;
	}

	/**
	 * @see \Gacela\DataSource\iDataSource::loadResource()
	 */
	public function loadResource($name)
	{
		$cached = $this->_singleton()->cache('resource_'.$name);

		if($cached === false || is_null($cached))  {
			$cached = new Resource($this->_driver()->load($this->_conn, $name, $this->_config->schema));

			$this->_singleton()->cache('resource_'.$name, $cached);
		}
		
		return $cached;
	}

	public function rollbackTransaction()
	{
		return false;
	}
}

