<?php

namespace App\Search;

use App\Model\ModelInterface;
use App\Search\Mapping\FrameworkMapping;
use App\Search\Mapping\SupplierMapping;
use App\Search\FrameworkSearchClient;
use App\Search\SupplierSearchClient;
use Elastica\Document;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;
use Elastica\Reindex;
use Elastica\Client;
use Elastica\Index;

class ReindexSearchClient extends AbstractSearchClient
{
    /**
     * @var Elastica\Client
     */
    protected $elasticaClient;

    protected $indexClient;


    public function __construct(string $indexName)
    {

        // initialise frameworks or supplier

        if ($indexName == 'frameworks') {
            $this->indexClient = new FrameworkSearchClient();
        }

        else if ($indexName == 'supplier') {
            $this->indexClient = new SupplierSearchClient();
        } else {
            throw new \Exception('Please set a valid index name.');
        }

        // config for elastica client
        $config = [
            'transport' => getenv('ELASTIC_TRANSPORT'),
            'host'      => getenv('ELASTIC_HOST'),
            'port'      => getenv('ELASTIC_PORT'),
          ];

        // initialise Elastica client
          $this->elasticaClient = new Client($config);
    }


    /**
     * reindexes elasticsearch index
     */

    public function reindex () 
    {
        $oldIndex = $this->elasticaClient->getIndex($this->indexClient->getQualifiedIndexName());
        $newIndex = $this->elasticaClient->getIndex($this->indexClient->getQualifiedIndexName() . '_v1');

        // create new temp index with new index settings to copy documents to
        $this->createNewIndex($this->indexClient->getQualifiedIndexName() . '_v1');
        
        // copy documents from old index to new index
        $reindexAPI = new Reindex($oldIndex, $newIndex);
        $reindexAPI->run();

        // delete old index
        $oldIndex->delete();

        // create new index with same name as old index
        $this->createNewIndex($this->indexClient->getQualifiedIndexName());

        // copy documents from new temp index to old index(with the new data and settings)
        $reindexAPI = new Reindex($newIndex, $oldIndex);
        $reindexAPI->run();
        
        // delete new temp index
        $newIndex->delete();
        
        
    }

    public function createNewIndex (string $indexName) 
    {
        $index = $this->elasticaClient->getIndex($indexName);

        $analysis = [
          'analysis' => array(
            'analyzer' => array(
              'english_analyzer' => array(
                'tokenizer' => 'standard',
                'filter'    => array('lowercase', 'english_stemmer', 'english_stop'),
              ),
            ),
            'filter'   => array(
              'english_stemmer' => array(
                'type' => 'stemmer',
                'name' => 'english'
              ),
              'english_stop' => array(
                'type' => 'stop',
                'stopwords' => '_english_'
              )
            )
          )
        ];

        $index->create(['settings' => $analysis]);

        $index->setMapping($this->indexClient->getIndexMapping());
    }
}