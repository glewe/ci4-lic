# CI4-Lic
[![PHP](https://img.shields.io/badge/Language-PHP-8892BF.svg)](https://www.php.net/)
[![Bootstrap 5](https://img.shields.io/badge/Styles-Bootstrap%205-7952b3.svg)](https://www.getbootstrap.com/)
[![Font Awesome](https://img.shields.io/badge/Icons-Font%20Awesome%205-1e7cd6.svg)](https://www.fontawesome.com/)
[![Maintained](https://img.shields.io/badge/Maintained-yes-009900.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)

CI4-Lic is a software license manager modul for Codeigniter 4, connecting to WordPress license server based on [Software License Manager Plugin](https://wordpress.org/plugins/software-license-manager/).

## Requirements

- PHP 7.3+, 8.0+ (Attention: PHP 8.1 not supported yet by CI 4 as of 2022-01-01)
- CodeIgniter 4.0.4+

## Features

- activate/deactivate a license key
- register/deregister a domain for a license key
- display license information

## Installation

### Install Sofftware License Manager on your WordPress site

I assume that you have a WordPress site installed on which you want to manage your licenses. I recommend to install a separate WordPress instance just for that purpose. 
Connectivity between your CI4 application and that server is required.

- Install the Software License Manager Plugin on that server
- Configure the plugin, e.g. setting the secret key for validation

### Install Codeigniter

Install an appstarter project with Codigniter 4 as described [here](https://codeigniter.com/user_guide/installation/installing_composer.html).

Make sure your app and database is configured right and runs fine showing the Codigniter 4 welcome page.

### Download CI4-Lic

Download the CI4-Lic archive from this repo here.

### Copy CI4-Lic to your ThirdParty folder

*Note: CI4-Lic is not available as a Composer package yet. It works from your ThirdParty folder.*

Unzip the CI4-Lic archive and copy the 'lewe' directory to your **\app\ThirdParty** folder in your Codeigniter project.
You should see this tree section then:
```
project-root
- app
  - ThirdParty
    - lewe
      - ci4-lic
        - src
```
### Configuration

1. Add the Psr4 path in your **app/Config/Autoload.php** file as follows:
```php
public $psr4 = [
    APP_NAMESPACE  => APPPATH, // For custom app namespace
    'Config'       => APPPATH . 'Config',
    'CI4\Lic'      => APPPATH . 'ThirdParty/lewe/ci4-lic/src',
];
```

2. For easier access the the helper functions, add the helper names 'lic' and 'bs5' to your **app/Controller/BaseCopntroller.php**. It might look like this (do not reomve existing helper entries):
```php
protected $helpers = ['bs5', 'lic', 'session'];
```

3. Change the details for your license server and secret key in **lewe/ci4-lic/src/Config/Lic.php**. Example:
```php
public $licenseServer = 'https://www.mylicenseserver.com';
...
public $secretKey = '5e07r7d791df36.99585318';
```

### Routes

The CI4-Lic routes are defined in **lewe/ci4-lic/src/Config/Routes.php**. Copy the routes group from there to your
**app/Config/Routes.php** file, right after the 'Route Definitions' header comment.
```php
/*
* --------------------------------------------------------------------
* Route Definitions
* --------------------------------------------------------------------
*/
//
// CI4-Lic Routes
//
$routes->group('', ['namespace' => 'CI4\Lic\Src\Controllers'], function ($routes) {

    $routes->match(['get', 'post'], 'license', 'LicController::index', ['as' => 'license']);
    
    ...

});
```

### Views

The views that come with CI4-Lic are based on [Bootstrap 5](http://getbootstrap.com/) and [Font Awesome 5](https://fontawesome.com/).

If you like to use your own view you can override them editing the `$views` array in
**lewe/ci4-lic/src/Config/Lic.php**:
```php
public $views = [

        'license'   => 'CI4\Lic\Views\license',

];
```

### Database

This library does not need any database records. However, you may want to save the license key in a database at a later point.

Currently, this library uses a hard-coded key for testing purposes in the `readKey()` function of the license controller. Replace that code 
for reading the key from your database when you're ready.
**lewe/ci4-lic/src/Controllers/LicController.php**:
```php
// ------------------------------------------------------------------------
/**
 * Reads the license key from the database.
 */
function readKey()
{
    //
    // Most probably your license key will be in your database together with
    // other settings of your application. Add the proper code to read it
    // here e.g. from a database with this pseudo code
    // $this->key = read_key_from_db();
    //
    $this->key = 'CI4-1781ydy363738';
}
```
For saving a license key in your database, use the `saveKey()` function of the license controller. Add your code there.
**lewe/ci4-lic/src/Controllers/LicController.php**:
```php
// ------------------------------------------------------------------------
/**
 * Saves the license key to the database.
 * 
 * @param string $value The license key
 */
function saveKey($value)
{
    //
    // You may want to use this method to save the license key elsewhere
    // e.g. to a database with this pseudo code
    // save_key_to_db($this->key);
    //
}
```

### Run Application

Start your browser and navigate to your public directory. Use the menu to check out the views that come with
CI4-Lic.

## Helper Functions (Lic)

In addition to the helper functions that come with Myth-Auth, CI4-Lic provides these:

**dnd()**

* Function: Dump'n'Die. Returns a preformatted output of objects and variables.
* Parameters: Variable/Object, Switch to die after output or not
* Returns: Preformatted output

## Helper Functions (Bootstrap 5)

In order to create Bootstrap objects quicker and to avoid duplicating code in views, these helper functions are
provided:

**bs5_alert()**

* Function: Creates a Bootstrap 5 alert box.
* Parameters: Array with alert box details.
* Returns: HTML

**bs5_cardheader()**

* Function: Creates a Bootstrap card header.
* Parameters: Array with card header details.
* Returns: HTML

**bs5_formrow()**

* Function: Creates a two-column form field div (text, email, select, password).
* Parameters: Array with form field details.
* Returns: HTML

**bs5_modal()**

* Function: Creates a modal dialog.
* Parameters: Array with modal dialog details.
* Returns: HTML

**bs5_searchform()**

* Function: Creates a search form field.
* Parameters: Array with search form details.
* Returns: HTML

**bs5_toast()**

* Function: Creates a Bootstrap toast.
* Parameters: Array with toast details.
* Returns: HTML

## Disclaimer

The CI4-Lic library is not perfect. It may very well contain bugs or things that can be done better. If you stumble upon such things, let me know.
Otherwise I hope the library will help you. Feel free to change anything to meet the requirements in your environment.

Enjoy,
George Lewe
