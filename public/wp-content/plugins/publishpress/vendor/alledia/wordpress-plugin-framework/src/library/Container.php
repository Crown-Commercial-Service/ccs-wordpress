<?php

namespace Allex;

use Allex\Module\Addons;
use Allex\Module\Assets;
use Allex\Module\Reviews;
use Allex\Module\Upgrade;

class Container extends \Pimple\Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        /**
         * @param $c
         *
         * @return string
         */
        $this['VERSION'] = function ($c) {
            return '0.6.0';
        };

        /**
         * @param $c
         *
         * @return mixed
         */
        $this['PLUGIN_BASENAME'] = function ($c) use ($values) {
            return $values['PLUGIN_BASENAME'];
        };

        /**
         * @param $c
         *
         * @return bool|string
         */
        $this['FRAMEWORK_BASE_PATH'] = function ($c) {
            // Added slashes to prevent issues in Windows machines, where the backslash where interpreted as escape char.
            $dir = str_replace('\\', '/', __DIR__);

            return realpath($dir . '/../');
        };

        /**
         * @param $c
         *
         * @return string
         */
        $this['TWIG_PATH'] = function ($c) {
            return $c['FRAMEWORK_BASE_PATH'] . '/twig';
        };

        /**
         * @param $c
         *
         * @return string
         */
        $this['ASSETS_BASE_URL'] = function ($c) {
            $abspath = ABSPATH;

            // Fix for windows machines
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $abspath = preg_replace('#/$#', '', $abspath);
            }

            // Add support for bedrock.
            if (class_exists('\\Roots\\Bedrock\\Autoloader')) {
                $abspath = str_replace('/wp/', '/', $abspath);
            }

            $path = str_replace($abspath, '', $c['FRAMEWORK_BASE_PATH']);
            $path = str_replace('\\', '/', $path);

            $siteUrl = get_site_url();
            if (class_exists('\\Roots\\Bedrock\\Autoloader')) {
                $siteUrl = str_replace('/wp', '', $siteUrl);
            }

            return $siteUrl . '/' . $path . '/assets';
        };

        /**
         * @param $c
         *
         * @return mixed
         */
        $this['PLUGIN_NAME'] = function ($c) {
            return str_replace('.php', '', basename($c['PLUGIN_BASENAME']));
        };

        /**
         * @param $c
         *
         * @return mixed
         */
        $this['PLUGIN_TITLE'] = function ($c) {
            if (is_admin()) {

                if ( ! function_exists('get_plugin_data')) {
                    require_once ABSPATH . '/wp-admin/includes/plugin.php';
                }

                $data = get_plugin_data(WP_PLUGIN_DIR . '/' . $c['PLUGIN_BASENAME']);

                return $data['Name'];
            }

            return $c['PLUGIN_NAME'];
        };

        /**
         * @param $c
         *
         * @return string
         */
        $this['EDD_API_URL'] = function ($c) use ($values) {
            return $values['EDD_API_URL'];
        };


        /**
         * @param $c
         *
         * @return string
         */
        $this['UPDATES_DOC_URL'] = function ($c) use ($values) {
            return $values['UPDATES_DOC_URL'];
        };


        /**
         * @param $c
         *
         * @return string
         */
        $this['PLUGIN_AUTHOR'] = function ($c) use ($values) {
            return $values['PLUGIN_AUTHOR'];
        };


        /**
         * @param $c
         *
         * @return Textdomain
         */
        $this['textdomain'] = function ($c) {
            return new Textdomain($c);
        };

        /**
         * @param $c
         *
         * @return \Twig_Loader_Filesystem
         */
        $this['twig_loader_filesystem'] = function ($c) {
            return new \Twig_Loader_Filesystem($c['TWIG_PATH']);
        };

        /**
         * @param $c
         *
         * @return \Twig_Environment
         */
        $this['twig'] = function ($c) {
            $twig = new \Twig_Environment(
                $c['twig_loader_filesystem'],
                // [ 'debug' => true ]
                []
            );

            // $twig->addExtension(new \Twig_Extension_Debug());

            return $twig;
        };

        /**
         * @param $c
         *
         * @return Upgrade
         */
        $this['module_upgrade'] = function ($c) {
            return new Upgrade($c);
        };

        /**
         * @param $c
         *
         * @return Assets
         */
        $this['module_assets'] = function ($c) {
            return new Assets($c);
        };

        /**
         * @param $c
         *
         * @return Reviews
         */
        $this['module_reviews'] = function ($c) {
            return new Reviews($c);
        };

        /**
         * @param $c
         *
         * @return Addons
         */
        $this['module_addons'] = function ($c) {
            return new Addons($c);
        };
    }
}
