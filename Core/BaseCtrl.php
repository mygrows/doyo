<?php

namespace Core;

class BaseCtrl
{

    /**
     * 取得一个Smarty对象
     *
     * @var \Smarty
     */
    private $view;

    /**
     * 保存param数组
     *
     * @var array
     */
    private $param = array();

    /**
     * 是否是表单提交
     *
     * @var boolean
     */
    public $isPost = false;

    /**
     * 构造函数
     */
    public final function __construct()
    {

    }

    /**
     * 继承的子类要用到的构造函数
     */
    public function __initialize()
    {

    }

    /**
     * 初始化Smarty配置
     *
     * @param null $template
     * @param null $compile
     */
    public final function initSmarty($template = null, $compile = null)
    {

        require_once(CORE_PATH . '/Engine/Smarty/libs/Smarty.class.php');

        $this->view = new \Smarty();

        $this->view->debugging = SMARTY_DEBUG;

        if ($template != null) {
            $this->view->setTemplateDir(APP_PATH . '/' . $template);
        } else {
            $this->view->setTemplateDir(APP_PATH . '/' . SMARTY_TEMPLATE_DIR);
        }

        if ($compile != null) {
            $this->view->setCompileDir(APP_PATH . '/' . $compile);
        } else {
            $this->view->setCompileDir(APP_PATH . '/' . SMARTY_COMPILE_DIR);
        }

        $this->view->compile_check = SMARTY_COMPILE_CHECK;

        $this->view->left_delimiter = SMARTY_LEFT_DELIMITER;

        $this->view->right_delimiter = SMARTY_RIGHT_DELIMITER;

        $this->view->joined_config_dir = APP_PATH . '/' . WEBROOT . '/';

        if (defined('SMARTY_CONFIG_LOAD')) {
            $this->view->configLoad(SMARTY_CONFIG_LOAD);
        }

    }

    /**
     * 设置Smarty的模板目录和编译目录
     *
     * @param $template
     * @param $compile
     */
    public final function setSmarty($template, $compile)
    {

        $this->view->setTemplateDir(APP_PATH . '/' . $template);
        $this->view->setCompileDir(APP_PATH . '/' . $compile);

    }

    /**
     * * 初始化upload配置
     *
     * @return \Engine\FileEngine
     */
    public final function initFiles()
    {

        return Util::loadCls('Engine\FileEngine');

    }

    /**
     * 设置参数
     *
     * @param $param
     * @throws \Exception\HTTPException
     */
    public final function setParams($param)
    {

        if (is_array($param)) {
            $this->param = $param;
        } else if (is_string($param)) {

            $this->param = array();

            $ary = explode('&', $param);

            foreach ($ary as $val) {
                $kv = explode('=', $val);
                $this->param[trim($kv[0])] = trim($kv[1]);
            }
        } else {
            throw Util::HTTPException('set params error');
        }

    }

    /**
     * 获得参数
     *
     * @return array
     */
    public final function getParams()
    {

        return $this->param;

    }

    /**
     * 查询传递的Integer参数
     *
     * @param $key
     * @param bool $notEmpty
     * @param bool $abs
     * @return float|int|string
     * @throws \Exception\HTTPException
     */
    protected final function getInteger($key, $notEmpty = false, $abs = false)
    {

        if (is_numeric($key)) {
            $key--;
        }

        $val = isset($this->param[$key]) ? floatval($this->param[$key]) : false;

        if ($notEmpty && $val === false) {
            throw Util::HTTPException($key . ' empty');
        }

        if ($abs) {
            $val = abs($val);
        }

        return $val;

    }

    /**
     * 查询传递的参数
     *
     * @param $key
     * @return bool
     */
    protected final function hasParam($key)
    {

        if (is_numeric($key)) {
            $key--;
        }

        if (isset($this->param[$key])) {
            return true;
        }

        return false;

    }

    /**
     * 查询传递的String参数
     *
     * @param $key
     * @param bool $notEmpty
     * @return string
     * @throws \Exception\HTTPException
     */
    protected final function getString($key, $notEmpty = false)
    {

        if (is_numeric($key)) {
            $key--;
        }

        $val = isset($this->param[$key]) ? trim($this->param[$key]) : false;

        if ($notEmpty && $val === false) {
            throw Util::HTTPException($key . ' empty');
        }

        return strval($val);

    }

