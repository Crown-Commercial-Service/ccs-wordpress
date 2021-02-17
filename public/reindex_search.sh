#!/bin/bash

# reindexes elasticsearch

# loads elastic suffix from .env file
ELASTIC_SUFFIX=$(grep ELASTIC_SUFFIX ../.env | xargs)
IFS='=' read -ra ELASTIC_SUFFIX <<< "$ELASTIC_SUFFIX"
ELASTIC_SUFFIX=${ELASTIC_SUFFIX[1]//$'\r'}

if [[ $ELASTIC_SUFFIX == "local" ]]; then
  ELASTIC_ENDPOINT="localhost:9200"
fi

if [[ $ELASTIC_SUFFIX == "dev" ]]; then
  ELASTIC_ENDPOINT="https://vpc-elasticsearch-ccs-dev-wvwrvhqdfoxbuhcdft7bk33dta.eu-west-1.es.amazonaws.com"
fi

if [[ $ELASTIC_SUFFIX == "uat" ]]; then
  ELASTIC_ENDPOINT="https://vpc-elasticsearch-ccs-uat-ebfgjzsrb2rdyxm6z3oy2mnohi.eu-west-2.es.amazonaws.com"
fi

if [[ $ELASTIC_SUFFIX == "prod" ]]; then
  ELASTIC_ENDPOINT="https://vpc-elasticsearch-ccs-prod-eur4vilhtjrxeumhbeqevtkcvi.eu-west-2.es.amazonaws.com"
fi

echo "Reindexing Frameworks"
curl -X PUT "${ELASTIC_ENDPOINT}/framework_${ELASTIC_SUFFIX}_temp?pretty" -H 'Content-Type: application/json' -d' {
  "settings": {
    "analysis": {
      "filter": {
        "english_stemmer": {
          "name": "english",
          "type": "stemmer"
        },
        "english_stop": {
          "type": "stop",
          "stopwords": "_english_"
        }
      },
      "analyzer": {
        "english_analyzer": {
          "filter": [
            "lowercase",
            "english_stemmer",
            "english_stop"
          ],
          "tokenizer": "standard"
        }
      }
    }
  },
  "mappings": {
    "properties": {
      "benefits": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "category": {
        "type": "keyword"
      },
      "description": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "end_date": {
        "type": "date"
      },
      "how_to_buy": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "id": {
        "type": "integer"
      },
      "keywords": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "lots": {
        "type": "nested",
        "properties": {
          "description": {
            "type": "text"
          },
          "title": {
            "type": "keyword"
          }
        }
      },
      "pillar": {
        "type": "keyword"
      },
      "published_status": {
        "type": "keyword"
      },
      "rm_number": {
        "type": "text",
        "fields": {
          "raw": {
            "type": "keyword"
          }
        },
        "fielddata": true
      },
      "rm_number_numerical": {
        "type": "keyword"
      },
      "salesforce_id": {
        "type": "keyword"
      },
      "start_date": {
        "type": "date"
      },
      "status": {
        "type": "keyword"
      },
      "summary": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "terms": {
        "type": "keyword"
      },
      "title": {
        "type": "text",
        "fields": {
          "raw": {
            "type": "keyword"
          }
        },
        "analyzer": "english_analyzer"
      },
      "type": {
        "type": "keyword"
      }
    }
  }
} ' &&

curl -X POST "${ELASTIC_ENDPOINT}/_reindex?pretty" -H 'Content-Type: application/json' -d' { "source": { "index": "framework_'${ELASTIC_SUFFIX}'" }, "dest": { "index": "framework_'${ELASTIC_SUFFIX}'_temp" } } ' &&

curl -X DELETE "${ELASTIC_ENDPOINT}/framework_${ELASTIC_SUFFIX}?pretty" &&

curl -X PUT "${ELASTIC_ENDPOINT}/framework_${ELASTIC_SUFFIX}?pretty" -H 'Content-Type: application/json' -d' {
  "settings": {
    "analysis": {
      "filter": {
        "english_stemmer": {
          "name": "english",
          "type": "stemmer"
        },
        "english_stop": {
          "type": "stop",
          "stopwords": "_english_"
        }
      },
      "analyzer": {
        "english_analyzer": {
          "filter": [
            "lowercase",
            "english_stemmer",
            "english_stop"
          ],
          "tokenizer": "standard"
        }
      }
    }
  },
  "mappings": {
    "properties": {
      "benefits": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "category": {
        "type": "keyword"
      },
      "description": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "end_date": {
        "type": "date"
      },
      "how_to_buy": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "id": {
        "type": "integer"
      },
      "keywords": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "lots": {
        "type": "nested",
        "properties": {
          "description": {
            "type": "text"
          },
          "title": {
            "type": "keyword"
          }
        }
      },
      "pillar": {
        "type": "keyword"
      },
      "published_status": {
        "type": "keyword"
      },
      "rm_number": {
        "type": "text",
        "fields": {
          "raw": {
            "type": "keyword"
          }
        },
        "fielddata": true
      },
      "rm_number_numerical": {
        "type": "keyword"
      },
      "salesforce_id": {
        "type": "keyword"
      },
      "start_date": {
        "type": "date"
      },
      "status": {
        "type": "keyword"
      },
      "summary": {
        "type": "text",
        "analyzer": "english_analyzer"
      },
      "terms": {
        "type": "keyword"
      },
      "title": {
        "type": "text",
        "fields": {
          "raw": {
            "type": "keyword"
          }
        },
        "analyzer": "english_analyzer"
      },
      "type": {
        "type": "keyword"
      }
    }
  }
} ' &&

