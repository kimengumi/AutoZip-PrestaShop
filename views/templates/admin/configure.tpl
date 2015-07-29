{*
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
*}

{if $display_panel}
    <div class="panel">
        <h3>
            <i class="icon icon-exclamation-sign"></i> {l s='Cron job scheduling' mod='autozip'}
        </h3>
    {/if}
    <p>
        <strong>{l s='Don\'t forget to schedule the cron job to update your zips' mod='autozip'}</strong>
    </p><p>
        <i>{l s='Url for Webcron tools' mod='autozip'}</i>
    </p><p>
        <a href="{$cron_url|escape:html:'UTF-8'}" target="blank">{$cron_url|escape:html:'UTF-8'}</a>
    </p><p>
        <i>{l s='Config line sample for command line / system cron' mod='autozip'}</i>
    </p>
    <pre>16  3   *   *   *   php {$cron_cli|escape:html:'UTF-8'}</pre>
    <p>
        {l s='As the cron will launch systems softwares,' mod='autozip'} 
        <strong>{l s='it is highly recommended to schedule the cron job via a system crontab.' mod='autozip'}</strong><br/>
        {l s='The script will be able to use the credential keys of the account running the cron job (eg. SSH keys for GIt or SVN).' mod='autozip'}

    </p>
    {if $display_panel}
    </div>
{/if}
