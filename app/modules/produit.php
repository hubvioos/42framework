<?php
namespace app\modules;
use framework\libs as F;

class produit extends \framework\libs\Controller
{
	public function index()
	{
		//echo 'It works !';
	}
	
	public function view($id = null)
	{
		if($id == 31)
		{
			return $this->showError404(array('module' => 'produit', 'action' => 'view', 'params' => array($id)));
		}
		
		if($id == 44)
		{
			F\Response::getInstance()->location('http://blog.kevinoneill.fr', 302);
		}
		
		$this->set('text', 'It works ! => produit/view/'.$id);
		$this->set('pageTitle', 'Article '.$id);
	}
}
?>