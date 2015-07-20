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
if (!defined('_PS_VERSION_'))
    exit;

class Autozip extends Module {

    public function __construct() {
        $this->name = 'autozip';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Antonio Rossetti';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Automatic Zip Update Mangement');
        $this->description = $this->l('This module Allow you to automaticaly update your virtual Products from a private or public Git repository, according to your last version TAG');

        $this->confirmUninstall = $this->l('Uninstalling the module will delete configured sources. Are you Sure ?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install() {

        // Database Table
        include(dirname(__FILE__).'/sql/install.php');

        // Token for "wget" style crons
        Configuration::updateGlobalValue('AUTOZIP_TOKEN_NAME', Tools::passwdGen(10));
        Configuration::updateGlobalValue('AUTOZIP_TOKEN_KEY', Tools::passwdGen(10));

        // Module Tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminManageAutoZip';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'AutoZip';
        }

        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminCatalog');

        return parent::install() && $tab->add();
    }

    public function uninstall() {

        // Database Table
        include(dirname(__FILE__).'/sql/uninstall.php');

        // Module Tab
        if ($id_tab = (int)Tab::getIdFromClassName('AdminManageAutoZip')) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }

        return parent::uninstall();
    }

}
