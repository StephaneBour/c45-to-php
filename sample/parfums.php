<?php
    include(__DIR__ . '/../C45.class.php');

    $c45 = new C45('parfums');
    $c45->parse();

    var_dump($c45->php);