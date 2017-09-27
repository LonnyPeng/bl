<?php

return array(
    'default/index'     => '',
    'common/qrcode'     => 'qrcode/<key?:.*>.png',
    'order/info'        => 'order-<key?:\d{7,10}>',
    '*/*'               => '<_controller:[^/]+>/<_action:[^/]+>',
);
