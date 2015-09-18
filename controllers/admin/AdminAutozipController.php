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
require_once _PS_MODULE_DIR_.'autozip/classes/AutoZipConfig.php';

class AdminAutozipController extends ModuleAdminController {

    public function __construct() {

        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'autozip';
        $this->explicitSelect = true;
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'attachment_lang` atl ON a.id_attachment = atl.id_attachment AND atl.id_lang = '.(int)$this->context->language->id.' '.
            'LEFT JOIN `'._DB_PREFIX_.'product_download` pd ON a.id_product_download = pd.id_product_download '.
            'LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON pl.id_product = pd.id_product AND pl.id_lang = '.(int)$this->context->language->id;
        $this->_select = 'a.active as zip_active, IF (a.id_product_download,pl.name,atl.name) AS name ';
        $this->className = 'AutoZipConfig';
        $this->identifier = 'id_autozip';
        $this->lang = false;
        $this->requiredDatabase = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent :: __construct();
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
        $this->fields_list = array(
            $this->identifier => array(
                'title' => '#',
            ),
            'name' => array(
                'title' => $this->l('Attachment or Virtual product'),
                'search' => false
            ),
            'source_url' => array(
                'title' => $this->l('Source Url'),
            ),
            'source_folder' => array(
                'title' => $this->l('Source Folder'),
            ),
            'last_zip_update' => array(
                'title' => $this->l('Last Zip Date'),
                'type' => 'datetime',
            ),
            'zip_active' => array(
                'title' => $this->l('Enabled'),
                'active' => 'status',
                'search' => false
            ),
        );
    }

    public function renderList() {

        // if no cron execution within the last weekn we display the reminder as a warning
        if (!(int)Db::getInstance()->getValue('SELECT COUNT('.$this->identifier.') '
                .'FROM `'._DB_PREFIX_.$this->table.'` '
                .'WHERE DATE(last_zip_update) >= DATE_SUB(NOW(),INTERVAL 1 WEEK)')) {
            $this->warnings[] = $this->l('No cron execution detected within the last week').
                $this->module->renderReminder(false);
        }


        // if some product dowloads links are out of date
        $missing_links = Db::getInstance()->executeS('SELECT a.'.$this->identifier.', a.zip_basename '
            .'FROM `'._DB_PREFIX_.$this->table.'` a '
            .$this->_join
            .' WHERE ! pd.id_product_download IS NULL '
            .' AND ! atl.id_attachment IS NULL ');
        foreach ($missing_links as $ml)
                $this->errors[] = $this->l('Attachment or Virtual product does not exit for Autozip').
                ' '.$ml[$this->identifier].' - '.$ml['zip_basename'];

        return parent::renderList();
    }

    public function postProcess() {
        if ($this->action) {

            if (!$this->tabAccess['add'] === '1')
                $this->errors[] = Tools::displayError('You do not have write access.');

            if (Tools::getIsset('id_attachment') && Tools::getIsset('id_product_download') &&
                (int)Tools::getValue('id_attachment') && (int)Tools::getValue('id_product_download'))
                $this->errors[] = Tools::displayError('You can not choose an attachment and a download at the same time.');

            if (Tools::getIsset('id_attachment') && Tools::getIsset('id_product_download') &&
                !(int)Tools::getValue('id_attachment') && !(int)Tools::getValue('id_product_download'))
                $this->errors[] = Tools::displayError('You have to choose an attachment OR a download.');
        }
        parent::postProcess();
    }

    public function renderForm() {

        $attachement_list = Db::getInstance()->ExecuteS(
            'SELECT a.id_attachment,CONCAT(a.id_attachment," - ",atl.name) AS name '
            .'FROM `'._DB_PREFIX_.'attachment` a '
            .'LEFT JOIN `'._DB_PREFIX_.'attachment_lang` atl ON '
            .'atl.id_attachment = a.id_attachment AND atl.id_lang = '.(int)$this->context->language->id.' '
            .(Tools::getIsset('add'.$this->table) ?
                'WHERE a.id_attachment NOT IN (SELECT id_attachment FROM `'._DB_PREFIX_.$this->table.'`) ' : ' ')
            .'ORDER BY atl.name');

        $download_list = Db::getInstance()->ExecuteS(
            'SELECT pd.id_product_download,CONCAT(pl.id_product," - ",pl.name) AS name   '
            .'FROM `'._DB_PREFIX_.'product_download` pd '
            .'LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON '
            .'pl.id_product = pd.id_product AND pl.id_lang = '.(int)$this->context->language->id.' '
            .(Tools::getIsset('add'.$this->table) ?
                'WHERE pd.id_product_download NOT IN (SELECT id_product_download FROM `'._DB_PREFIX_.$this->table.'`) ' : ' ')
            .'ORDER BY pl.name');

        // No option available
        if (!count($attachement_list) && !count($download_list)) {
            $this->errors[] = Tools::displayError('No attachment or download available (or all already assigned).');
            $this->fields_form = array(
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'hidden'
                )
            );
            return parent::renderForm();
        }

