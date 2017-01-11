<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use App\Common\Facades\Domain;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Modules\Operations\Services\Log\LogConst;

class UserOperationLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * 注册一个可以终止的中间件，可终止中间件
     * @param $request 请求
     * @param 响应
     */
    public function terminate($request, $response)
    {
        //记录没有定义code的信息主体
        $message = '';
        $content = json_decode($response->content());
        //判断是否是json
        try {
            //判断是否是json
            if (json_last_error() != JSON_ERROR_NONE) {
                $return = new \StdClass();
                $return->flag = 0;
                if ($response->status() == '200') {
                    $return->flag = 1;
                }
                $return->data = [];
            } else {
                $return = $content;
                // 记录必要的用户操作
                $this->record($request->path(), $content);
            }

            //模块名字
            $moduleName = $this->getModuleName($request);
            //controller和action的名字
            $collection = $this->getControllerAndAction();
            //当前登录用户的ID
            $session    = $this->getCurrentUserId();
            //根据url地址找到对应到系统的编码
            $code = $this->loadActionMappingXml($moduleName, $collection['controller'], $collection['action']);
            if ($code['module'] == '') {
                $message .= "模块没有定义code: ". $moduleName;
            } elseif ($code['controller'] == '') {
                $message .= "控制器没有定义code: ". $collection['controller'];
            } elseif ($code['action'] == '') {
                $message .= "动作没有定义code: ". $collection['action'];
            } else {
                $actionCode = $code['module'].$code['controller'].$code['action'];
                $locateCode = '100002'.$code['module'];
                $json['handler']      = (string)$session['userId'];
                $json['actionCode']   = (string)$actionCode;
                $json['responseCode'] = isset($return->flag) ? (string)$return->flag : '';
                $json['locateCode']   = (string)$locateCode;
                $json['parameters'] = [
                    'path' => $request->url(),
                    // 'request' => $request->all(),
                    'brandId' => (string)$session['brandId'],
                    'storeId' => (string)$session['storeId'],
                    'desc'    => $code['desc']
                ];
                $message = $collection['position'] . ' ' . json_encode($json);
                Log::business($message);
            }
            if (Config::get('app.debug')) {
                if ($message != '') {
                    Log::info($message.' 路径为: '.$request->url());
                }
            }
        } catch (\Exception $e) {
            Log::error('记录用户行为出现错误'.$e->getMessage().' 路径为 '.$request->url());
        }
    }

    /**
     * 获取当前用的信息
     * @return string
     */
    public function getCurrentUserId()
    {
        $data    = [];
        $userId  = 0;
        $brandId = 0;
        $storeId = 0;
        $session = Domain::getSession();
        if (isset($session['userId'])) {
            $userId = $session['userId'];
        }
        if (isset($session['currentChainId'])) {
            $brandId = $storeId = $session['currentChainId'];
        }
        $data['userId']  = $userId;
        $data['brandId'] = $brandId;
        $data['storeId'] = $storeId;
        return $data;
    }
    /**
     * 获取模块的名字
     * @param  $request 请求对象
     * @return string
     */
    public function getModuleName($request)
    {
        $currentPath = $request->path();
        if ($currentPath == '/') {
            return 'index';
        }
        $parameters = explode('/', $currentPath);
        return $parameters[0];
    }

    /**
     * 获取controller和action的名字
     * @return array
     */
    public function getControllerAndAction()
    {
        $position = Route::currentRouteAction();
        $route    = explode('\\', $position);
        $realPath = end($route);
        $result   = explode('@', $realPath);
        if ($route['3'] == 'Controllers') {
            $controller = $result[0];
        } else {
            $controller = $route[3].'\\'.$result[0];
        }
        $data = [
            'controller' => $controller,
            'action'     => $result[1],
            'position'   => $position,
        ];
        return $data;
    }

    /**
     * 根据模块，控制器，action的名字找到相应的code
     * @param  string $moduleName     模块名字
     * @param  string $controllerName 控制器名字
     * @param  string $actionName     action名字
     * @return array
     */
    public function loadActionMappingXml($moduleName = '', $controllerName = '', $actionName = '')
    {
        $code = [
            'module'     => '',
            'controller' => '',
            'action'     => '',
            'desc'       => '',
        ];
        $xmlPath = \App::basePath().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'yazuo-sitemap.xml';
        if (file_exists($xmlPath)) {
            $result = simplexml_load_file($xmlPath);
            $result = json_decode(json_encode($result), true);
            //判断是否有多个模块
            if (isset($result['module']['name'])) {
                $result['module']= [$result['module']];
            }
            foreach ($result['module'] as $module) {
                //找到对应的模块名字
                if ($module['name'] == $moduleName) {
                    $code['module'] = $module['code'];
                    //判断是否有多个controller,如果有name就说明只有一个controller
                    if (isset($module['controller']['name'])) {
                        $module['controller'] = [$module['controller']];
                    }
                    foreach ($module['controller'] as $controller) {
                        if (isset($controller['name']) && $controller['name'] == $controllerName) {
                            $code['controller'] = $controller['code'];
                            //判断是否有多个action
                            if (isset($controller['action']['name'])) {
                                $controller['action'] = [$controller['action']];
                            }
                            foreach ($controller['action'] as $action) {
                                if (isset($action['name']) && $action['name'] == $actionName) {
                                    //找到action就要跳出所有循环
                                    $code['action'] = $action['code'];
                                    $code['desc']   =  $action['desc'];
                                    break 3;
                                }
                            }
                            break 2;
                        }
                    }
                }
            }
        }
        
        return $code;
    }
}
