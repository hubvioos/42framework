<?php
namespace framework\utils;

// Inflector permet de formater une chaîne de caractères selon des règles précises
class Inflector 
{
    // transforme une chaîne de caractères avec la notatino camel-case ("une chaine de caracteres" devient "UneChaineDeCaracteres")
    public static function camelize($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
    
    public static function lowerCamelize($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    public static function underscore($string)
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', self::camelize($string)));
    }
    
    public static function humanize($string)
    {
        return ucfirst(str_replace('_', ' ', $string));
    }
    
    public static function slug($string)
    {
    	$string = str_replace( array('à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý'), array('a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y'), $string);
    	$string = str_replace(array(' ', '\''), '-', $string);
    	$string = preg_replace('#[^a-zA-Z0-9 -]#', '', preg_replace('#[-]+#', '-', $string));
    	
    	return trim(strtolower($string), '-');
    }
} // fin de Inflector
?>