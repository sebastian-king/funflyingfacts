<?php

    $jsonData = file_get_contents('airports.json');
    $json = json_decode($jsonData, true);

    foreach ($json as $key => $value) {
        echo $value['city'] . ', ' . $value['countryName'] . PHP_EOL;
    }

    echo count($json);
    
?>