    /**
     * 查询传递的Integer数组参数
     *
     * @param $key
     * @param bool $notEmpty
     * @param bool $abs
     * @return array|mixed|string
     * @throws \Exception\HTTPException
     */
    protected final function getIntegers($key, $notEmpty = false, $abs = false)
    {

        if (is_numeric($key)) {
            $key--;
        }

        $val = isset($this->param[$key]) ? $this->param[$key] : false;

        if ($notEmpty && $val === false) {
            throw Util::HTTPException($key . ' empty');
        }

        if ($val != '') {

            $_val = $val;

            if (!is_array($_val)) {

                $_val = json_decode($val);

                if (!is_array($_val)) {
                    $_val = explode(',', $val);
                }

                if (!is_array($_val) || count($_val) == 1) {
                    $_val = explode('-', $val);
                }
            }

            if (!is_array($_val)) {
                throw Util::HTTPException($key . ' not array');
            }

            $val = array_map('floatval', $_val);

            if ($abs) {
                $val = array_map('abs', $val);
            }
        }

        return $val;

    }

    /**
     * 查询传递的String数组参数
     *
     * @param $key
     * @param bool $notEmpty
     * @return array|mixed|string
     * @throws \Exception\HTTPException
     */
    protected final function getStrings($key, $notEmpty = false)
    {

        if (is_numeric($key)) {
            $key--;
        }

        $val = isset($this->param[$key]) ? $this->param[$key] : false;

        if ($notEmpty && $val === false) {
            throw Util::HTTPException($key . ' empty');
        }

        if ($val != '') {

            $_val = $val;

            if (!is_array($_val)) {

                $_val = json_decode($val);

                if (!is_array($_val)) {
                    $_val = explode(',', $val);
                }

                if (!is_array($_val) || count($_val) == 1) {
                    $_val = explode('-', $val);
                }
            }

            if (!is_array($_val)) {
                throw Util::HTTPException($key . ' not array');
            }

            $val = $_val;

            $val = array_map('strval', $val);
        }

        return $val;

    }

    /**
     * 设置$_SESSION内的值
     *
     * @param $key
     * @param $val
     */
    protected final function setSession($key, $val)
    {

        $_SESSION[$key] = $val;

    }

    /**
     * 查询$_SESSION内的值
     *
     * @param $key
     * @param bool $notEmpty
     * @return bool
     * @throws \Exception\HTTPException
     */
    protected final function getSession($key, $notEmpty = false)
    {

        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        if ($notEmpty) {
            throw Util::HTTPException($key . ' error.');
        }

        return false;

    }

    /**
     * 删除$_SESSION内的值
     *
     * @param $key
     */
    protected final function delSession($key)
    {

        unset($_SESSION[$key]);

    }

    /**
     * 清空$_SESSION内的值
     */
    protected final function clearSession()
    {

        session_destroy();

    }

    /**
     * 设置$_COOKIE内的值
     *
     * @param $key
     * @param $val
     * @param int $expire
     * @param string $path
     * @param string $domain
     */
    protected final function setCookie($key, $val, $expire = 86400, $path = '/', $domain = COOKIE_DOMAIN)
    {

        $expire += time();

        setcookie($key, $val, $expire, $path, $domain);

    }

    /**
     * 查询$_COOKIE内的值
     *
     * @param $key
     * @param bool $notEmpty
     * @return null
     * @throws \Exception\HTTPException
     */
    protected final function getCookie($key, $notEmpty = false)
    {

        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }

        if ($notEmpty) {
            throw Util::HTTPException($key . 'error.');
        }

