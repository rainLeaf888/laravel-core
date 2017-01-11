<?php
/**
 * @file 调用第三方包的基本查询构造
 * @author guojinli@yazuo.com
 */
namespace App\Common\Elastica;

use Elastica\Client as ElasticaClient;
use Elastica\Search;
use Elastica\QueryBuilder;
use Elastica\Query;
use Config;

class Client
{
    //索引
    private $index = null;
    //类型
    private $type  = null;

    /**
     * 构造函数初始化 index 和 type
     */
    public function __construct($index, $type)
    {
        $this->index = $index;
        $this->type  = $type;
    }

    /**
     * 获取elastica实例
     * @return Object
     */
    public function getClient()
    {
        $settings = Config::get('crm');
        $config   = $settings['elastica'];
        $client   = new ElasticaClient($config);
        
        return $client;
    }

    /**
     * 执行搜索结果
     * @return array
     */
    public function run($query, $exeType = 'search')
    {
        //获取连接对象
        $client    = $this->getClient();
        //指定需要查询的索引
        $index     = $client->getIndex($this->index);
        //指定需要查询的类型
        $type      = $index->getType($this->type);
        if ($exeType == 'count') {
            $result = $type->count($query);
        } else {
            //执行query
            $resultSet = $type->search($query);
            //格式化结果体
            $result    = $resultSet->getResponse()->getData();
        }
        return $result;
    }

    /**
     * 获取 query 构造器
     * @return Elastica\QueryBuilder\DSL\Query
     */
    public function getQuery()
    {
        $builder = new QueryBuilder();
        return $builder->query();
    }

    /**
     * 获取 filter 构造器
     * @return Elastica\QueryBuilder\DSL\Filter
     */
    public function getFilter()
    {
        $builder = new QueryBuilder();
        return $builder->filter();
    }

    /**
     * 获取 Aggregation 构造器
     * @return Elastica\QueryBuilder\DSL\Aggregation
     */
    public function getAgg()
    {
        $builder = new QueryBuilder();
        return $builder->aggregation();
    }

    /**
     * 获取 Suggest 构造器
     * @return Elastica\QueryBuilder\DSL\Suggest
     */
    public function getSuggest()
    {
        $builder = new QueryBuilder();
        return $builder->suggest();
    }
}
