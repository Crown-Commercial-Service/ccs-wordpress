<?php

declare(strict_types=1);

namespace App\Services\S3Client;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class S3SitemapClient
{

    protected $bucket;
    protected $key;


    public function __construct()
    {
        $this->bucket = 'ccs-dev-wp-config';
        $this->key = 'sitemap';
    }


    public function get_s3_client()
    {
        $args = [
            'version' => 'latest',
            'region'  => 'eu-west-1',
        ];

        if (defined(getenv('AWS_ACCESS_KEY_ID')) && defined(getenv('AWS_SECRET_ACCESS_KEY'))) {
            $args['credentials'] = [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ];
        }

        return new \Aws\S3\S3Client($args);
    }

    public function get_s3_sitemap_metadata()
    {
        $s3Client = $this->get_s3_client();

        try {
            $metadata = $s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->key . '/' . 'sitemap.xml',
            ]);

            return [
                'exists'        => true,
                'last_modified' => $metadata['LastModified']->getTimestamp(),
                'size'          => $metadata['ContentLength'],
                'url'           => $s3Client->getObjectUrl($this->bucket, $this->key)
            ];
        } catch (AwsException $e) {
            return [
                'exists'        => false,
                'last_modified' => 'No file found on S3',
                'size'          => 0,
                'url'           => '#'
            ];
        }
    }

    public function get_s3_sitemap()
    {
        try {

            $result = $this->get_s3_client()->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->key . '/' . 'sitemap.xml',
            ]);
        } catch (AwsException $e) {
            return new WP_Error('s3_error', 'Failed to retrieve sitemap from S3', array('status' => 500));
        }
        return $result;
    }

    public function upload_user_xml_to_s3($file_path, $file_name)
    {
        $s3Client = $this->get_s3_client();

        try {
            $result = $s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->key . '/' . $file_name,
                'SourceFile' => $file_path,
                'ContentType' => 'application/xml',
            ]);
            return $result['ObjectURL'];
        } catch (AwsException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
