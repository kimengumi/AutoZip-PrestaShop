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

class Autozip extends Module {

    public function __construct() {
        $this->name = 'autozip';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Antonio Rossetti';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Management of automatic zip files update');
        $this->description = $this->l('This module Allow you to automatically update your products downloads or attachments from an external source');

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

    /**
     * Load the configuration form
     */
    public function getContent() {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitAutozipModule')) == true)
            $this->postProcess();

        return $this->renderReminder().$this->renderForm();
    }

    /**
     * Create the reminder to schedule the cron
     */
    public function renderReminder($display_panel = true) {

        $this->context->smarty->assign('display_panel', (bool)$display_panel);
        $this->context->smarty->assign('cron_cli', _PS_MODULE_DIR_.$this->name.'/cron.php');
        $this->context->smarty->assign('cron_url',
            Tools::getAdminUrl('modules/'.$this->name.'/cron.php?'.
                Configuration::get('AUTOZIP_TOKEN_NAME').'='.Configuration::get('AUTOZIP_TOKEN_KEY')));
        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm() {

        $features_list = Feature::getFeatures($this->context->language->id);
        $features_list[] = array('id_feature' => 0, 'name' => $this->l('(None)'));

        $form = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Version Feature'),
                    'name' => 'id_feature',
                    'required' => true,
                    'options' => array(
                        'query' => $features_list,
                        'id' => 'id_feature',
                        'name' => 'name',
                    ),
                    'desc' => $this->l('When using a GIT repository as source, the script will be able to auto detect & use the latest TAG')
                    .'<br/>'.$this->l('The TAG name will be stored as a custom value of this feature, allowing your customers to see the published current version number')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAutozipModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value['id_feature'] = (int)Configuration::get('AUTOZIP_ID_FEATURE');

        return $helper->generateForm(array(array('form' => $form)));
    }

    /**
     * Save form data.
     */
    protected function postProcess() {

        Configuration::updateGlobalValue('AUTOZIP_ID_FEATURE', (int)Tools::getValue('id_feature'));
    }

}
