<?php

return array(
    'default/index'     => '',
    'wechat/callback' => 'oauth-callback',
    'common/qrcode'     => 'qrcode/<key?:.*>.png',
    '*/*'               => '<_controller:[^/]+>/<_action:[^/]+>',
);
