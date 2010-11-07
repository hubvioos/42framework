<?php
/**
 * Copyright (C) 2010 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
 * 
 * 42framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * 42framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */
defined('FRAMEWORK_DIR') or die('Invalid script access');

$autoload = array(
         'theseer\\tools\\autoloadbuilder' => VENDORS_DIR.DS.'theseer'.DS.'autoload'.DS.'autoloadbuilder.php',
         'theseer\\tools\\autoloadbuilderexception' => VENDORS_DIR.DS.'theseer'.DS.'autoload'.DS.'autoloadbuilder.php',
         'theseer\\tools\\autoloadbuildercli' => VENDORS_DIR.DS.'theseer'.DS.'autoload'.DS.'autoloadbuildercli.php',
         'theseer\\tools\\classfinder' => VENDORS_DIR.DS.'theseer'.DS.'autoload'.DS.'classfinder.php',
         'theseer\\tools\\phpfilteriterator' => VENDORS_DIR.DS.'theseer'.DS.'autoload'.DS.'phpfilter.php',
         'theseer\\tools\\directoryscanner' => VENDORS_DIR.DS.'theseer'.DS.'scanner'.DS.'directoryscanner.php',
         'theseer\\tools\\directoryscannerexception' => VENDORS_DIR.DS.'theseer'.DS.'scanner'.DS.'directoryscanner.php',
         'theseer\\tools\\filesonlyfilteriterator' => VENDORS_DIR.DS.'theseer'.DS.'scanner'.DS.'filesonlyfilter.php',
         'theseer\\tools\\includeexcludefilteriterator' => VENDORS_DIR.DS.'theseer'.DS.'scanner'.DS.'includeexcludefilter.php',
         'application\\modules\\cli\\autoloadbuilder' => MODULES_DIR.DS.'cli'.DS.'AutoloadBuilder.php',
         'application\\modules\\cli\\classfinder' => MODULES_DIR.DS.'cli'.DS.'ClassFinder.php',
         'application\\modules\\cli\\cliutils' => MODULES_DIR.DS.'cli'.DS.'CliUtils.php',
         'application\\modules\\cli\\configbuilder' => MODULES_DIR.DS.'cli'.DS.'ConfigBuilder.php',
         'application\\modules\\cli\\controllers\\cliexception' => MODULES_DIR.DS.'cli'.DS.'controllers'.DS.'CliCommand.php',
         'application\\modules\\cli\\controllers\\clicommand' => MODULES_DIR.DS.'cli'.DS.'controllers'.DS.'CliCommand.php',
         'application\\modules\\cli\\controllers\\compileautoload' => MODULES_DIR.DS.'cli'.DS.'controllers'.DS.'compileAutoload.php',
         'application\\modules\\cli\\controllers\\compileconfig' => MODULES_DIR.DS.'cli'.DS.'controllers'.DS.'compileConfig.php',
         'application\\modules\\cli\\controllers\\showdoc' => MODULES_DIR.DS.'cli'.DS.'controllers'.DS.'showDoc.php',
         'application\\modules\\cli\\directoryscanner' => MODULES_DIR.DS.'cli'.DS.'DirectoryScanner.php',
		 'application\\modules\\errors\\generic' => MODULES_DIR.DS.'errors'.DS.'generic.php',
         'application\\modules\\errors\\controllers\\error403' => MODULES_DIR.DS.'errors'.DS.'controllers'.DS.'error403.php',
         'application\\modules\\errors\\controllers\\error404' => MODULES_DIR.DS.'errors'.DS.'controllers'.DS.'error404.php',
         'application\\modules\\errors\\controllers\\error503' => MODULES_DIR.DS.'errors'.DS.'controllers'.DS.'error503.php',
         'application\\modules\\website\\controllers\\index' => MODULES_DIR.DS.'website'.DS.'controllers'.DS.'index.php'
    );