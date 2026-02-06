<?php

use App\Services\S3Client\S3SitemapClient;

if (! defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {
    add_menu_page('Sitemap Manager', 'Sitemap Upload', 'edit_posts', 'sitemap-manager', 'render_sitemap_uploader', 'dashicons-admin-site-alt3', 55);
});

function render_sitemap_uploader()
{
    if (!current_user_can('edit_posts')) return;

    $S3_Client = new S3SitemapClient();

    if (isset($_FILES['sitemap_file'])) {
        $file = $_FILES['sitemap_file'];
        $upload_result = $S3_Client->upload_user_xml_to_s3($file['tmp_name'], 'sitemap.xml');

        if ($upload_result) {
            echo '<div class="updated"><p>Sitemap synced to S3 successfully!</p></div>';
        } else {
            echo '<div class="error"><p>S3 Upload failed. Check AWS IAM Role permissions.</p></div>';
        }
    }

    $s3_data = $S3_Client->get_s3_sitemap_metadata();
    $last_modified = $s3_data['exists'] ? date("F d, Y @ H:i:s", $s3_data['last_modified']) : 'No file found';
    $file_size = $s3_data['exists'] ? size_format($s3_data['size']) : '0 KB';

?>
    <div class="wrap">
        <h1>Sitemap Manager</h1>
        <div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; max-width: 600px; margin-top: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h3>Current Sitemap Status</h3>
            <table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">
                <tr>
                    <td><strong>Last Updated:</strong></td>
                    <td><?php echo $last_modified; ?></td>
                </tr>
                <tr>
                    <td><strong>File Size:</strong></td>
                    <td><?php echo $file_size; ?></td>
                </tr>
            </table>

            <hr />

            <h3>Upload New Sitemap</h3>

            <form method="post" enctype="multipart/form-data" style="margin-top: 15px;">
                <input type="file" name="sitemap_file" accept=".xml" required>
                <div style="margin-top: 15px;">
                    <?php submit_button('Upload and Overwrite', 'primary', 'submit', false); ?>
                </div>
            </form>
        </div>
    </div>
<?php
}
