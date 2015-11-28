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
 * to arossetti@users.noreply.github.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AutoZip to newer
 * versions in the future. If you wish to customize AutoZip for your
 * needs please refer to https://github.com/arossetti/Prestashop-Module-AutoZip for more information.
 *
 *  @author    Antonio Rossetti <arossetti@users.noreply.github.com>
 *  @copyright Antonio Rossetti
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  
 */
if (!defined('_PS_VERSION_'))
    exit;

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
    public $zip_basename;
    public $zip_folder;
    public $active;
    public $last_zip_update;
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
            'zip_basename' => array(
                'type' => ObjectModel :: TYPE_STRING,
                'required' => true
            ),
            'zip_folder' => array(
                'type' => ObjectModel :: TYPE_STRING,
                'required' => false
            ),
            'active' => array(
                'type' => ObjectModel :: TYPE_BOOL,
                'required' => true
            ),
            'last_zip_update' => array(
                'type' => ObjectModel :: TYPE_DATE,
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
     * updateZipDate
     * 
     * Update the last_zip_update fields with current date
     * 
     * @return type
     */
    public function updateZipDate() {

        $this->last_zip_update = date('Y-m-d H:i:s');
        return $this->update();
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

    public function getRelatedProductsIds() {

        if ($this->id_attachment) {
            $sql_ids = 'SELECT DISTINCT pa.id_product '
                .'FROM `'._DB_PREFIX_.'product_attachment` pa '
                .'WHERE pa.id_attachment = '.(int)$this->id_attachment;
        } else if ($this->id_product_download) {
            $sql_ids = 'SELECT DISTINCT pd.id_product '
                .'FROM `'._DB_PREFIX_.'product_download` pd '
                .'WHERE pd.id_product_download = '.(int)$this->id_product_download;
        } else
            return array();

        return Db::getInstance()->ExecuteS($sql_ids);
    }

    /**
     * Get all actives configs
     * 
     * @return array self
     */
    public static function getAllStatic() {
        $all = array();
        $ids = Db::getInstance()->ExecuteS('SELECT id_autozip '
            .'FROM `'._DB_PREFIX_.self::$definition['table'].'` '
            .'WHERE active=1');

        foreach ($ids as $id) {
            $all[(int)$id['id_autozip']] = new self((int)$id['id_autozip']);
        }
        return $all;
    }

}
