<?php
return array(
    "onyx_mail" => array(
        "transport_method" => "sendmail", // options are "sendmail" or "smtp"
        "smtp" => array(
            "name" => "localhost.localdomain",
            "host" => "127.0.0.1",
            "port" => 25,
            "connection_class" => "login",
            'connection_config' => array(
                'username' => 'user',
                'password' => 'pass',
            ),
        ),
        "defaults" => array(
            "from" => array("no-reply@localhost","no-reply"),
            "encoding" => "UTF-8",
        ),
    )
);

//for smtp settings see http://framework.zend.com/manual/2.1/en/modules/zend.mail.smtp.options.html#zend-mail-smtp-options