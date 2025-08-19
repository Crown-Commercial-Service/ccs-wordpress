<?php 
include 'reports-api.php';
require_once('util.php');
/*
Plugin Name: WP Reports Plugin
Version: 3.0
Description: This plugin downlods csv reports for Authors, Frameworks, and Documents, based on selected parameters
*/


$WpReportsPlugin = new WpReportsPlugin();

if ( ! defined('ABSPATH')) exit; // exit if accessed directly

class WpReportsPlugin {
    
    private $util;

    function __construct() {
        $util = new Util();

        add_action('admin_menu', array($this, 'reportsMenu'));
        add_action('admin_post_authors_form', array($util, 'downloadAuthorsReport'));
        add_action('admin_post_frameworks_form', array($util, 'downloadFrameworksReport'));
        add_action('admin_post_documents_form', array($util, 'downloadDocumentsReport'));
    }

    function reportsMenu () {
        // Main Reports Page
        $page_browser_title = 'Reports'; // browser tab title
        $page_menu_title = 'Reports'; // title in admin sidebar
        $capability = 'manage_options'; // required user permissions
        $main_page_slug = 'download-reports'; // make Authors page the main page
        $callback = array($this, 'downloadReportsPage'); // make Authors page the main page
        $icon = 'dashicons-clipboard'; // icon that will appear in the admin menu
        $position = 75; // position in the admin menu
        
        $main_page_hook = add_menu_page($page_browser_title, $page_menu_title, $capability, $main_page_slug, $callback, $icon, $position);
    
        // Download Page
        $download_page_title = "Download Reports";
        $download_menu_title = "Download";
        $download_page_slug = "download-reports";
        $download_page_HTML = array($this, 'downloadReportsPage');
        $downloadPageHook = add_submenu_page($main_page_slug, $download_page_title, $download_menu_title, $capability, $download_page_slug, $download_page_HTML);
    
        // Load CSS
        add_action("load-{$main_page_hook}", array($this, 'reportsPluginAssets'));
    }

    /**
     *  Load CSS
     */
    function reportsPluginAssets() {
        wp_enqueue_style('reportsCss', plugin_dir_url(__FILE__) . 'styles.css');
    }

 
    /**
     * * DOWNLOAD PAGE HTML
    */
    function downloadReportsPage() { ?>
        <div>
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <div class='download-form'>
            <h2>Author Reports</h2>
                <div>
                    <label for="author_name"> Author Name </label>
                    <input type="checkbox" id="author_name" name="author_name" value="author_name">
                </div>
                <div>
                    <label for="last_login"> Last Accessed Wordpress </label>
                    <input type="checkbox" id="last_login" name="last_login" value="last_login" >
                </div>
                <div>
                    <label for="post_modified"> Last Update Date </label>
                    <input type="checkbox" id="post_modified" name="post_modified" value="post_modified" >
                </div>
                <div>
                    <label for="title"> Last Updated Framework </label>
                    <input type="checkbox" id="title" name="title" value="title" >
                </div>
                <div>
                    <input type="hidden" name="action" value="authors_form">
                </div>
                <div><button>Download Authors CSV</button></div>
                <div><button type="reset" value="Clear">Clear</button></div>
            </div>
        </form>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
        <div class='download-form'>
            <h2>Framework Reports</h2>
            <div>
                <label for="post_modified"> Last Modified </label>
                <input type="checkbox" id="post_modified" name="post_modified" value="post_modified">
            </div>
            <div>
                <label for="post_author"> Modified By </label>
                <input type="checkbox" id="post_author" name="post_author" value="post_author">
            </div>
            <div>
                <label for="last_published"> Last Update Date </label>
                <input type="checkbox" id="last_published" name="last_published" value="last_published">
            </div>
            <div>
                <label for="title"> Framework Title </label>
                <input type="checkbox" id="title" name="title" value="title">
            </div>
            <div>
                <label for="rm_number"> RM Number </label>
                <input type="checkbox" id="rm_number" name="rm_number" value="rm_number">
            </div>
            <div>
                <label for="lot_title"> Associated Lot Title </label>
                <input type="checkbox" id="lot_title" name="lot_title" value="lot_title">
            </div>
            <div>
                <label for="lot_id"> Associated Lot ID </label>
                <input type="checkbox" id="lot_id" name="lot_id" value="lot_id">
            </div>
            <div>
                <label for="doc_name"> Linked Document </label>
                <input type="checkbox" id="doc_name" name="doc_name" value="doc_name">
            </div>
            <div>
                <label for="doc_type"> Document Type </label>
                <input type="checkbox" id="doc_type" name="doc_type" value="doc_type">
            </div>
            <div>
                <input type="hidden" name="action" value="frameworks_form">
            </div>
            <div><button>Download Frameworks CSV</button></div>
            <div><button type="reset" value="Clear">Clear</button></div>
        </div>
        </form>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
        <div class='download-form'>
            <h2>Document Reports</h2>
            <div>
                <label for="document_name"> Document Title </label>
                <input type="checkbox" id="document_name" name="document_name" value="document_name">
            </div>
            <div>
                <label for="post_mime_type"> File Type </label>
                <input type="checkbox" id="post_mime_type" name="post_mime_type" value="post_mime_type">
            </div>
            <div>
                <label for="post_date"> Date Uploaded </label>
                <input type="checkbox" id="post_date" name="post_date" value="post_date">
            </div>
            <div>
                <label for="title"> Associated Framework </label>
                <input type="checkbox" id="title" name="title" value="title">
            </div>
            <div>
                <label for="author"> Author </label>
                <input type="checkbox" id="author" name="author" value="author">
            </div>
            <div>
                <input type="hidden" name="action" value="documents_form">
            </div>
            <div><button>Download Documents CSV</button></div>
            <div><button type="reset" value="Clear">Clear</button></div>
        </div>
        </form>
        <?php
    }

}


