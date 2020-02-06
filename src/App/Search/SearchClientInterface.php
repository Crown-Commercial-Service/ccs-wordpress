<?php

namespace App\Search;

use App\Model\ModelInterface;
use Elastica\Mapping;
use Elastica\Query;

interface SearchClientInterface
{

    public function getIndexName(): string;

    public function createOrUpdateDocument(ModelInterface $model, array $relationships = null): void;

    public function getIndexMapping(): Mapping;
}
