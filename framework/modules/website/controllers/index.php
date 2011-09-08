<?php 
namespace framework\modules\website\controllers;

class Index extends \framework\core\Controller
{
       public function _before()
        {
            echo "Index - Before <br/>";
        }
        
	public function processAction ()
	{
            echo "Index - Action <br/>";
            //$this->getComponent("eventManager")->addListener("afterIndex", "\\application\\modules\\website\\controllers\\kuku::kaka");
        }
        
        public function _after()
        {
            echo "Index - After <br/>";
        }

}
