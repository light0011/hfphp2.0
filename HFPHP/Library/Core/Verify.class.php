<?php

/**
 * 验证码类
 */
namespace Core;


class Verify{

    protected $config = array(
        'key' => 'HFPHP',
        'code' => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',
        'expire' => 1800,
        'fontSize' => 25,
        'useNoise' => false,   //是否添加杂点
        'imageH' => 0,
        'imageW' => 0,
        'num' => 5,   //验证码位数
        'bg' => array(243,251,254), //背景颜色
        'reset' => true,
        'fontttf' => ''   //是否使用特定字体，否则随机

    );

    //验证码图片实例
    private $image = NULL;

    //验证码字体颜色
    private $color = NULL;

    /**
     * 设置参数
     * @param array $config
     */
    public function __construct($config=array()) {
        $this->config = array_merge($this->config,$config);
    }

    /**
     * 得到验证码的相关配置
     * 在类的内部调用类不存在的属性也会触发该魔术方法
     * @param $name
     * @return mixed
     */
    public function __get($name){
        return $this->config[$name];
    }

    /**
     * 设置验证码的配置
     * @param $name
     * @param $value
     */
    public function __set($name,$value) {
        if(isset($this->$name)) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 判断是否存在该属性
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        return isset($this->config[$name]);
    }



    public function verify($id = '') {
        //图片宽度(px)
        $this->imageW || $this->imageW = $this->num*$this->fontSize*2;
        //图片高度(px)
        $this->imageH || $this->imageH = $this->fontSize * 2.5;
        //建立一副 $this->imageW x $this->imageH 的图像

        $this->image = imagecreate($this->imageW,$this->imageH);


        //设置背景

        imagecolorallocate($this->image,$this->bg[0],$this->bg[1],$this->bg[2]);
        //验证码字体随机颜色
        $this->color = imagecolorallocate($this->image,mt_rand(1,150),mt_rand(1,150),mt_rand(1,150));



        //验证码使用随机字体

        $ttfPath = VENDOR_PATH.'Verify/ttfs/';

        if(empty($this->fontttf)) {
            $dir = dir($ttfPath);
            $ttfs = array();
            while(false !== ($file = $dir->read())) {
                if($file[0] != '.' && substr($file,-4) == '.ttf') {
                    $ttfs[] = $file;
                }
            }
            $dir->close();
            $this->fontttf = $ttfs[array_rand($ttfs)];

        }

        $this->fontttf = $ttfPath.$this->fontttf;



        //绘制杂点
        if($this->useNoise) {
            $this->writeNoise();
        }



        //绘制验证码
        $code = array();  //验证码
        $codeNx = 0;   //验证码第N个字符的左边距

        for($i = 0;$i<$this->num;$i++) {

            $code[$i] = $this->code[mt_rand(0,strlen($this->code)-1)];

            $codeNx += mt_rand($this->fontSize*1.2,$this->fontSize*1.6);
            imagettftext($this->image,$this->fontSize,mt_rand(-40,40),$codeNx,$this->fontSize*1.6,$this->color,$this->fontttf,$code[$i]);

        }

        //保存验证码
        $key = $this->authcode($this->key);
        $code = $this->authcode(strtoupper(implode('',$code)));

        $save_code = array();
        $save_code['verify_code'] = $code;
        $save_code['verify_time'] = time();

        $session = \Core\Session::getInstance();
        $session->set($key.$id,$save_code);

        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("content-type: image/png");

        //输出图像
        imagepng($this->image);
        imagedestroy($this->image);


    }


    /**
     * 绘制杂点
     */
    public function writeNoise() {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
        for($i = 0; $i < 10; $i++) {
            //杂点颜色
            $noiseColor = imagecolorallocate($this->image,mt_rand(150,225),mt_rand(150,225),mt_rand(150,225));
            for($j = 0;$j < 5; $j++) {
                //绘制杂点
                //与imagettftext不同的是这个不能指定角度与字体文件从而使用特定字体
                imagestring($this->image,5,mt_rand(-10,$this->imageW),mt_rand(-10,$this->imageH),$codeSet[mt_rand(0,29)],$noiseColor);
            }
        }

    }


    /**
     * 加密验证码
     * @param $str
     * @return string
     */
    public function authcode($str) {
        $key = substr(md5($this->key),5,8);
        $str = substr(md5($str),8,10);
        return md5($key.$str);
    }


    /**
     * 判断验证码是否正确
     * @param $code
     * @param string $id
     * @return bool
     */
    public function check($code,$id = '') {
        $key = $this->authcode($this->key) . $id;
        //验证码不能为空
        $session = \Core\Session::getInstance();
        $res_code = $session->get($key);
        if (empty($code) || empty($res_code)) {
            return false;
        }

        //session过期
        if(time() - $res_code['verify_time'] > $this->expire) {
            $session->set($key,null);
            return false;
        }

        //判断验证码是否正确
        if($this->authcode(strtoupper($code)) == $res_code['verify_code']) {
            $this->reset && $session->set($key,null);
            return true;
        }

        return false;


    }



}




