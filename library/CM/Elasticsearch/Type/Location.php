<?php

class CM_Elasticsearch_Type_Location extends CM_Elasticsearch_Type_Abstract {

    const INDEX_NAME = 'location';

    protected $_mapping = array(
        'level'       => array('type' => 'integer', 'store' => 'yes'),
        'id'          => array('type' => 'integer', 'store' => 'yes'),
        'ids'         => array('type' => 'object', 'properties' => array(
            '1' => array('type' => 'integer'),
            '2' => array('type' => 'integer'),
            '3' => array('type' => 'integer'),
            '4' => array('type' => 'integer'),
        )),
        'name'        => array('type' => 'multi_field', 'fields' => array(
            'name'   => array('type' => 'string', 'analyzer' => 'lowercase'),
            'prefix' => array('type' => 'string', 'analyzer' => 'ngram_words'),
        )),
        'nameFull'    => array('type' => 'multi_field', 'fields' => array(
            'nameFull' => array('type' => 'string', 'analyzer' => 'lowercase'),
            'prefix'   => array('type' => 'string', 'analyzer' => 'ngram_words'),
        )),
        'coordinates' => array('type' => 'geo_point'),
    );

    protected $_indexParams = array(
        'index'    => array(
            'number_of_shards'   => 1,
            'number_of_replicas' => 0
        ),
        'analysis' => array(
            'analyzer' => array(
                'ngram_words' => array(
                    'type'      => 'custom',
                    'tokenizer' => 'standard',
                    'filter'    => ['lowercase', 'ngram_edge'],
                ),
                'lowercase'   => array(
                    'type'      => 'custom',
                    'tokenizer' => 'standard',
                    'filter'    => ['lowercase'],
                )
            ),
            'filter'   => array(
                'ngram_edge' => array(
                    'type'     => 'edgeNGram',
                    'min_gram' => 2,
                    'max_gram' => 20,
                ),
            )
        )
    );

    protected function _getQuery($ids = null, $limit = null) {
        $query = 'SELECT * FROM `cm_tmp_location`';
        if (($limit = (int) $limit) > 0) {
            $query .= ' LIMIT ' . $limit;
        }
        return $query;
    }

    protected function _getDocument(array $data) {
        $doc = new Elastica\Document(null, array(
            'level'    => (int) $data['level'],
            'id'       => (int) $data['id'],
            'ids'      => array(
                '1' => $data['1Id'],
                '2' => $data['2Id'],
                '3' => $data['3Id'],
                '4' => $data['4Id'],
            ),
            'name'     => $data['name'],
            'nameFull' => $data['nameFull'],
        ));

        if (isset($data['lat']) && isset($data['lon'])) {
            $doc->addGeoPoint('coordinates', (float) $data['lat'], (float) $data['lon']);
        }

        return $doc;
    }

    /**
     * @throws CM_Exception_NotImplemented
     */
    public static function updateItem($item) {
        throw new CM_Exception_NotImplemented();
    }
}
