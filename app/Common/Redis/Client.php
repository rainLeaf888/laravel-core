<?php
/**
 * @file Redis请求设置key
 */
namespace App\Common\Redis;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use PSRedis\Client as SentinelClient;
use PSRedis\MasterDiscovery;
use PSRedis\HAClient;
use Illuminate\Support\Facades\Config;

class Client
{
    //最大尝试次数
    private $maxRetry = 1;
    //当前redis 请求前缀
    private $prefix = '';
    //redis前缀数组
    private $prefixs = [];
    //Application 对象
    private $app;

    private $HAClient = null;

    public function __construct()
    {
        $settings = Config::get('crm');
        $this->prefixs = $settings['redisprefix'];
    }

    /**
     * 设置前缀
     * @param string $prefix
     */
    public function setPrefix($prefix = 'DEFAULT')
    {
        $this->prefix = strtoupper($prefix);
        return $this;
    }

    /**
     * 调用redis方法
     * @param  方法名
     * @param  参数
     * @return redis请求返回值
     */
    public function __call($method, $args)
    {
        $mixRet = false;
        //如果没有前缀，那么不能进行redis读写
        if ($this->prefix != '' && array_key_exists($this->prefix, $this->prefixs)) {
            //第一个参数永远是键
            $args[0] = $this->prefixs[$this->prefix] . $args[0];
            for ($i = 0; $i < $this->maxRetry; $i++) {
                try {
                    $mixRet = call_user_func_array([$this->getSentinel(), $method], $args);
                    break;
                } catch (\Exception $e) {
                    //如果写入报错，将错误写入日志
                    Log::error($e->getMessage() . $e->getTraceAsString());
                }
            }
        }

        return $mixRet;
    }

    /**
     * 获取sentinel配置，并获取可执行的redis
     * @return Object
     */
    public function getSentinel()
    {
        $settings = Config::get('database');
        $sentinel = $settings['sentinel'];
        $masterName = array_pull($sentinel, 'master');
        $masterDiscovery = new MasterDiscovery($masterName);
        if (!empty($sentinel)) {
            foreach ($sentinel as $key => $value) {
                $client = new SentinelClient($value['host'], $value['port']);
                $masterDiscovery->addSentinel($client);
            }
        }
        $this->HAClient = new HAClient($masterDiscovery);

        return $this->HAClient;
    }

    /**
     * 断开redis 服务器连接
     * @return mixed
     */
    public function quit()
    {
        if (!empty($this->HAClient)) {
            return $this->HAClient->quit();
        }
        return true;
    }
}
