<?php

namespace App\Search;

class FacetDataResolver
{

    /**
     * @param array $facets
     * @return array|null
     */
    public function prepareFacetsForView(array $facets): ?array
    {
        $returnData = [];

        foreach ($facets as $name => $data) {
            if ($name == 'frameworks') {
                $returnData['frameworks'] = $this->prepareFrameworkFacetsForView($data);
            }

            if ($name == 'lots') {
                $returnData['lots'] = $this->prepareLotFacetsForView($data);
            }
        }

        return $returnData;
    }

    /**
     * @param array $data
     * @return array|null
     */
    protected function prepareFrameworkFacetsForView(array $data): ?array
    {
        $returnData = [];

        $frameworks = $data['titles']['buckets'];

        foreach ($frameworks as $framework) {
            $returnData[$framework['key']] = [
              'title'     => $framework['key'],
              'doc_count' => $framework['doc_count'],
              'rm_number' => strtoupper($framework['rm_number']['buckets'][0]['key'])
            ];
        }

        ksort($returnData);

        return $returnData;
    }


    /**
     * @param array $data
     * @return array|null
     */
    protected function prepareLotFacetsForView(array $data): ?array
    {
        $returnData = [];

        $lots = $data;

        /** @var \App\Model\Lot $lot */
        foreach ($lots as $lot) {
            $returnData[$lot->getLotNumber()] = [
              'title'       => $lot->getTitle(),
              'id'          => $lot->getId(),
              'description' => $lot->getDescription(),
              'lot_number'  => $lot->getLotNumber()
            ];
        }

        usort($returnData, function ($a, $b) {
            return strnatcasecmp($a['lot_number'], $b['lot_number']);
        });

        return $returnData;
    }
}
