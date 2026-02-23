<?php

declare(strict_types=1);

namespace App\Services\S3Client;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class S3SitemapClient
{
    protected $bucket;
    protected $key;
    protected $env;


    public function __construct()
    {
        $this->env = getenv('CCS_FRONTEND_APP_ENV');
        $this->bucket = 'ccs-' . $this->env . '-wp-config' ?: 'ccs-dev-wp-config';

        if ($this->env === 'local') {
            $this->bucket = 'ccs-dev-wp-config';
        }

        $this->key = 'sitemap';
    }


    public function getS3Client()
    {
        $args = [
            'version' => 'latest',
            'region'  => ($this->env === 'dev' || $this->env === 'local') ? 'eu-west-1' : 'eu-west-2',
        ];

        $key = getenv('AWS_ACCESS_KEY_ID');
        $secret = getenv('AWS_SECRET_ACCESS_KEY');

        // This is for local development. In production, the IAM Role attached to the EC2 instance will handle authentication, so we don't want to provide credentials.
        if (!empty($key) && !empty($secret)) {
            $args['credentials'] = [
                'key'    => $key,
                'secret' => $secret,
            ];
        }

        return new \Aws\S3\S3Client($args);
    }

    public function getS3SitemapMetadata()
    {
        $s3Client = $this->getS3Client();

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

    public function getS3Sitemap()
    {
        try {
            $result = $this->getS3Client()->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->key . '/' . 'sitemap.xml',
            ]);
        } catch (AwsException $e) {
            return new WP_Error('s3_error', 'Failed to retrieve sitemap from S3', array('status' => 500));
        }
        return $result;
    }

    public function uploadUserXmlToS3($filePath, $fileName)
    {
        $s3Client = $this->getS3Client();

        try {
            $result = $s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->key . '/' . $fileName,
                'SourceFile' => $filePath,
                'ContentType' => 'application/xml',
            ]);
            return $result['ObjectURL'];
        } catch (AwsException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}