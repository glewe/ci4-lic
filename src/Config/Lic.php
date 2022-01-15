<?php

namespace CI4\Lic\Config;

use CodeIgniter\Config\BaseConfig;

class Lic extends BaseConfig
{
    /**
     * ------------------------------------------------------------------------
     * The license server URL.
     * ------------------------------------------------------------------------
     *
     * This variable specifies the URL of your WordPress server where the license
     * manager plugin is installed on. This server will be contacted from your
     * application to activate or deactivate license keys.
     *
     * @var string
     */
    public $licenseServer = 'https://www.mylicenseserver.com';

    /**
     * ------------------------------------------------------------------------
     * The secret key.
     * ------------------------------------------------------------------------
     *
     * This variable must contain the value from the License Manager plugin 
     * Settings page on your WordPress site. The description of the field there
     * says "Secret Key for License Verification Requests".
     *
     * @var string
     */
    public $secretKey = '5e07r7d791df36.99585318';

    /**
     * ------------------------------------------------------------------------
     * The license item reference.
     * ------------------------------------------------------------------------
     *
     * This variable provides a reference label for the licenses which will be 
     * issued. Therefore you should enter something specific to describe what 
     * the licenses issued are pertaining to.
     *
     * @var string
     */
    public $itemReference = 'CI4 Application';

    /**
     * ------------------------------------------------------------------------
     * Days to expiry warning threshold.
     * ------------------------------------------------------------------------
     *
     * This variable defines the amount of days to expiry after which a warning
     * message is shown to the user.
     *
     * @var int
     */
    public $expiryWarning = 30;

    /**
     * ------------------------------------------------------------------------
     * Views used by Auth Controllers
     * ------------------------------------------------------------------------
     *
     * @var array
     */
    public $views = [

        'license'   => 'CI4\Lic\Views\license',

    ];

    /**
     * ------------------------------------------------------------------------
     * Layout for the views to extend
     * ------------------------------------------------------------------------
     *
     * @var string
     */
    public $viewLayout = 'CI4\Lic\Views\_layout';
}
