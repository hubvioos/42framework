<?php
namespace app\modules;

class produit extends \framework\libs\Controller
{
	public function index()
	{
		echo 'It works !';
	}
	
	public function view($id = null)
	{
		if($id == 31)
		{
			return $this->showError404(array('module' => 'produit', 'action' => 'view', 'params' => array($id)));
		}
		
		$this->set('text', 'It works ! => produit/view/'.$id);
		$this->set('pageTitle', 'Article '.$id);
	}
}
?>