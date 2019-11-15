<?php

namespace App\Search;

use App\Model\ModelInterface;
use Elastica\Query;

interface SearchClientInterface {

    public function getIndexName(): string;

    public function createOrUpdateDocument(ModelInterface $model, array $relationships = null): void;

}