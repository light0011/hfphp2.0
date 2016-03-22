<?php

namespace Core;

//图像处理类

class Image{

    private $file;         //图片地址
    private $width;      //图片宽度
    private $height;     //图片长度
    private $type;        //图片类型
    private $img;         //原图的资源句柄
    private $new;        //新图的资源句柄


    //构造方法，初始化
    public function __construct($file){
        $this->file = ROOT_PATH.$file;
        list($this->width,$this->height,$this->type) = getimagesize($this->file);
        $this->img = $this->getFromImg($this->file,$this->type);

    }


    //图片裁剪
    public function thumb($new_width = 0,$new_height = 0){

        if (empty($new_width) && empty($new_height)) {
            $new_width = $this->width;
            $new_height = $this->height;
        }


        if(!is_numeric($new_width) || !is_numeric($new_height)){
            $new_width = $this->width;
            $new_height = $this->height;
        }

        //创建一个新容器
        $n_w = $new_width;
        $n_h = $new_height;

        //创建裁剪点
        $cut_width = 0;
        $cut_height = 0;

        if($this->width < $this->height){
            $new_width = ($new_height / $this->height) * $this->width;
        } else {
            $new_height = ($new_width / $this->width) * $this->height;
        }

        //输入要裁剪后的高度、宽度，这个宽高可能是和原图的比例不一致，那么就要改变要裁剪的宽高比，而且是依据原图的宽和高来确定改变要裁剪的宽和高
        //改变之后，再把新的裁剪宽和高分别与原来的宽和高相比，一般只有宽或者高其中发生了变化。
        //然后再根据哪个变化，如果是缩小的话，宽和高都进行同等程度的放大，其中一个和原来一样，另一个长度一定超过了原来的长度，在进行裁剪。
        //这样做的话，可以保证最后加上裁剪点的话，裁剪之后的图片和原图片长宽比例一致，不会发生变形。

        if ($new_width < $n_w) {   //如果新宽度小于新容器宽度
            $r = $n_w / $new_width;  //按长度求出等比例因子
            $new_width *= $r;   //扩展填充后的长度
            $new_height *= $r;  //扩展填充后的高度
            $cut_height = ($new_height - $n_h) / 2;
            //求出裁剪点的高度
        }


        if ($new_height < $n_h) {  //如果新高度小于容器高度
            $r = $n_h / $new_height;   //按高度求出等比例因子
            $new_width *= $r; //扩展填充后的长度
            $new_height *= $r; //扩展填充后的高度
            $cut_width = ($new_width - $n_w) / 2;
            //求出裁剪点的长度
        }

        $this->new = imagecreatetruecolor($n_w,$n_h);

        imagecopyresampled($this->new,$this->img,0,0,$cut_width,$cut_height,$new_width,$new_height,$this->width,$this->height);

    }



    //加载图片，各种类型，返回图片的资源句柄
    private function getFromImg($file,$type){
        switch ($type) {
            case 1:
                $img = imagecreatefromgif($file);
                break;
            case 2:
                $img = imagecreatefromjpeg($file);
                break;
            case 3:
                $img = imagecreatefrompng($file);
                break;
            default:
                Tool::alert('警告：此图片类型本系统不支持！');
                break;
        }
        return $img;
    }


    //图像输出
    public function out($name = ''){
        $start = substr($this->file, 0,-strlen(strchr($this->file,'.')));
        $end = strchr($this->file,'.');
        $this->file = $start.$name.$end;
        imagepng($this->new,$this->file);
        imagedestroy($this->img);
        imagedestroy($this->new);
    }



}



















?>