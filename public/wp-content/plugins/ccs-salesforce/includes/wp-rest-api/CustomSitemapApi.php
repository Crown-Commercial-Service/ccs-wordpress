<?php

use App\Services\S3Client\S3SitemapClient;
use Aws\Exception\AwsException;

class CustomSitemapApi
{

    public function getSitemap()
    {

        $S3_Client = new S3SitemapClient();
        $s3_data = $S3_Client->getS3SitemapMetadata();

        if (!$s3_data['exists']) {
            return new WP_Error('no_sitemap', 'Sitemap file not found on S3', array('status' => 404));
        }

        try {
            $result = $S3_Client->getS3Sitemap();

            $content = (string) $result['Body'];

            return new WP_REST_Response([
                'filename' => 'sitemap.xml',
                'content' => base64_encode($content),
                'raw_xml' => $content,
                'size' => $s3_data['size'],
                'last_modified' => $s3_data['last_modified'],
                'url' => $s3_data['url']
            ], 200);
        } catch (AwsException $e) {
            return new WP_Error('s3_error', 'Failed to retrieve sitemap from S3', array('status' => 500));
        }
    }
}
