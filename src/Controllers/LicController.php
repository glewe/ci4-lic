<?php

namespace CI4\Lic\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;

use CI4\Lic\Config\Lic as LicConfig;

use App\Controllers\BaseController;

class LicController extends BaseController
{
    /**
     * ------------------------------------------------------------------------
     * The license key.
     * ------------------------------------------------------------------------
     *
     * Private variable holding the linces key itself.
     * Set with setKey(), read with getKey() method.
     *
     * @var string
     */
    private $key = '';

    /**
     * ------------------------------------------------------------------------
     * The license details.
     * ------------------------------------------------------------------------
     *
     * JSON reponse from the license server.
     *
     * @var JSON
     */
    public $details;

    /**
     * ------------------------------------------------------------------------
     * The client domain.
     * ------------------------------------------------------------------------
     *
     * The SERVER_NAME (domain) that this application runs on. The domain is
     * registered with the license on the license server.
     *
     * @var string
     */
    private $domain;

    /**
     * ------------------------------------------------------------------------
     * The Lic config class.
     * ------------------------------------------------------------------------
     *
     * JSON reponse from the license server.
     *
     * @var LicConfig
     */
    protected $config;

    //-------------------------------------------------------------------------
    /**
     */
    public function __construct($key = '')
    {
        $this->config = config('Lic');
        $this->domain = $_SERVER['SERVER_NAME'];
        $this->key    = $key;
    }

    // -------------------------------------------------------------------------
    /**
     * Handles the license page.
     *
     * @return void
     */
    public function index()
    {
        //
        // Get the license key
        //
        $this->load($this->readKey());
        // dnd($this->details);

        $data = [
            'config'  => $this->config,
            'L'       => $this,
        ];

        if ($this->request->getMethod() === 'post') {
            //
            // A form was submitted. Let's see what it was...
            //
            if (array_key_exists('btn_activate', $this->request->getPost())) {

                $this->activate();
                return redirect()->route('license');
            } else if (array_key_exists('btn_register', $this->request->getPost())) {

                $this->activate();
                return redirect()->route('license');
            } else if (array_key_exists('btn_deregister', $this->request->getPost())) {

                $this->deactivate();
                return redirect()->route('license');
            }
        }

        return $this->_render($this->config->views['license'], $data);
    }

    //-------------------------------------------------------------------------
    /**
     * Activates a license key (and registers the domain thet the request is
     * coming from).
     *
     * @return JSON
     */
    public function activate()
    {
        $parms = array(
            'slm_action' => 'slm_activate',
            'secret_key' => $this->config->secretKey,
            'license_key' => $this->key,
            'registered_domain' => $this->domain,
            'item_reference' => urlencode($this->config->itemReference),
        );

        $response = $this->callAPI('GET', $this->config->licenseServer, $parms);

        if (!$response) {
            return redirect()->route('license')->with('error', lang('Lic.alert.api_error'));
        }

        $response = json_decode((string)$response);

        if ($response->result == 'error') {
            return redirect()->route('license')->with('error', $response->message);
        }

        return $response;
    }

