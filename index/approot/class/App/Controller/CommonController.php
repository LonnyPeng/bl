<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use QR\QRcode;
use Ihuyi\Sms;

class CommonController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function qrcodeAction()
    {
        require_once INC_DIR . 'QRCode.php';

        $value = $this->param('key');
        $value = $this->funcs->encrypt($value, 'D', QRCODE_KEY);

        $errorCorrectionLevel = "L"; // 纠错级别：L、M、Q、H  
        $matrixPointSize = "4"; // 点的大小：1到10


        QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
    }

    public function phoneCodeAction()
    {
        $phone = trim($this->param('phone'));

        if (!isPhone($phone)) {
            return new JsonModel('error', '手机号码格式错误');
        }
        
        $code = '';
        for($i=0; $i<6; $i++) {
            $code .= mt_rand(1, 9);
        }

        //调用第三方API发送短信验证码
        require_once INC_DIR . 'Ihuyisms.php';
        $sms = new Sms(require_once CONFIG_DIR . 'ihuyisms.config.php');
        $result = $sms->send(array(
            'template' => "1", // 短信模板编号
            'phone' => "$phone", // 短信接收者
            'code' => "$code", // 验证码
        ));
        if ($result['code'] == 2) {
            $sql = "DELETE FROM t_phone_verif WHERE phone_number = ?";
            $this->locator->db->exec($sql, $phone);

            $sql = "INSERT INTO t_phone_verif 
                    SET phone_number = ?,
                    phone_code = ?";
            $this->locator->db->exec($sql, $phone, $code);

            return JsonModel::init('ok', '');
        } else {
            return new JsonModel('error', $result['msg']);
        }
    }
}
