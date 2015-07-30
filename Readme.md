# AutoZip PrestaShop Module - Automatic zip files update management

## Context

If you are selling softwares or modules/plugins/extensions/addons *(for any kind of platform, eg. WordPress, Drupal, Joomla, Magento, Dolibarr or whatever)*, [PrestaShop][4] is a good e-commerce solution for you, because it have the availability to [sell downloadable products][5].

Problem : For each new version of your software, you have to package & upload the source file in Prestashop back office, and ... it's boring !

## Purpose of the module

This module Allow you to automatically update your products downloads or attachments from an external source.

When using a GIT repository as source, the script will be able to auto detect & use the latest TAG
This Tag name will be stored as a product feature, allowing your customers to see the published current version number.

The script will be also able to use the credential keys of the account running the cron job (eg. SSH keys for GIt or SVN).

## Supported input sources

- GIT repositories (http / https / ssh)
- SVN repositories (http / https / ssh)
- HTTP servers (http / https)
- FTP servers (ftp)

## Prerequisites

The processing task uses system commands. You need the following CLI softwares installed on your server :

- git
- subversion
- wget
- zip
- and some usual commands such as "find" & "xargs".

You will need to schedule a cronjob (can be scheduled by a "web cron" tool, but running from a system crontab is highly recommended).

## Contributing

This modules is an open-source extensions to the PrestaShop e-commerce solution. 
Everyone is welcome and even encouraged to contribute with their own improvements.

### Requirements

Contributors must follow the following rules:

* Do a pull request
* Do not update the module's version number.
* Follow [the coding standards][1].

### Process in details

Contributors wishing to edit a module's files should follow the following process:

1. Create your GitHub account, if you do not have one already.
2. Fork the Prestashop-Module-AutoZip project to your GitHub account.
3. Clone your fork to your local machine in the ```/modules/autozip``` directory of your PrestaShop installation.
4. Create a branch in your local clone of the module for your changes.
5. Change the files in your branch. Be sure to follow [the coding standards][1]!
6. Push your changed branch to your fork in your GitHub account.
7. Create a pull request for your changes of the module's project. Be sure to follow [the commit message norm][2] in your pull request. If you need help to make a pull request, read the [Github help page about creating pull requests][3].
8. Wait for one of the developers either to include your change in the codebase, or to comment on possible improvements you should make to your code.

That's it: you have contributed to this open-source project! Congratulations!
[1]: http://doc.prestashop.com/display/PS16/Coding+Standards
[2]: http://doc.prestashop.com/display/PS16/How+to+write+a+commit+message
[3]: https://help.github.com/articles/using-pull-requests
[4]: https://www.prestashop.com/en/how-to-sell-digital-products-online
[5]: https://www.squirrelhosting.co.uk/hosting-blog/hosting-blog-info.php?id=75#16