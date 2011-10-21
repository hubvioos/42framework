<?php
namespace framework\modules\website\controllers;

class Kuku extends \framework\core\Controller
{

        public function _before()
        {
            echo "Kuku - Before <br/>";

        }

	public function processAction ()
	{
            $this->usesView = false;
        }

        public function _after()
        {
            echo "Kuku - After <br/>";
        }

        public static function kaka()
        {

            echo "kaka works <br/>";
        }

}
