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
/*
 * This script is intended to be launched in command line only (mostly via a System Crontab)
 */

// Load Prestashop environnement
require(dirname(__FILE__).'/../../config/config.inc.php');
if (!defined('_PS_VERSION_'))
    exit;

// Allow only running from CLI or with a token parameter
if (!Tools::isPHPCLI()) {
    if (Tools::getValue(Configuration::get('AUTOZIP_TOKEN_NAME')) !==
        Configuration::get('AUTOZIP_TOKEN_KEY')) {
        header('HTTP/1.0 403 Forbidden');
        die('This script is intended to be launched in command line or with the right token'); ;
    }
}

// Load Config
require_once(_PS_MODULE_DIR_.'autozip/classes/AutoZipConfig.php');
require_once(_PS_MODULE_DIR_.'autozip/classes/AutoZipCron.php');

AutoZipCron::checkCommonPrerequisities();

foreach (AutoZipConfig::getAllStatic() as $autozip) {
    try {
        $last_version = AutoZipCron::{$autozip->source_type.'Download'}($autozip);
        AutoZipCron::generateZip($autozip, $last_version);
        if ($last_version)
            AutoZipCron::UpdateVersionNumber($autozip, $last_version);
        $autozip->updateZipDate();
    } catch (Exception $e) {
        echo "\n".'Error processing autozip "'.$autozip->zip_basename.' - '.$autozip->source_url.'" : '."\n".$e->getMessage();
    }
}