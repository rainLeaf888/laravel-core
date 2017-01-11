<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Common\Facades\YzRedis;

/**
 * 获取数据库的链接
 */
if (! function_exists('getConnection')) {
    function getConnection($key)
    {
        return DB::connection($key);
    }
}

/**
 * 替换危险字符
 */
if (! function_exists('replaceRiskWord')) {
    function replaceRiskWord($str)
    {
        if (!empty($str)) {
            $str    = trim($str); //先删除空格
            $search = array(
                    "'<script[^>]*?>.*?</script>'si",   // 去掉javascript
                    "'<[\/\!]*?[^<>]*?>'si",            // 去掉 HTML 标记
                    "'([\r\n])[\s]+'",                  // 去掉空白字符
                    "'&(quot|#34);'i",                  // 替换 HTML 实体
                    "'&(amp|#38);'i",
                    "'&(lt|#60);'i",
                    "'&(gt|#62);'i",
                    "'&(nbsp|#160);'i"
            );
            $replace = array(
                    "",
                    "",
                    "\\1",
                    "\"",
                    "&",
                    "<",
                    ">",
                    ""
            );
            $str = @preg_replace($search, $replace, $str);
            $str = str_replace("\"", "", $str);
            $str = str_replace("\\", "", $str);
            $str = str_replace("(", "", $str);
            $str = str_replace(")", "", $str);
            $str = str_replace("/", "/", $str);
            $str = str_replace("'", "", $str);
            $str = str_replace("<", "", $str);
            $str = str_replace(">", "", $str);
            $str = str_replace("$", "+", $str);
            $str = str_replace("%", "+", $str);
            $str = str_replace("{", "", $str);
            $str = str_replace("}", "", $str);
        }
        return $str;
    }
}

/**
 * 检查是否手机号是否合法
 *
 * @return  bool
 */
if (! function_exists('isMobile')) {
    function isMobile($mobile)
    {
        $pattern = '#^1[0-9]{10}$#';
        return (bool)preg_match($pattern, $mobile);
    }
}

/**
 * 检查是否手机号是否合法
 *
 * @return  bool
 */
if (! function_exists('isEmail')) {
    function isEmail($email)
    {
        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        return (bool)preg_match($pattern, $email);
    }
}

/**
 * 格式化金额
 */
if (! function_exists('moneyFormat')) {
    function moneyFormat($money, $species = '')
    {
        if (is_numeric($money)) {
            return $species . number_format($money, 2, '.', ',');
        }
        return $money;
    }
}

/**
 * 百分比输出
 */
if (! function_exists('percent')) {
    function percent($num1, $num2, $decimals = 2)
    {
        $ret = 0;
        if ($num2) {
            $ret = $num1 / $num2 * 100;
        }

        return round($ret, $decimals);
    }
}
/**
 * 设置redis key，value
 */
if (! function_exists('cacheSet')) {
    function cacheSet($prefix, $key, $value, $expire)
    {
        $value = json_encode($value, true);
        $ok = YzRedis::setPrefix($prefix)->set($key, $value);
        if ($ok) {
            YzRedis::setPrefix($prefix)->expire($key, $expire);
        }
        if (!$ok) {
            \Log::error("缓存失败 " . $key);
        }
        return $ok;
    }
}
/**
 * 获取redis key，value
 */
if (! function_exists('cacheGet')) {
    function cacheGet($prefix, $key)
    {
        $value = YzRedis::setPrefix($prefix)->get($key);
        
        return json_decode($value, true);
    }
}
/**
 * 判断字符串是否是json
 */
if (! function_exists('isJson')) {
    function isJson($string)
    {
        json_decode($string, true);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

/**
 * 判断字符串是否是json
 */
if (! function_exists('getOutBizNo')) {
    function getOutBizNo()
    {
        $prefix = 'OUT_BIZ_NO';
        $seq = YzRedis::setPrefix($prefix)->get('incr');
        if ($seq) {
            $code = date("YmdHi") . str_pad($seq + 1, 8, "0", STR_PAD_LEFT);
            YzRedis::setPrefix($prefix)->incr('incr');
        } else {
            YzRedis::setPrefix($prefix)->set('incr', 1);
            $code = date("YmdHi") . str_pad(1, 8, "0", STR_PAD_LEFT);
        }

        return $code;
    }
}

/**
 * 一维数组转为二维键值对形式,方便前端调用
 */
if (! function_exists('transKeyValue')) {
    function transKeyValue($arr)
    {
        $data = [];
        if (!empty($arr)) {
            foreach ($arr as $key => $value) {
                $data[] = [
                    'key'   => $key,
                    'value' => $value,
                ];
            }
        }
        return $data;
    }
}

/**
 * 根据短信内容，获取短信条数
 */
if (! function_exists('getSmsCount')) {
    function getSmsCount($content)
    {
        $content = str_ireplace("\n", "", $content);
        $content = str_ireplace("\r", "", $content);
        $content = str_ireplace("\t", "", $content);
        $content = preg_replace('/\(\d\/\d\)/i', "", $content);

        return ceil(mb_strlen($content, 'UTF-8') / 64);
    }
}

/**
 * 打包文件夹
 *
 * @param  $zipName 最终生成压缩包的名称
 * @param  $zipDir  压缩包存储的位置
 * @param  $zipName 需要打包的文件夹
 */
if (! function_exists('createZip')) {
    function createZip($zipName, $zipDir, $targetPath)
    {
        if (!class_exists('ZipArchive')) {
            $zipName = $zipName . '.zip';
            $file = $zipDir . '/' . $zipName;
            system("zip -qj -r $file {$zipDir}");
        } else {
            // 利用ZipArchive对文件进行压缩
            $zip = new ZipArchive();
            $zipName = $zipName . '.zip';
            $zipFile = $zipDir . '/' . $zipName;
            if ($zip->open($zipFile, ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            if (is_dir($targetPath)) {
                $handler = opendir($targetPath);
                while (($file = readdir($handler)) !== false) {
                    if ($file != "." && $file != "..") {
                        $zip->addFile($targetPath . "/" . $file, iconv('UTF-8', 'GBK//TRANSLIT', $file));
                    }
                }
                closedir($handler);
            } else {
                return false;
            }
            $zip->close();
            if (!file_exists($zipFile)) {
                return false;
            }
        }

        return $zipFile;
    }
}

/**
 * 删除文件夹下的所有文件
 */
if (! function_exists('delDir')) {
    function delDir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file=readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir."/".$file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 检查密码强度（0-不容许 1-弱 2-中等 3强)
 */
if (! function_exists('passwordLevel')) {
    function passwordLevel($string)
    {
        if (strlen($string) < 6 || preg_match('/^\d+$/', $string)) {
            return 0;
        }
        return preg_match('/\d+/', $string)
            + preg_match('/[a-z]+/i', $string)
            + (preg_replace('/[\da-z]+/i', '', $string) ? 1 : 0);
    }
}

/**
 * 缓存时间计算
 */
if (! function_exists('expireTime')) {
    function expireTime($type = 'day')
    {
        $expire = 0;
        if ($type) {
            $day    = date('Y-m-d 23:59:59');
            $expire = strtotime($day) - time();
        }

        return $expire;
    }
}
