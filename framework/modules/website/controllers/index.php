<?php 
namespace application\modules\website\controllers;

class Index extends \framework\core\Controller
{
       public function _before(\framework\core\Request &$request, \framework\core\Response &$response)
        {
            echo "Index - Before <br/>";
        }
        
	public function processAction ()
	{
            echo "Index - Action <br/>";
            $this->getComponent("eventManager")->addListener("afterIndex", "\\application\\modules\\website\\controllers\\kuku::kaka");
        }
        
        public function _after(\framework\core\Request &$request, \framework\core\Response &$response)
        {
            echo "Index - After <br/>";
        }

}
