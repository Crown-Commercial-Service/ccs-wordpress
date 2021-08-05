<?php

use Symfony\Component\Dotenv\Dotenv;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 */
class PluginCore
{

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name = 'CCS Salesforce Importer';

    /**
     * The current version of the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version = '0.1.0';


    /**
     * PluginCore constructor.
     */
    public function __construct()
    {
        $this->load_dependencies();
    }


    /**
     * Runs on activation
     */
    public static function activate() {
        //
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_dependencies()
    {
        $rootDir = __DIR__ . '/../../../../../';

        require_once($rootDir . 'vendor/autoload.php');
        $dotenv = new Dotenv(true);
        $dotenv->load($rootDir . '.env');
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     0.1.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.1.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }



}
