<?php // this is an autogenerated file - do not edit (created ___CREATED___)
if (!defined('__DIR__')) define('__DIR__',dirname(__FILE__));
function ___AUTOLOAD___($class) {
   static $classes = array(
         ___CLASSLIST___
   );
   $cn = strtolower($class);
   if (isset($classes[$cn])) {
      require ___BASEDIR___$classes[$cn];
   }
}
spl_autoload_register('___AUTOLOAD___');
