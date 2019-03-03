<?php

namespace CCS\SFI;

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;

class SyncText
{

    /**
     * Array of fields to sync
     *
     * WordPress field name => Custom tables field name
     *
     * @var array
     */
    protected $fieldsToSync = [
        'frameworks' => [
            'framework_summary'     => 'summary',
            'framework_updates'     => 'document_updates',
            'framework_description' => 'description',
            'framework_benefits'    => 'benefits',
            'framework_how_to_buy'  => 'how_to_buy',
            'framework_keywords'    => 'keywords',
        ],
        'lots' => [
            'lot_description'       => 'description',
        ]
    ];

    /**
     * Sync data from WordPress to custom table
     *
     * @param string $type frameworks or lots
     * @param array $wordpressData
     * @param array $customTableData
     * @return int $count Number of items synced from WordPress to custom table
     * @throws \Exception
     */
    public function syncTextContent(string $type, array $wordpressData, array $customTableData): int
    {
        $count = 0;

        $valid = array_keys($this->fieldsToSync);
        if (!in_array($type, $valid)) {
            throw new \Exception(sprintf('Invalid type: %s', $type));
        }

        $ids = array_keys($wordpressData);
        foreach ($ids as $id) {
            $update = [];

            foreach ($this->fieldsToSync[$type] as $wpField => $customField) {

                $wpData = $wordpressData[$id][$wpField];
                if (empty($wpData)) {
                    continue;
                }

                if (!isset($customTableData[$id])) {
                    continue;
                }

                $customData = $customTableData[$id][$wpField];
                if ($wpData != $customData) {
                    $update[$customField] = $wpData;
                }
            }

            // Save data
            if (!empty($update)) {
                switch ($type) {
                    case 'frameworks':
                        $repository = new FrameworkRepository();
                        $repository->updateFields($update, 'wordpress_id', $id);
                        break;

                    case 'lots':
                        $repository = new LotRepository();
                        $repository->updateFields($update, 'wordpress_id', $id);
                        break;
                }
                $count++;
            }
        }

        return $count;
    }


    /**
     * Return array of all content for frameworks from WordPress
     *
     * @return array
     */
    public function getFrameworksFromWordPress(): array
    {
        $data = [];
        $args = [
            'post_type' => 'framework',
            'post_status' => 'any',
            'posts_per_page' => -1
        ];
        $loop = new \WP_Query( $args );
        while ($loop->have_posts()) {
            $loop->the_post();
            $id = get_the_ID();
            $itemData = [];

            foreach ($this->fieldsToSync['frameworks'] as $wpField => $customField) {
                $itemData[$wpField] = get_field($wpField);
            }
            $data[$id] = $itemData;
        }

        return $data;
    }

    /**
     * Return array of all content for frameworks from custom tables
     *
     * @return array
     */
    public function getFrameworksFromCustomTables(): array
    {
        $data = [];
        $repository = new FrameworkRepository();
        $results = $repository->findAll('SELECT * FROM ccs_frameworks');

        foreach ($results as $item) {
            $item = $item->toArray();
            $itemData = [];
            foreach ($this->fieldsToSync['frameworks'] as $wpField => $customField) {
                $itemData[$wpField] = $item[$customField];
            }
            $id = $item['wordpress_id'];
            $data[$id] = $itemData;
        }
        return $data;
    }


    /**
     * @todo Return array of all content for lots from WordPress
     *
     * @return array
     */
    public function getLotsFromWordPress(): array
    {
        $data = [];
        $args = [
            'post_type' => 'lot',
            'post_status' => 'any',
            'posts_per_page' => -1
        ];
        $loop = new \WP_Query( $args );
        while ($loop->have_posts()) {
            $loop->the_post();
            $id = get_the_ID();
            $itemData = [];

            foreach ($this->fieldsToSync['lots'] as $wpField => $customField) {
                $itemData[$wpField] = get_field($wpField);
            }
            $data[$id] = $itemData;
        }

        return $data;
    }

    /**
     * Return array of all content for lots from custom tables
     *
     * @return array
     */
    public function getLotsFromCustomTables(): array
    {
        $data = [];
        $repository = new FrameworkRepository();
        $results = $repository->findAll('SELECT * FROM ccs_lots');

        foreach ($results as $item) {
            $item = $item->toArray();
            $itemData = [];
            foreach ($this->fieldsToSync['lots'] as $wpField => $customField) {
                $itemData[$wpField] = $item[$customField];
            }
            $id = $item['wordpress_id'];
            $data[$id] = $itemData;
        }
        return $data;
    }



}