curl -X POST "${ELASTIC_ENDPOINT}/_reindex?pretty" -H 'Content-Type: application/json' -d' { "source": { "index": "framework_'${ELASTIC_SUFFIX}'_temp" }, "dest": { "index": "framework_'${ELASTIC_SUFFIX}'" } } ' &&

curl -X DELETE "${ELASTIC_ENDPOINT}/framework_${ELASTIC_SUFFIX}_temp?pretty"

echo "Reindexing Supplier"
curl -X PUT "${ELASTIC_ENDPOINT}/supplier_${ELASTIC_SUFFIX}_temp?pretty" -H 'Content-Type: application/json' -d' {
  "settings": {
    "analysis": {
      "filter": {
        "english_stemmer": {
          "name": "english",
          "type": "stemmer"
        },
        "english_stop": {
          "type": "stop",
          "stopwords": "_english_"
        }
      },
      "analyzer": {
        "english_analyzer": {
          "filter": [
            "lowercase",
            "english_stemmer",
            "english_stop"
          ],
          "tokenizer": "standard"
        }
      }
    }
  },
  "mappings": {
    "properties": {
      "alternative_trading_names": {
        "type": "text"
      },
      "city": {
        "type": "text"
      },
      "duns_number": {
        "type": "keyword"
      },
      "encoded_name": {
        "type": "text"
      },
      "have_guarantor": {
        "type": "boolean"
      },
      "id": {
        "type": "integer"
      },
      "live_frameworks": {
        "type": "nested",
        "properties": {
          "end_date": {
            "type": "date"
          },
          "lot_ids": {
            "type": "keyword"
          },
          "rm_number": {
            "type": "text",
            "fielddata": true
          },
          "rm_number_numerical": {
            "type": "keyword"
          },
          "status": {
            "type": "keyword"
          },
          "title": {
            "type": "keyword"
          },
          "type": {
            "type": "keyword"
          }
        }
      },
      "name": {
        "type": "text",
        "fields": {
          "raw": {
            "type": "keyword"
          }
        },
        "analyzer": "english_analyzer"
      },
      "postcode": {
        "type": "text"
      },
      "salesforce_id": {
        "type": "keyword"
      },
      "trading_name": {
        "type": "text"
      }
    }
  }
} ' &&

curl -X POST "${ELASTIC_ENDPOINT}/_reindex?pretty" -H 'Content-Type: application/json' -d' { "source": { "index": "supplier_'${ELASTIC_SUFFIX}'" }, "dest": { "index": "supplier_'${ELASTIC_SUFFIX}'_temp" } } ' &&

curl -X DELETE "${ELASTIC_ENDPOINT}/supplier_${ELASTIC_SUFFIX}?pretty" &&

curl -X PUT "${ELASTIC_ENDPOINT}/supplier_${ELASTIC_SUFFIX}?pretty" -H 'Content-Type: application/json' -d' {
  "settings": {
    "analysis": {
      "filter": {
        "english_stemmer": {
          "name": "english",
          "type": "stemmer"
        },
        "english_stop": {
          "type": "stop",
          "stopwords": "_english_"
        }
      },
      "analyzer": {
        "english_analyzer": {
          "filter": [
            "lowercase",
            "english_stemmer",
            "english_stop"
          ],
          "tokenizer": "standard"
        }
      }
    }
  },
  "mappings": {
    "properties": {
      "alternative_trading_names": {
        "type": "text"
      },
      "city": {
        "type": "text"
      },
      "duns_number": {
        "type": "keyword"
      },
      "encoded_name": {
        "type": "text"
      },
      "have_guarantor": {
        "type": "boolean"
      },
      "id": {
        "type": "integer"
      },
      "live_frameworks": {
        "type": "nested",
        "properties": {
          "end_date": {
            "type": "date"
          },
          "lot_ids": {
            "type": "keyword"
          },
          "rm_number": {
            "type": "text",
            "fielddata": true
          },
          "rm_number_numerical": {
            "type": "keyword"
          },
          "status": {
            "type": "keyword"
          },
          "title": {
            "type": "keyword"
          },
          "type": {
            "type": "keyword"
          }
        }
      },
      "name": {
        "type": "text",
        "fields": {
          "raw": {
            "type": "keyword"
          }
        },
        "analyzer": "english_analyzer"
      },
      "postcode": {
        "type": "text"
      },
      "salesforce_id": {
        "type": "keyword"
      },
      "trading_name": {
        "type": "text"
      }
    }
  }
} ' &&

curl -X POST "${ELASTIC_ENDPOINT}/_reindex?pretty" -H 'Content-Type: application/json' -d'{"source": {"index": "supplier_'${ELASTIC_SUFFIX}'_temp"},"dest": {"index": "supplier_'${ELASTIC_SUFFIX}'"}}' &&

curl -X DELETE "${ELASTIC_ENDPOINT}/supplier_${ELASTIC_SUFFIX}_temp?pretty"
echo "Reindexing Complete"