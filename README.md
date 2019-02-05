# Large Network Management #

**Contributors:** [pbiron](https://profiles.wordpress.org/pbiron)  
**Tags:** multisite, admin  
**Requires at least:** 4.6  
**Tested up to:** 5.1-beta3  
**Requires PHP:** 5.6  
**Stable tag:** 0.1.1  
**License:** GPL-2.0-or-later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  
**Donate link:** https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Z6D97FA595WSU  

Optimize admin management of large networks

## Description ##

For Super Admins, all Wordpress admin screens can take **very** long to load when working with a multisite network with even 100 sites.  Additionally, the "My Sites" dropdown in the adminbar does not deal well with users who have access to more than ~30 sites.

This plugin tries to address that by:

1. short circuiting certain calls to [get_blogs_of_user()](https://developer.wordpress.org/reference/functions/get_blogs_of_user/) for Super Admins and "pretending" that they only have access to the main site
2. replacing the URL for the "My Sites" admin bar menu item for Super Admins to use the URL for the Network Admin > Sites page
    * if a Super Admin "manually" goes to the "My Sites" screen, they are automatically redirected to the Network Admin > Sites screen
3. for all non-Super Admin users, the sites they belong to are shown in alphabetical order by blogname
    * by default, Wordpress shows them in the order the sites were created (i.e., their blog_id)

## Installation ##

From your WordPress dashboard

1. Go to _Plugins > Add New_ and click on _Upload Plugin_
2. Upload the zip file
3. Activate the plugin

### Build from sources ###

1. clone the global repo to your local machine
2. install node.js and npm ([instructions](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm))
3. install composer ([instructions](https://getcomposer.org/download/))
4. run `npm update`
5. run `grunt build`
    * to build a new release zip
        * run `grunt release`

## To Do ##

* add ability to short circuit `get_blogs_of_user()` for any user that is a member of more than _n_ blogs, instead of just doing so for super_admins
* for users that aren't short circuited and have more than _m_ blogs, have the adminbar _My Sites_ list their blogs in sub-menus (e.g., **A-E**, **F-I**, etc)

## Changelog ##

### 0.1.1 ###

* hook Large_Network_Management_Plugin::setup() to 'init' instead of 'plugins_loaded' to avoid calling is_admin_bar_showing() before the query is run on the front-end
* Added 'wp_dashboard_quick_press()' to the list of "expensive" calls of get_blogs_of_user()

### 0.1.0 ###

* init commit
