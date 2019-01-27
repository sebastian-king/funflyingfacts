<?php

    
    $jsonData = file_get_contents('https://en.wikipedia.org/w/api.php?action=opensearch&search=' . urlencode($_GET['city']). '&limit=1&format=json');
    $json = json_decode($jsonData, true);

    var_dump($json[2][0])

?>