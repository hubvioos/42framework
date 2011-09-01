<?php
namespace application\modules\website\controllers;

class Kuku extends \framework\core\Controller
{

        public function _before(\framework\core\Request &$request, \framework\core\Response &$response)
        {
            echo "Kuku - Before <br/>";

        }

	public function processAction ()
	{
            $this->usesView = false;
        }

        public function _after(\framework\core\Request &$request, \framework\core\Response &$response)
        {
            echo "Kuku - After <br/>";
        }

        public static function kaka()
        {

            echo "kaka works <br/>";
        }

}
