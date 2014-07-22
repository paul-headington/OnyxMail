<?php
return array(
    "OnyxMail" => array(
        "transportMethod" => "sendmail", // options are "sendmail" or "smtp"
        "smtp" => array(
            "name" => "localhost.localdomain",
            "host" => "127.0.0.1",
            "connectionClass" => "login",
            'connectionConfig' => array(
                'username' => 'user',
                'password' => 'pass',
            ),
        ),
    )
);