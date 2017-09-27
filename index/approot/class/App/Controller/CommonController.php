<?php

namespace App\Controller;

use QR\QRcode;

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
}
