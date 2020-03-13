<?php
/**
 * elasticsearch 6.x版本封装，不适用于7.0以上
 */
namespace Hjp\Utils\Elasticsearch;

use Elasticsearch\ClientBuilder;
use Exception;

class Es
{
    private $client;

    /**
     * Es constructor.
     * @param array $hosts
     * @throws Exception
     */
    public function __construct(Array $hosts = [])
    {
        if (!$hosts) {
            throw new Exception('请配置hosts');
        }
        $this->client = ClientBuilder::create()->setHosts($hosts)->build();
    }

    /**
     * @param array $query
     * @return bool
     * @throws Exception
     */
    private function exists(Array $query = [])
    {
        if (!$query) return false;
        $exists = false;
        try {
            $res = $this->client->get($query);
            if ($res["found"]) return $res['_source'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $exists;
    }

    /**
     * @param String $index
     * @param String $id
     * @param array $P
     * @return bool|String
     * @throws Exception
     */
    public function add(String $index = '', String $id = '', $P = [])
    {
        if (!$index || !$id || !$P) return false;

        try {
            $exists = $this->exists(['index' => $index, 'type' => $index, 'id' => $id]);
            if ($exists) return false;

            $this->client->index([
                'index' => $index,
                'type' => $index,
                'body' => $P,
                'id' => $id,
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $id;
    }

    /**
     * @param String $index
     * @param array $where
     * @param array $data
     * @return bool|int
     * @throws Exception
     */
    public function update(String $index = '', Array $where = [], Array $data = [])
    {
        try {
            if (!$index || !$where || !$data || !isset($where['id'])) return false;
            $exists = $this->exists(['index' => $index, 'type' => $index, 'id' => $where['id']]);
            if (!$exists) return false;

            $res = $this->client->update([
                'index' => $index,
                'type' => $index,
                'id' => $where['id'],
                'body' => [
                    'doc' => $data
                ],
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        if ($res['result'] !== 'updated') {
            return false;
        }
        return 1;
    }

    /**
     * @param String $index
     * @param Int $page
     * @param Int $page_size
     * @param array $must
     * @param array $sort
     * @param array $fields
     * @param array $must_not
     * @return array
     * @throws Exception
     */
    public function list(String $index = '', Int $page = 0, Int $page_size = 0, Array $must = [], Array $sort = [], Array $fields = [], Array $must_not = [])
    {
        if (!$index) return [];
        $params = [
            'index' => $index,
            'type' => $index,
            'body' => [
                'query' => [
                    'bool'=> [
                        'must' => $must,
                        'must_not' => $must_not,
                    ],
                ],
            ]
        ];
        if ($page && $page_size) {
            $from = ($page - 1) * $page_size;
            $params['body']['from'] = $from < 0 ? 0 : $from;
            $params['body']['size'] = $page_size < 0 ? 20 : $page_size;
        }
        if ($sort){
            $params['body']['sort'] = $sort;
        }
        if ($fields){
            $params['body']['_source'] = $fields;
        }
        try {
            $response = $this->client->search($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        $hits = $response['hits'];
        if ( !$hits['hits'] ) return [];
        return array_map(function ($m) {
            return $m['_source'];
        },$hits['hits']);
    }

    /**
     * @param String $index
     * @param array $must
     * @param array $must_not
     * @return int
     * @throws Exception
     */
    public function count(String $index = '', Array $must = [], Array $must_not = [])
    {
        if (!$index) return 0;
        $params = [
            'index' => $index,
            'type' => $index,
            'body' => [
                'query' => [
                    'bool'=> [
                        'must' => $must,
                        'must_not' => $must_not,
                    ],

                ],
            ]
        ];
        try {
            $response = $this->client->count($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $response['count'];
    }

    /**
     * @param String $index
     * @param array $sum_fields
     * @param array $must
     * @param array $must_not
     * @return array
     * @throws Exception
     */
    public function sum(String $index = '', Array $sum_fields = [], Array $must = [], Array $must_not = [])
    {
        if (!$index || !$sum_fields) return [];
        $params = [
            'index' => $index,
            'type' => $index,
            'body' => [
                'query' => [
                    'bool'=> [
                        'must' => $must,
                        'must_not' => $must_not,
                    ],
                ],
                'aggs' => [],
                'from' => 0,
                'size' => 0,
            ],
        ];
        foreach ($sum_fields as $item) $params['body']['aggs'][$item] = ['sum' => ['field' => $item]];

        try {
            $response = $this->client->search($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        $result = [];
        foreach ($response['aggregations'] ?: [] as $k => $item) $result[$k] = $item['value'];
        return $result;
    }
}