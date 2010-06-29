<?php
namespace app\modules;

class globals extends \framework\libs\Controller
{
	//protected $useModels = array('Article');
	
	public function index()
	{
		//$this->Article->sayItWorks();
		//\framework\libs\Core::loadPlugin('test');
		$this->set('text', 'It works !');
		$this->set('pseudo', 'kevinard');
	}
	
	public function error404($request = array())
	{
		$this->set('t1', '404 Page not found<br />');
		$this->set('request', $request);
		$this->set('t2', '<br />');
		$this->setView('globals/error404');
	}
}
?>