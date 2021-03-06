<?php
namespace Core;

use Engine\RedisEngine;
use Exception\HTTPException;

class Util {

    private static $instances = array ();

    /**
     * 根据类名获取该类的单例
     *
     * @param $className
     * @param string $tags
     * @return object
     */
    public static function loadCls($className, $tags = '') {

        if (!isset(self::$instances[$className . '_' . $tags])) {
            self::$instances[$className . '_' . $tags] = new $className();
        }
        return self::$instances[$className . '_' . $tags];
    
    }

    private static $models = array ();

    /**
     * 根据类名获取该类的单例
     *
     * @param $className
     * @param $entryName
     * @param $id
     * @return BaseModel
     */
    public static function loadModel($className, $entryName, $id) {

        if (!isset(self::$models[$className . '_' . $id])) {
            self::$models[$className . '_' . $id] = new $className($entryName, $id);
        }
        
        return self::$models[$className . '_' . $id];
    
    }

    private static $redis = array ();

    /**
     * 根据类名获取该类的单例
     *
     * @param $tags
     * @param bool $config
     * @return RedisEngine
     * @throws HTTPException
     */
    public static function loadRedis($tags, $config = false) {

        if ($config === false) {
            if (!isset($GLOBALS['REDIS'][$tags])) {
                throw Util::HTTPException('load redis tags ' . $tags . ' not exists.');
            }
            $config = $GLOBALS['REDIS'][$tags];
        }
        
        if (!isset(self::$redis[$tags])) {
            $className = 'Engine\\RedisEngine';
            self::$redis[$tags] = new $className();
            self::$redis[$tags]->connect($config['host'], $config['port'], $config['timeout'], $config['database'], $config['pconnect'], $config['password']);
        }
        
        return self::$redis[$tags];
    
    }

    /**
     * 加载Ctrl类
     *
     * @param $clsName
     * @return BaseCtrl
     */
    public static function loadCtrl($clsName) {

        return Util::loadCls("Ctrl\\{$clsName}");
    
    }

    /**
     * 抛出异常
     *
     * @param $errMsg
     * @param null $errData
     * @return HTTPException
     */
    public static function HTTPException($errMsg, $errData = null) {

        return new HTTPException($errMsg, $errData);
    
    }

    /**
     * 拆分ID
     *
     * @param $id
     * @return string
     */
    public static function id_path($id) {
        // str_pad
        $pathNum = 1000000000 + $id;
        $pathNumA = substr($pathNum, 1, 3);
        $pathNumB = substr($pathNum, 4, 3);
        $pathNumC = substr($pathNum, 7, 3);
        $user_path = $pathNumA . '/' . $pathNumB . '/' . $pathNumC;
        return $user_path;
    
    }

    /**
     * 递归创建目录
     *
     * @param $path
     * @param int $mode
     * @return bool|string
     */
    public static function mkdirs($path, $mode = 0755) {

        $dirs = explode('/', $path);
        $dirslen = count($dirs);
        $state = '';
        for($c = 0; $c < $dirslen; $c++) {
            $thispath = '';
            for($cc = 0; $cc <= $c; $cc++) {
                $thispath .= $dirs[$cc] . '/';
            }
            if (!@file_exists($thispath)) {
                $thispaths = substr($thispath, 0, strrpos($thispath, '/'));
                $state = @mkdir($thispaths, $mode);
            }
        }
        return $state;
    
    }

    /**
     * 按权重获取类型
     *
     * @param $data
     * @return int|string
     */
    private static function rand($data) {

        $rd = rand(1, array_sum($data));
        $rv = 0;
        
        foreach ( $data as $type => $odds ) {
            $rv += $odds;
            
            if ($rd <= $rv) {
                return $type;
            }
        }
        
        return 0;
    
    }

    /**
     * 按权重获取多个
     *
     * @param $data
     * @param int $num
     * @return array
     */
    public static function rands($data, $num = 1) {

        $items = array ();
        
        while ( $num ) {
            $item = self::rand($data);
            
            if ($item)
                $items[] = $item;
            else
                break;
            
            unset($data[$item]);
            $num--;
        }
        
        return $items;
    
    }

    /**
     * 按权重获取物品
     *
     * @param $data
     * @return mixed
     */
    public static function randItem($data) {

        $items = array ();
        $num = 0;
        foreach ( $data as $item ) {
            $num += $item[2];
        }
        
        $rd = rand(1, $num);
        $rv = 0;
        foreach ( $data as $item ) {
            $rv += $item[2];
            if ($rd <= $rv) {
                return $item;
            }
        }
    
    }

