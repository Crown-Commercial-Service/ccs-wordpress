<?php

namespace App\Search;

use App\Search\FrameworkSearchClient;

class ReindexFrameworkClient extends ReindexSearchClient
{
    /**
     * @var \App\Search\FrameworkSearchClient
     */
    protected $indexClient;

    public function __construct()
    {
        // initialise client
        $this->indexClient = new FrameworkSearchClient();
    }

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

    public function reindexFrameworks()
    {
        $this->reindex($this->indexClient->getQualifiedIndexName(), $this->analysis);
    }
}
