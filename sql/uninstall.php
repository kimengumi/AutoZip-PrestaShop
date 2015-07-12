<?php
/**
* 2007-2015 Antonio Rossetti
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Antonio Rossetti to newer
* versions in the future. If you wish to customize Antonio Rossetti for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Antonio Rossetti <arossetti@users.noreply.github.com>
*  @copyright Antonio Rossetti
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  
*/

/**
* In some cases you should not drop the tables.
* Maybe the merchant will just try to reset the module
* but does not want to loose all of the data associated to the module.
*/

$sql = array();

$sql[] = 'DROP TABLE `'._DB_PREFIX_.'autozip`';

foreach ($sql as $query)
	if (Db::getInstance()->execute($query) == false)
		return false;
