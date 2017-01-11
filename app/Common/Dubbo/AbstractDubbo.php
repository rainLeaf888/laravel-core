<?php
/**
 * 调用dubbo接口的基类
 */
namespace App\Common\Dubbo;

use App\Common\Facades\YzZookeeper;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Log;

class AbstractDubbo
{
    //定义一个键，根据该键获取到相应的dubbo地址，非常重要
    protected $serviceKey;

    //解析后的dubbo接口地址
    protected $wsdl     = '';

    //dubbo接口汇总
    protected $wsdlMaps = [];

    /**
     * 获取dubbo具体接口
     * @param  string $parseUrl 需要解析的字符串
     * @return string 解析后的地址
     */
    private function getWsdlUri($parseUrl)
    {
        $scheme = $host = $port = $path = '';
        if (isset($parseUrl['scheme'])) {
            $scheme = $parseUrl['scheme'];
        }
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        }
        if (isset($parseUrl['port'])) {
            $port = $parseUrl['port'];
        }
        if (isset($parseUrl['path'])) {
            $path = $parseUrl['path'];
        }

        return $scheme .'://'. $host . ':'.$port . $path . '?wsdl';
    }
    
    /**
     * 返回指定key的dubbo接口地址
     * @return string
     */
    public function getWsdl()
    {
        Log::info('dubbo key为 ' . $this->serviceKey);
        if (extension_loaded('zookeeper')) {
            //获取该key提供的所有服务,我们只取带有webservice字样的
            $providers         = YzZookeeper::getChildren('/dubbo/'.$this->serviceKey.'/providers');
            $providersHashKeys = array_values($providers);
            foreach ($providersHashKeys as $providersHashKey) {
                $parsedUrl = parse_url(urldecode($providersHashKey));
                if ($parsedUrl['scheme'] == 'webservice') {
                    $parsedUrl['scheme'] = 'http';
                    $this->wsdlMaps[$this->serviceKey][] = $this->getWsdlUri($parsedUrl);
                }
            }
            //从数组中取出我们需要的dubbo接口地址
            if (isset($this->wsdlMaps[$this->serviceKey])) {
                $this->wsdl = $this->wsdlMaps[$this->serviceKey][0];
            } else {
                throw new BusinessException('服务地址没有找到！');
            }
        } elseif (config('crm.dubbo.'.substr(strrchr($this->serviceKey, '.'), 1), '')) {
            //先判断本地文件是否存在，纯粹是为了给本地调试用的
            $this->wsdlMaps[$this->serviceKey][] = config('crm.dubbo.'.substr(strrchr($this->serviceKey, '.'), 1));
            $this->wsdl = $this->wsdlMaps[$this->serviceKey][0];
        } else {
            throw new BusinessException('WDSL地址没有找到！');
        }

        return $this->wsdl;
    }

    /**
     * 设置service key
     * @param string $key key
     */
    public function setServiceKey($serviceKey)
    {
        $this->serviceKey = $serviceKey;
        return $this;
    }

    /**
     * Get SOAP client
     *
     * @return \SoapClient
     */
    public function getSoapClient($wsdl, $options)
    {
        // $options['soap_version'] = SOAP_1_2;
        $options['features'] = 1;
        $options['trace']    = true;
        return new \SoapClient($wsdl, $options);
    }

    /**
     * 返回调用的随机数
     *
     * @return string
     */
    public function getRandomString()
    {
        return 'Dubbo' . time();
    }
}
