<?php

$components = array(

	/**
	 * Number Helper provides usefull methods for number management (conversion, displaying,...)
	 */
	 	/**
	 * Number Helper provides usefull methods for number management (conversion, displaying,...)
	 */
	'appp' =>	 array('callable' =>	function ($c, $args)
													{
														/* @var $c ComponentsContainer */
														return new \framework\helpers\Number();
													},
								'isUnique' => false)

);