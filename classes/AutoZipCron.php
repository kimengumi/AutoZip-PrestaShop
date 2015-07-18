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
if (!defined('_PS_VERSION_'))
    exit;

if (!defined('_AUTOZIP_TMP_')) {
    define('_AUTOZIP_TMP_', _PS_MODULE_DIR_.'autozip/tmp/');
    chdir(_AUTOZIP_TMP_);
}

class AutoZipCron {

    /**
     * cliExec
     * 
     * @param string $cmd
     * @param array $env
     * @param string $cwd
     * @param string $stdin
     * @param string $stdout
     * @param bool $throw_execption
     * @return boolean
     * @throws PrestaShopException
     */
    protected static function cliExec($cmd, $env = array(), $cwd = _AUTOZIP_TMP_, $stdin = null, &$stdout = null,
        $throw_execption = true) {

        if (_PS_DEBUG_PROFILING_)
            echo '# '.$cmd."\n";

        $pipes = array();

        $descriptors = array(
            0 => array("pipe", "r"), // stdin
            1 => array("pipe", "w"), // stdout
            2 => array("pipe", "w")  // stderr
        );

        $proc = proc_open($cmd, $descriptors, $pipes, $cwd, $env);

        if (!is_resource($proc))
            throw new PrestaShopException('Unknown failure, maybe the php function "proc_open()" is not allowed');

        if ($stdin)
            fwrite($pipes[0], $stdin."\n");
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $return = proc_close($proc);

        if ((int)$return) {

            if (_PS_MODE_DEV_) {
                $message = "\n".
                    '==== Command Line Error ===='."\n".
                    'Path        : '.$cwd."\n".
                    'Command     : '.$cmd."\n".
                    'Return code : '.(int)$return."\n".
                    ($stdin ? 'Input       : **** hidden for security reason ****'."\n" : '').
                    ($stdout ? 'Output      : '.$stdout : '').
                    ($stderr ? 'Error       : '.$stderr : '').
                    '============================'."\n";
            } else
                $message = "\n".$stderr;

            if ($throw_execption)
                throw new PrestaShopException($message);
            else
                return false;
        } else
            return true;
    }

    /**
     * checkCommandAvailability
     * 
     * @param string $cmds
     * @throws PrestaShopException
     */
    protected static function checkCommandAvailability($cmds) {

        $miss = array();
        foreach ($cmds as $cmd) {
            if (!self::cliExec('which '.$cmd, array(), _AUTOZIP_TMP_, null, $stdout, false)) {
                $miss[] = $cmd;
            }
        }
        if (count($miss)) {
            throw new PrestaShopException('"'.implode('","', $miss).
            '" CLI software(s) is(are) not installed on the system OR not available in the current ENV path.'
            ."\n".'Please Install software(s) or correct your ENV.');
        }
    }

    /**
     * checkCommonPrerequisities
     * 
     * @throws PrestaShopException
     */
    public static function checkCommonPrerequisities() {

        self::checkCommandAvailability(array('rm', 'mv', 'zip'));

        if (!is_writable(_AUTOZIP_TMP_))
            throw new PrestaShopException('The directory "'._AUTOZIP_TMP_.
            '" must be accessible with write permission for the current user');

        if (!is_writable(_PS_DOWNLOAD_DIR_))
            throw new PrestaShopException('The directory "'._PS_DOWNLOAD_DIR_.
            '" must be accessible with write permission for the current user');
    }

