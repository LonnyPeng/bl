<?php

namespace App\Controller;

use umeditor\Uploader;

class CommonController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function umeditorAction()
    {
    	return array();
    }

    public function imageUpAction()
    {
    	include INC_DIR . "umeditor/Uploader.class.php";

    	//上传配置
    	$config = array(
    	    "savePath" => SYS_DIR ,             //存储文件夹
    	    "maxSize" => 1024 * 1024 * 4 ,                   //允许的文件最大尺寸，单位KB
    	    "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" )  //允许的文件格式
    	);

    	$up = new \umeditor\Uploader( "upfile" , $config );
    	$callback = $this->param('callback');

    	$info = $up->getFileInfo();
        $path = str_replace(SYS_DIR, '', $info['url']);
        $url = 'sys/' . $path;
        //图片处理
        $fileInfo = $this->funcs->getFileInfo(SYS_DIR . $path);
        $width = 750;
        $height = ($width * $fileInfo[1]) / $fileInfo[0];
        $result = $this->funcs->setImage(SYS_DIR . $url, SYS_DIR, $width, $height);
        if ($result['status']) {
            $url = $result['content'];
        }
    	$info['url'] = $url;

    	/**
    	 * 返回数据
    	 */
    	if($callback) {
    	    echo '<script>'.$callback.'('.json_encode($info).')</script>';
    	} else {
    	    echo json_encode($info);
    	}

    	return false;
    }
}
