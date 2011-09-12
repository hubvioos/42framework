<?php 

/* @var $cache \framework\libs\cache\MemcacheEngine */
$cache = $this->getComponent('cache', 'Memcache');
$cache->write('eerre', 'Memcache fonctionne :)');
echo $cache->read('eerre');
$cache->clear(false);
echo $cache->read('eerre');

?>
