<?php

/**
 * ---------------------------------------------------------------------------------
 *
 * 1997-2013 Quadra Informatique
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ecommerce@quadra-informatique.fr so we can send you a copy immediately.
 *
 * @author Quadra Informatique <ecommerce@quadra-informatique.fr>
 * @copyright 1997-2013 Quadra Informatique
 * @version Release: $Revision: 1.0 $
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * ---------------------------------------------------------------------------------
 */
class AutoZipConfig extends ObjectModel
{

	public $id_autozip;
	public $id_attachment;
	public $id_product_download;
	public $source_url;
	public $source_folder;
	public $source_type;
	public $source_login;
	public $source_password;
	public static $definition = array(
		'table' => 'autozip',
		'primary' => 'id_autozip',
		'multilang' => false,
		'fields' => array(
			'id_autozip' => array(
				'type' => ObjectModel :: TYPE_INT
			),
			'id_attachment' => array(
				'type' => ObjectModel :: TYPE_INT,
				'required' => true
			),
			'id_product_download' => array(
				'type' => ObjectModel :: TYPE_INT,
				'required' => true
			),
			'source_url' => array(
				'type' => ObjectModel :: TYPE_STRING,
				'required' => true,
			),
			'source_folder' => array(
				'type' => ObjectModel :: TYPE_STRING,
				'required' => false
			),
			'source_type' => array(
				'type' => ObjectModel :: TYPE_STRING,
				'required' => true
			),
			'source_login' => array(
				'type' => ObjectModel :: TYPE_STRING,
				'required' => false
			),
			'source_password' => array(
				'type' => ObjectModel :: TYPE_STRING,
				'required' => false
			)
		)
	);

}
