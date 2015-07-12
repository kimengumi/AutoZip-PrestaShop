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
class AutoZipConfig extends ObjectModel {

	protected $cipher;
	public $id_autozip;
	public $id_attachment;
	public $id_product_download;
	public $source_url;
	public $source_folder;
	public $source_type;
	public $source_login;
	public $source_password;
	public $zip_folder;
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
			),
			'zip_folder' => array(
				'type' => ObjectModel :: TYPE_STRING,
				'required' => false
			)
		)
	);

	/**
	 * Builds the object
	 *
	 * @param int|null $id      If specified, loads and existing object from DB (optional).
	 * @param int|null $id_lang Required if object is multilingual (optional).
	 * @param int|null $id_shop ID shop for objects with multishop tables.
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function __construct($id = null, $id_lang = null, $id_shop = null) {

		if (defined('_RIJNDAEL_KEY_') && defined('_RIJNDAEL_IV_'))
			$this->cipher = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
		else if (defined('_COOKIE_KEY_') && defined('_COOKIE_IV_'))
			$this->cipher = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
		else
			throw new PrestaShopException('AutoZip : No Encryption method available');
		
		parent::__construct($id, $id_lang, $id_shop);

		if ($this->source_password)
			$this->source_password = $this->cipher->decrypt($this->source_password);
	}

	/**
	 * Updates the current object in the database
	 *
	 * @param bool $null_values
	 *
	 * @return bool
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function update($nullValues = false) {

		//keep old password if no new provided
		if (!$this->source_password && $this->source_login) {
			$old = new self($this->id_autozip);
			$this->source_password = $old->source_password;
		}

		//encrypt Passowrd
		if ($this->source_password)
			$this->source_password = $this->cipher->encrypt($this->source_password);

		return parent::update($nullValues);
	}

	/**
	 * Adds current object to the database
	 *
	 * @param bool $auto_date
	 * @param bool $null_values
	 *
	 * @return bool Insertion result
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function add($autodate = true, $null_values = true) {

		//encrypt Passowrd
		if ($this->source_password)
			$this->source_password = $this->cipher->encrypt($this->source_password);

		return parent::add($autodate, $null_values);
	}

}