        // "None" option on both list
        $attachement_list[] = array('id_attachment' => 0, 'name' => $this->l('(None)'));
        $download_list[] = array('id_product_download' => 0, 'name' => $this->l('(None)'));
        $this->informations[] = Tools::displayError('You have to choose an attachment OR a download.');

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('AutoZip'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Attachment'),
                    'name' => 'id_attachment',
                    'required' => true,
                    'options' => array(
                        'query' => $attachement_list,
                        'id' => 'id_attachment',
                        'name' => 'name',
                    ),
                    'desc' => $this->l('To make the attachment available in the list you first have to create the attachment with the Name, description, and a dummy zip file in "Catalog > Attachments" or in a product edit page, tab "Attachments"')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Virtual product'),
                    'name' => 'id_product_download',
                    'required' => true,
                    'options' => array(
                        'query' => $download_list,
                        'id' => 'id_product_download',
                        'name' => 'name',
                    ),
                    'desc' => $this->l('To make the virtual product available in the list you first have to create the entry with the name, download rules, and a dummy zip file in the product edit page, tab "Virtual product"')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Zip base name'),
                    'name' => 'zip_basename',
                    'size' => 64,
                    'maxlength' => 96,
                    'required' => true,
                    'desc' => $this->l('Base name used to generate the name of the zip file.').' '.
                    $this->l('If available (GIt source having Tags), the latest version number will be added at the end of the filename.').
                    ' '.$this->l('Example : "myname" will give "myname-1.2.3.zip"')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Zip Folder'),
                    'name' => 'zip_folder',
                    'size' => 64,
                    'maxlength' => 255,
                    'required' => false,
                    'desc' => $this->l('Root folder name inside the zip (keep empty to disable root folder).')
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Source type'),
                    'name' => 'source_type',
                    'required' => true,
                    'desc' => null,
                    'values' => array(
                        array(
                            'id' => 'git',
                            'value' => 'git',
                            'label' => 'GIT (ssh / https)<p class="help-block">'.
                            $this->l('The script will be able to auto detect & use the latest TAG.').'<br/>'.
                            $this->l('The Tag name should be exclusively composed of numbers and dots (eg. "1.2.3.4").').'</p>'
                        ),
                        array(
                            'id' => 'svn',
                            'value' => 'svn',
                            'label' => 'SVN (http / https / ssh)'
                        ),
                        array(
                            'id' => 'wget',
                            'value' => 'wget',
                            'label' => $this->l('File server ').' (ftp / http / https) <p class="help-block">'.
                            $this->l('If your server is hosting thousand of files, you should specify your subfolder in the "Source Url" AND in the "Source Folder", to avoid downloading unnecessary datas.').'</p>'
                        ),
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Source Url'),
                    'name' => 'source_url',
                    'size' => 64,
                    'maxlength' => 255,
                    'required' => true,
                    'desc' => $this->l('Example :').'<br/>'.
                    'git@github.com:arossetti/Prestashop-Module-AutoZip.git<br/>'.
                    'https://github.com/arossetti/Prestashop-Module-AutoZip.git<br/>'.
                    'https://svn.someserver.net/somerepo/branches/publish<br/>'.
                    'ftp://ftp.someserver.net/some/directory<br/>'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Source Login'),
                    'name' => 'source_login',
                    'size' => 32,
                    'maxlength' => 128,
                    'required' => false,
                    'desc' => $this->l('Optional.').' '.
                    $this->l('The script will be able to use the credential keys of the account running the cron job (eg. SSH keys for GIt or SVN).')
                ),
                array(
                    'type' => 'password',
                    'label' => $this->l('Source Password'),
                    'name' => 'source_password',
                    'size' => 32,
                    'maxlength' => 128,
                    'required' => false,
                    'desc' => $this->l('Optional.').' '.
                    $this->l('The script will be able to use the credential keys of the account running the cron job (eg. SSH keys for GIt or SVN).')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Source Folder'),
                    'name' => 'source_folder',
                    'size' => 64,
                    'maxlength' => 255,
                    'required' => false,
                    'desc' => $this->l('Subfolder in the source to use as base dir :').'<br/>'.
                    $this->l('Relative path for GIT & SVN sources').'<br/>'.
                    $this->l('Absolute path for File Server sources')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enabled :'),
                    'title' => $this->l('Enabled :'),
                    'name' => 'active',
                    'required' => true,
                    'desc' => $this->l('Enable or Disable the generation of this zip file'),
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        return parent::renderForm();
    }

}