    // ------------------------------------------------------------------------
    /**
     * License server API call.
     *
     * @param string $method  POST, PUT, GET, ...
     * @param string $url     API host URL
     * @param array  $data    URL paramater: array("param" => "value") ==> index.php?param=value
     * @return JSON
     */
    function callAPI($method, $url, $data = false)
    {
        $curl = curl_init();

        switch (strtoupper($method)) {

            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;

            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        // Optional Debugging:
        // $query = LICENSE_SERVER . '?slm_action=' . $data['slm_action'] . '&amp;secret_key=' . $data['secret_key'] . '&amp;license_key=' . $data['license_key'] . '&amp;registered_domain=' . $data['registered_domain'] . '&amp;item_reference=' . $data['item_reference'];
        // dnd($query);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    // ------------------------------------------------------------------------
    /**
     * Deactivate license.
     * 
     * Deregisters the domain the request is coming from.
     *
     * @return JSON
     */
    function deactivate()
    {
        $parms = array(
            'slm_action' => 'slm_deactivate',
            'secret_key' => $this->config->secretKey,
            'license_key' => $this->key,
            'registered_domain' => $this->domain,
            'item_reference' => urlencode($this->config->itemReference),
        );

        $response = $this->callAPI('GET', $this->config->licenseServer, $parms);

        if (!$response) {
            return redirect()->route('license')->with('error', lang('Lic.alert.api_error'));
        }

        $response = json_decode((string)$response);

        if ($response->result == 'error') {
            return redirect()->route('license')->with('error', $response->message);
        }

        return $response;
    }

    // ------------------------------------------------------------------------
    /**
     * Returns the days until expiry.
     *
     * @return integer
     */
    function daysToExpiry()
    {
        $todayDate = new Time('now');
        $expiryDate = new Time($this->details->date_expiry);
        $daysToExpiry = $todayDate->diff($expiryDate);

        return intval($daysToExpiry->format('%R%a'));
    }

    // ------------------------------------------------------------------------
    /**
     * Checks whether the current domain is registered.
     *
     * @return boolean
     */
    function domainRegistered()
    {
        if (count($this->details->registered_domains)) {
            foreach ($this->details->registered_domains as $domain) {
                if ($domain->registered_domain == $this->domain) return true;
            }
            return false;
        } else {
            return false;
        }
    }

    // ------------------------------------------------------------------------
    /**
     * Returns an alert message when the expiry threshold is reached.
     *
     * @return string
     */
    function expiryWarning()
    {
        $html = '';
        if ($this->daysToExpiry() <= $this->config->expiryWarning) {
            $html = bs5_toast($data = [
                'title'         => lang('Lic.expiringsoon'),
                'time'          => date('Y-m-d H:i'),
                'style'         => 'warning',
                'body'          => lang('Lic.expiringsoon_subject', [$this->daysToExpiry()]) . '<br>' . lang('Lic.expiringsoon_help'),
                'delay'         => 5000,
                'custom_style'  => false,
            ]);
        }

        return $html;
    }

    // ------------------------------------------------------------------------
    /**
     * Reads the class license key.
     *
     * @return string
     */
    function getKey()
    {
        return $this->key;
    }

    // ------------------------------------------------------------------------
    /**
     * Loads the license information from license server.
     *
     * @return JSON Saved in $this->details
     */
    function load()
    {
        $parms = array(
            'slm_action' => 'slm_check',
            'secret_key' => $this->config->secretKey,
            'license_key' => $this->key,
        );

        $response = $this->callAPI('GET', $this->config->licenseServer, $parms);

        if (!$response) {
            return redirect()->route('license')->with('error', lang('Lic.alert.api_error'));
        }

        $response = json_decode((string)$response);
        // dnd($response);

        if ($response->result == 'error') {
            return redirect()->route('license')->with('error', $response->message);
        }

        $this->details = $response;
    }

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
        $this->key = 'CI4-61df038a2d0cb';
    }

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

    // ------------------------------------------------------------------------
    /**
     * Sets the class license key.
     *
     * @param string $key The license key
     */
    function setKey($key)
    {
        $this->key = $key;
    }

    // ------------------------------------------------------------------------
    /**
     * Creates a table with license details and displays it inside a Bootstrap
     * alert box. This method assumes that your application uses Bootstrap 5.
     *
     * @param    object    $data    License information array
     * @return   string    HTML
     */
    function show($data, $showDetails = false)
    {
        if (isset($data->result) && $data->result == "error") {

            $alert['type'] = 'danger';
            $alert['title'] = lang('Lic.invalid');
            $alert['subject'] = lang('Lic.invalid_subject');
            $alert['text'] = lang('Lic.invalid_text');
            $alert['help'] = lang('Lic.invalid_help');
            $details = "";
        } else {

            $domains = "";
            if (count($data->registered_domains)) {
                foreach ($data->registered_domains as $domain) {
                    $domains .= $domain->registered_domain . ', ';
                }
                $domains = substr($domains, 0, -2); // Remove last comma and blank
            }
            $daysleft = "";
            if ($daysToExpiry = $this->daysToExpiry()) {
                $daysleft = " (" . $daysToExpiry . " " . lang('Lic.daysleft') . ")";
            }

            switch ($this->status()) {
                case "active":
                    $title = lang('Lic.active');
                    $alert['type'] = 'success';
                    $alert['title'] = $title . '<span class="btn btn-' . $alert['type'] . ' btn-sm" style="margin-left:16px;">' . ucfirst($data->status) . '</span>';
                    $alert['subject'] = lang('Lic.active_subject');
                    $alert['text'] = '';
                    $alert['help'] = '';
                    break;

                case "expired":
                    $title = lang('Lic.expired');
                    $alert['type'] = 'warning';
                    $alert['title'] = $title . '<span class="btn btn-' . $alert['type'] . ' btn-sm" style="margin-left:16px;">' . ucfirst($data->status) . '</span>';
                    $alert['subject'] = lang('Lic.expired_subject');
                    $alert['help'] = lang('Lic.expired_help');
                    break;

                case "blocked":
                    $alert['type'] = 'warning';
                    $title = lang('Lic.blocked');
                    $alert['title'] = $title . '<span class="btn btn-' . $alert['type'] . ' btn-sm" style="margin-left:16px;">' . ucfirst($data->status) . '</span>';
                    $alert['subject'] = lang('Lic.blocked_subject');
                    $alert['text'] = '';
                    $alert['help'] = lang('Lic.blocked_help');
                    break;

                case "pending":
                    $alert['type'] = 'warning';
                    $title = lang('Lic.pending');
                    $alert['title'] = $title . '<span class="btn btn-' . $alert['type'] . ' btn-sm" style="margin-left:16px;">' . ucfirst($data->status) . '</span>';
                    $alert['subject'] = lang('Lic.pending_subject');
                    $alert['text'] = '';
                    $alert['help'] = lang('Lic.pending_help');
                    break;

                case "unregistered":
                    $title = lang('Lic.active');
                    $alert['type'] = 'warning';
                    $alert['title'] = $title . '<span class="btn btn-' . $alert['type'] . ' btn-sm" style="margin-left:16px;">' . ucfirst($data->status) . '</span>';
                    $alert['subject'] = lang('Lic.active_unregistered_subject');
                    $alert['text'] = '';
                    $alert['help'] = '';
                    break;
            }
        }

        $details = "
        <table class=\"table table-hover\">
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.product') . ":</th><td>" . $data->product_ref . "</td></tr>
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.key') . ":</th><td>" . $data->license_key . "</td></tr>
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.name') . ":</th><td>" . $data->first_name . " " . $data->last_name . "</td></tr>
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.email') . ":</th><td>" . $data->email . "</td></tr>
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.company') . ":</th><td>" . $data->company_name . "</td></tr>
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.date_created') . ":</th><td>" . $data->date_created . "</td></tr>
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.date_renewed') . ":</th><td>" . $data->date_renewed . "</td></tr>
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.date_expiry') . ":</th><td>" . $data->date_expiry . $daysleft . "</td></tr>
            <tr class=\"table-" . $alert['type'] . "\"><th>" . lang('Lic.registered_domains') . ":</th><td>" . $domains . "</td></tr>
        </table>";

        $alertBox = bs5_alert($data = [
            'type' => $alert['type'],
            'icon'  => '',
            'title' => $alert['title'],
            'subject' => $alert['subject'],
            'text' => $alert['text'],
            'help' => (strlen($alert['help']) ? "<p><i>" . $alert['help'] . "</i></p>" : "") . (($showDetails) ? $details : ''),
            'dismissable' => false,
        ]);

        return $alertBox;
    }

    // ------------------------------------------------------------------------
    /**
     * Get license status.
     *
     * @return string  active/blocked/invalid/expired/pending/unregistered
     */
    function status()
    {
        if ($this->details->result == 'error') return "invalid";

        switch ($this->details->status) {

            case "active":
                if (!$this->domainRegistered()) return 'unregistered';
                return 'active';
                break;

            case "expired":
                return 'expired';
                break;

            case "blocked":
                return 'blocked';
                break;

            case "pending":
                return 'pending';
                break;
        }
    }

    //-------------------------------------------------------------------------
    /**
     * Render View.
     *
     * @param string  $view
     * @param array   $data
     *
     * @return view
     */
    protected function _render(string $view, array $data = [])
    {
        return view($view, $data);
    }

    //-------------------------------------------------------------------------
    /**
     * Displays the Lewe Auth welcome page.
     */
    public function welcome()
    {
        return $this->_render($this->config->views['welcome'], ['config' => $this->config]);
    }
}
