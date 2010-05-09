<?php
namespace framework\utils;

// La classe Flash permet de faire passer des messages d'une page à l'autre, à l'aide des sessions.
// Elle peut être utilisée pour faire passer des messages d'information ou d'erreur suite à l'exécution d'une page, par exemple.
class Flash
{    
    // contient les messages provenant de la page précédente
    protected static $previousFlash = array();
    
    // contient l'unique instance de Flash (singleton)
    protected static $instance = null;
    
    // initialise la classe en remplissant $previousFlash et en vidant la session contenant les messages de la page précédente
    protected function __construct() {
    	if(!empty($_SESSION['flash']) && is_array($_SESSION['flash']))
        {
            self::$previousFlash = $_SESSION['flash'];
        }
        
        self::clear();
    }
    
    protected function __clone() {}
    
    // renvoie l'unique instance de Flash
    public static function getInstance()
    {
    	if(!isset(self::$instance))
        {
        	self::$instance = new self();
        }
        
        return self::$instance;
    }

    // renvoie le message provenant de la page précédente, s'il existe
    public static function get($var)
    {
        return isset(self::$previousFlash[$var]) ? self::$previousFlash[$var] : null;
    }

    // enregistre un message dans la session, qui sera disponible à la page suivante, en utilisant get (ci-dessus)
    public static function set($var, $value)
    {
        $_SESSION['flash'][$var] = $value;
    }

    // vide la session contenant les messages flash
    public static function clear()
    {
        $_SESSION['flash'] = array();
    }
} // fin de Flash
?>