<?php

namespace App\Search;

use App\Model\ModelInterface;
use App\Search\Mapping\FrameworkMapping;
use App\Search\Mapping\SupplierMapping;
use App\Search\FrameworkSearchClient;
use App\Search\SupplierSearchClient;
use Elastica\Mapping;
use Elastica\Reindex;
use Elastica\Client;

class ReindexSearchClient extends AbstractSearchClient
{
    public function reindex(string $indexName, array $analysis)
    {
        // call parent constructer so we can get access to elastica client methods
        parent::__construct();
        
        $oldIndex = $this->getIndex($indexName);
        $newIndex = $this->getIndex($indexName . '_temp');

        // create new temp index with new index settings to copy documents to
        $this->createNewIndex($indexName . '_temp', $analysis);
        
        // copy documents from old index to new index
        $reindexAPI = new Reindex($oldIndex, $newIndex);
        $reindexAPI->run();

        // delete old index
        $oldIndex->delete();

        // create new index with same name as old index
        $this->createNewIndex($indexName, $analysis);

        // copy documents from new temp index to old index(with the new data and settings)
        $reindexAPI = new Reindex($newIndex, $oldIndex);
        $reindexAPI->run();
        
        // delete new temp index
        $newIndex->delete();
    }

    public function createNewIndex(string $indexName, array $analysis)
    {
        $index = $this->getIndex($indexName);

        $index->create(['settings' => $analysis]);

        $index->setMapping($this->indexClient->getIndexMapping());
    }
}