    /**
     * gitDownload
     * 
     * @param AutoZipConfig $autozip
     * @return string
     */
    public static function gitDownload(AutoZipConfig $autozip) {

        self::checkCommandAvailability(array('git', 'sort', 'tail', 'find', 'xargs'));

        //Clear temporary space
        self::cliExec('rm -rf '._AUTOZIP_TMP_.'* '._AUTOZIP_TMP_.'.[a-z]*');

        if (!$autozip->source_login && !$autozip->source_password)
            $source_url = $autozip->source_url;

        else {
            $parts = parse_url($autozip->source_url);
            $source_url = (isset($parts['scheme']) ? $parts['scheme'].'://' : '').
                ($autozip->source_login ? $autozip->source_login :  //first we prefer use the dedicated field but
                    (isset($parts['user']) ? $parts['user'] : '')). //if only contained in the url, we will use it
                ($autozip->source_password ? ':'.$autozip->source_password : //idem login 
                    (isset($parts['pass']) ? ':'.$parts['pass'] : '')). 
                (($parts['user'] || $parts['pass']) ? '@' : '').
                (isset($parts['host']) ? $parts['host'] : '').
                (isset($parts['port']) ? ':'.$parts['port'] : '').
                (isset($parts['path']) ? $parts['path'] : '').
                (isset($parts['query']) ? '?'.$parts['query'] : '').
                (isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        }

        // Git Checkout
        self::cliExec('git clone "'.$source_url.'" download ',
            array('GIT_SSL_NO_VERIFY' => 'true', 'GIT_ASKPASS' => 'false'));

        // get last TAG name
        $last_tag = null;
        self::cliExec('git tag -l | sort -bt. -k1,1n -k2,2n -k3,3n -k4,4n -k5,5n -k6,6n -k7,7n -k8,8n | tail -n 1',
            array('GIT_SSL_NO_VERIFY' => 'true'), _AUTOZIP_TMP_.'download', null, $last_tag);

        // Switch to last TAG (if TAG exists ;)
        if ($last_tag)
            self::cliExec('git checkout -q tags/'.trim($last_tag), array('GIT_SSL_NO_VERIFY' => 'true'),
                _AUTOZIP_TMP_.'download');

        // Init all submodules (if the project have some)
        self::cliExec('git submodule init', array('GIT_SSL_NO_VERIFY' => 'true'), _AUTOZIP_TMP_.'download');
        self::cliExec('git submodule update', array('GIT_SSL_NO_VERIFY' => 'true'), _AUTOZIP_TMP_.'download');

        // Clean all git files.
        self::cliExec('find '._AUTOZIP_TMP_.'download -name ".git*" -print | xargs /bin/rm -rf');

        return trim($last_tag);
    }

    /**
     * ftpDownload
     * 
     * We use the stdin pipe to avaoid password to be displayed on system"s process list.
     * 
     * @param AutoZipConfig $autozip
     * @return null
     */
    public static function ftpDownload(AutoZipConfig $autozip) {

        self::checkCommandAvailability(array('wget', 'mkdir'));

        //Clear temporary space
        self::cliExec('rm -rf '._AUTOZIP_TMP_.'* '._AUTOZIP_TMP_.'.[a-z]*');

        self::cliExec('mkdir -p '._AUTOZIP_TMP_.'download');
        self::cliExec('wget -nH -r '.$autozip->source_url.' '.
            ($autozip->source_login ? ' --user='.$autozip->source_login : '').' '.
            ($autozip->source_password ? ' --ask-password' : ''), array(), _AUTOZIP_TMP_.'download',
            $autozip->source_password);

        return null;
    }

    /**
     * generateZip
     * 
     * @param AutoZipConfig $autozip
     * @param string $version_number
     */
    public static function generateZip(AutoZipConfig $autozip, $version_number = null) {

        // Move the configured folder as source folder        
        if ($autozip->source_folder)
            self::cliExec('mv download/'.$autozip->source_folder.' source');
        else
            self::cliExec('mv download source');

        // Zip with or without root folder in the zip
        if ($autozip->zip_folder) {
            self::cliExec('mv source '.$autozip->zip_folder);
            self::cliExec('zip -qr autozip.zip '.$autozip->zip_folder);
        } else
            self::cliExec('zip -qr ../autozip.zip . ', array(), _AUTOZIP_TMP_.'source');


        if ($autozip->id_attachment) {

            // Move the generated zip as the "regular" Attachement
            $attachment = new Attachment($autozip->id_attachment);
            self::cliExec('mv autozip.zip '._PS_DOWNLOAD_DIR_.$attachment->file);

            if ($autozip->zip_basename)
                $attachment->file_name = $autozip->zip_basename.($version_number ? '-'.$version_number : '').'.zip';
            $attachment->mime = 'application/zip';
            $attachment->update();
        }else if ($autozip->id_product_download) {

            // Move the generated zip as the "regular" Product Download
            $product_download = new ProductDownload($autozip->id_product_download);
            self::cliExec('mv autozip.zip '._PS_DOWNLOAD_DIR_.$product_download->filename);

            if ($autozip->zip_basename)
                $product_download->display_filename = $autozip->zip_basename.($version_number ? '-'.$version_number : '').'.zip';
            $product_download->date_add = date('Y-m-d H:i:s');

            //Prestashop dos not like the way he is himself storing an empty date (we do not change this field)
            if ($product_download->date_expiration === '0000-00-00 00:00:00')
                $product_download->date_expiration = null;

            $product_download->update();
        }
    }

    /**
     * updateVersionNumber
     * 
     * @param AutoZipConfig $autozip
     * @param type $version_number
     */
    public static function updateVersionNumber(AutoZipConfig $autozip, $version_number) {
        //@TODO
    }

}