    /**
     * 改变图片大小
     *
     * @param $path
     * @param $maxW
     * @param $maxH
     * @param $npath
     * @param $lcok
     */
    public static function resize($path, $maxW, $maxH, $npath, $lcok) {

        $iminfo = getimagesize($path);
        
        switch ($iminfo[2]) {
            case 1 :
                $im = imagecreatefromgif($path);
                break; /* gif */
            case 2 :
                $im = imagecreatefromjpeg($path);
                break; /* jpg */
            case 3 :
                $im = imagecreatefrompng($path);
                break; /* png */
        }
        
        $resizeByW = $resizeByH = false;
        
        if ($iminfo[0] > $maxW && $maxW) {
            $resizeByW = true;
        }
        if ($iminfo[1] > $maxH && $maxH) {
            $resizeByH = true;
        }
        
        if ($resizeByH && $resizeByW) {
            $resizeByH = ($iminfo[0] / $maxW < $iminfo[1] / $maxH);
            $resizeByW = !$resizeByH;
        }
        
        if ($resizeByW) {
            if ($lcok) {
                $newW = $maxW;
                $newH = round(($iminfo[1] * $maxW) / $iminfo[0]);
            } else {
                $newW = $maxW;
                $newH = $iminfo[1];
            }
        } else if ($resizeByH) {
            if ($lcok) {
                $newW = round(($iminfo[0] * $maxH) / $iminfo[1]);
                $newH = $maxH;
            } else {
                $newW = $iminfo[0];
                $newH = $maxH;
            }
        } else {
            $newW = $iminfo[0];
            $newH = $iminfo[1];
        }
        $imN = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($imN, $im, 0, 0, 0, 0, $newW, $newH, $iminfo[0], $iminfo[1]);
        switch ($iminfo[2]) {
            case '1' :
                $result = imagegif($imN, $npath);
                break;
            case '2' :
                $result = imagejpeg($imN, $npath, 100);
                break;
            case '3' :
                $result = imagepng($imN, $npath);
                break;
        }
    
    }

    /**
     * 转换URL
     *
     * @return string
     */
    public static function url() {

        $args = func_get_args();
        $url = array_shift($args);
        foreach ( $args as $param ) {
            $url .= '/' . $param;
        }
        
        return REWRITE . $url;
    
    }

    /**
     * 通过curl获取远程文本内容
     *
     * @param $url
     * @param $type
     * @param bool $params
     * @param array $header
     * @return bool|mixed
     */
    public static function curl_request($url, $type, $params = false, $header = array()) {

        $ch = curl_init();
        
        $timeout = 30;
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        if (count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        
        // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)'); // 伪造浏览器头
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        if (strtoupper($type) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            return false;
        }
        
        return $response;
    
    }

    /**
     * 发送一个异步执行请求
     *
     * @param $ctrl
     * @param $method
     * @param $params
     */
    public static function async($ctrl, $method, $params) {

        $client = new \swoole_client(SWOOLE_SOCK_TCP);
        
        $client->connect('127.0.0.1', 9501);
        
        $client->send(json_encode(array (
            'method' => 'process', 
            'params' => array (
                'ctrl' => $ctrl, 
                'method' => $method, 
                'params' => $params 
            ) 
        )));
    
    }

    /**
     * 检查email格式是否合法
     *
     * @param $email
     * @return bool
     */
    public static function check_email($email) {

        $pattern = '/^([0-9a-z\-_\.]+)@([0-9a-z]+\.[a-z]{2,30}(\.[a-z]{2,30})?)$/i';
        
        if (preg_match($pattern, $email)) {
            return true;
        } else {
            return false;
        }
    
    }

    /**
     * 异步发送邮件
     *
     * @param $to
     * @param $title
     * @param $body
     * @param array $attach
     */
    public static function send_mail($to, $title, $body, $attach = array()) {

        Util::async('Engine\MailEngine', 'send', array (
            $to, 
            $title, 
            $body, 
            $attach 
        ));
        
        file_put_contents('/tmp/swoole.process.log', 'mail send: ' . $title . "\n\n\n", FILE_APPEND);
    
    }

    /**
     * 当前系统毫秒
     *
     * @return float
     */
    public static function millisecond() {

        list ($usec, $sec) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000);
    
    }

}
