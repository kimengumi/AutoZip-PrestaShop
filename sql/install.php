<?php
/**
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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'autozip` (
  `id_autozip` int(11) NOT NULL AUTO_INCREMENT,
  `id_attachment` int(10) NOT NULL,
  `id_product_download` int(10) NOT NULL,
  `source_url` varchar(255) NOT NULL,
  `source_folder` varchar(255) DEFAULT NULL,
  `source_type` varchar(32) NOT NULL,
  `source_login` varchar(128) DEFAULT NULL,
  `source_password` varchar(128) DEFAULT NULL,
  `zip_folder` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_autozip`),
  UNIQUE KEY `id_attachment` (`id_attachment`,`id_product_download`)
) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2';

foreach ($sql as $query)
	if (Db::getInstance()->execute($query) == false)
		return false;
