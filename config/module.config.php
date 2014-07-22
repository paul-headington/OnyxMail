<?php
return array(
    "onyx_mail" => array(
        "transport_method" => "sendmail", // options are "sendmail" or "smtp"
        "smtp" => array(
            "name" => "localhost.localdomain",
            "host" => "127.0.0.1",
            "connection_class" => "login",
            'connection_config' => array(
                'username' => 'user',
                'password' => 'pass',
            ),
        ),
    )
);