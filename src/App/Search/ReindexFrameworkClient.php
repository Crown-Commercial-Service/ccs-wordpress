<?php

namespace App\Search;

use App\Search\FrameworkSearchClient;

class ReindexFrameworkClient extends AbstractSearchClient
{
    /**
     * @var \App\Search\FrameworkSearchClient
     */
    protected $indexClient;

    // update index setting here
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

    public function __construct()
    {
        // initialise client
        $this->indexClient = new FrameworkSearchClient();
    }

    public function reindexFrameworks()
    {   
         // check to see if old index exists if not create it
        $this->indexClient->getIndexOrCreate();

        $this->reindex($this->indexClient->getQualifiedIndexName(), $this->analysis);
    }
}