        return null;

    }

    /**
     * Curl POST提交
     *
     * @param $url
     * @param $param
     * @return bool|mixed
     */
    public final function post($url, $param)
    {

        return Util::curl_request($url, 'POST', $param);

    }

    /**
     * Curl GET提交
     *
     * @param $url
     * @return bool|mixed
     */
    public final function get($url)
    {

        return Util::curl_request($url, 'GET');

    }

    /**
     * 向模板传递变量
     *
     * @param $tpl_var
     * @param $value
     * @param bool $nocache
     */
    public final function assign($tpl_var, $value, $nocache = false)
    {

        $this->view->assignGlobal($tpl_var, $value, $nocache);

    }

    /**
     * 渲染模板
     *
     * @param $template
     * @param null $cache_id
     * @param null $compile_id
     * @param null $parent
     */
    public final function display($template, $cache_id = null, $compile_id = null, $parent = null)
    {

        if ($this->view->templateExists($template)) {
            $this->view->display($template, $cache_id, $compile_id, $parent);
        } else {
            exit('404');
        }

    }

    /**
     * 返回渲染的结果
     *
     * @param $template
     * @param null $cache_id
     * @param null $compile_id
     * @param null $parent
     * @param bool $display
     * @param bool $merge_tpl_vars
     * @param bool $no_output_filter
     * @return string
     * @throws \Exception
     * @throws \SmartyException
     */
    public final function fetch($template, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
    {

        if ($this->view->templateExists($template)) {
            return $this->view->fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
        }

        return '';

    }

    /**
     * 重定向
     *
     * @param $url
     */
    public final function redirect($url)
    {

        header('location:' . $url);
        exit();

    }

    /**
     * 以JSON格式返回
     */
    public final function display_json()
    {

        Context::dispatcher()->model = 'JSON';

    }

    /**
     * 以HTML格式返回
     */
    public final function display_html()
    {

        Context::dispatcher()->model = 'HTML';

    }

    /**
     * 获取客户端IP
     *
     * @param bool $long
     * @return array|false|int|string
     */
    public final function ipaddr($long = true)
    {
        if ($this->fd > 0 && $this->svr != null) {

            $ipaddr = $this->svr->connection_info($this->fd);

            if (isset($ipaddr['remote_ip'])) {
                if ($long) {
                    return ip2long($ipaddr['remote_ip']);
                }
                return $ipaddr['remote_ip'];
            } else {
                if ($long) {
                    return 0;
                } else {
                    return '0.0.0.0';
                }
            }

        }

        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddr = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddr = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddr = getenv('REMOTE_ADDR');
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddr = $_SERVER['REMOTE_ADDR'];
        } else {
            if ($long) {
                return 0;
            } else {
                return '0.0.0.0';
            }
        }

        if (strchr($ipaddr, ',')) {
            $ipaddr = explode(',', $ipaddr);
            $ipaddr = $ipaddr[count($ipaddr) - 1];
        }

        $ipaddr = ltrim($ipaddr);

        if ($long) {
            return ip2long($ipaddr);
        }

        return $ipaddr;

    }

    /**
     * 长连接的文件描述
     * @var int
     */
    public $fd = -1;

    /**
     * 长连接的文件引用
     *
     * @var \swoole_websocket_server
     *
     */
    public $svr = null;

    /**
     * 长连接发送数据
     *
     * @param $op
     * @param $data
     * @param int $fd
     * @param int $code
     * @return mixed
     */
    public final function send($op, $data, $fd = -1, $code = 1)
    {

        if ($fd === -1) {
            $fd = $this->fd;
        }

        if (!$this->svr->exist($fd)) {
            echo " fd not connect.: {$fd} \n";
            return false;
        }

        $array = array(
            'code' => $code,
            'op' => $op,
            'version' => VERSION,
            'unixtime' => Util::millisecond(),
            'data' => $data
        );

        $data = json_encode($array, JSON_UNESCAPED_UNICODE);

        return $this->svr->push($fd, $data);

    }

    /**
     * 长连接返回数据
     *
     * @param $data
     * @param int $fd
     */
    public final function error($data, $fd = -1)
    {

        echo $data;
        echo "\n";

        if ($fd === -1) {
            $fd = $this->fd;
        }

        $this->send(-1, array(
            'message' => $data
        ), $fd, 0);

    }


    /**
     * 绑定UID
     *
     * @param $pid
     */
    public final function connection_bind($pid)
    {

        $this->svr->bind($this->fd, $pid);

    }

    /**
     * file description info
     *
     * @param int $fd
     * @return array
     */
    public final function connection_info($fd = -1)
    {
        if ($fd === -1) {
            $fd = $this->fd;
        }

        return $this->svr->connection_info($fd);
    }

    /**
     * 关闭一个文件描述
     *
     * @param int $fd
     */
    public final function close($fd = -1)
    {

        if ($fd === -1) {
            $fd = $this->fd;
        }

        if ($this->svr->exist($fd)) {
            $this->svr->close($fd);
        }

    }

}