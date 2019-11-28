<?php

namespace App\Search;

class FacetDataResolver {

    /**
     * @param array $facets
     * @return array|null
     */
    public function prepareFacetsForView(array $facets): ?array {
        $returnData = [];

        foreach ($facets as $name => $data)
        {
            if ($name == 'frameworks') {
                $returnData['frameworks'] = $this->prepareFrameworkFacetsForView($data);
            }
        }

        return $returnData;
    }

    /**
     * @param array $data
     * @return array|null
     */
    protected function prepareFrameworkFacetsForView(array $data): ?array {
        $returnData = [];

        $frameworks = $data['titles']['buckets'];
        
        foreach ($frameworks as $framework) {
            $returnData[$framework['key']] = ['title' => $framework['key'], 'doc_count' => $framework['doc_count'], 'rm_number' => $framework['rm_number']['buckets'][0]['key']];
        }

        ksort($returnData);

        return $returnData;
    }
}