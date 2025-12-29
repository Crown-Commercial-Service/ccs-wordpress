<?php
/**
 * Plugin Name:     CCS MDM Importer
 * Description:     Imports required objects from MDM API into Wordpress
 * Author:          Chee Ng
 * Text Domain:     ccs-dmd-import
 * Version:         1.0
 */

// Abort if this file is called directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP-CLI commands if running from WP-CLI.
if ( defined('WP_CLI') && WP_CLI ) {
    require __DIR__ . '/includes/cli-commands.php';
    require __DIR__ . '/includes/SyncText.php';
    require __DIR__ . '/includes/dbManager.php';
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('PluginCore', 'activate'));
register_deactivation_hook(__FILE__, array('PluginCore', 'deactivate'));
