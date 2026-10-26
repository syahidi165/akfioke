<?php
// config/mail.php

return [
    'default' => [
        'host'       => 'smtp.gmail.com',
        'port'       => 587,
        'username'   => 'yayasan.alkahfibatam@gmail.com',
        'password'   => '', // Disimpan terenkripsi pada settings unit/sistem
        'encryption' => 'tls', // tls or ssl
        'from_name'  => 'Yayasan Islam Al Kahfi Batam',
        'from_email' => 'yayasan.alkahfibatam@gmail.com',
    ]
];
