<?php

namespace App\Search;

use App\Search\SupplierSearchClient;

class ReindexSupplierClient extends ReindexSearchClient
{
    /**
     * @var \App\Search\FrameworkSupplierClient
     */
    protected $indexName = 'supplier';

    public function __construct () 
    {
        // initialise client
        $this->indexClient = new SupplierSearchClient();
    }
    
    // update index settings here
    protected $analysis = [
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

    public function reindexSupplier () 
    {
        $this->reindex($this->indexClient->getQualifiedIndexName(), $this->analysis);
    }   
}