<?php

    $jsonData = file_get_contents('http://api.openweathermap.org/data/2.5/weather?q=Glasgow,uk&appid=f7f3565d3976180bed8a25f9d9281583');
    $json = json_decode($jsonData, true);

    echo 'Temperature: ' . (($json['main']['temp'] - 273.15) * (9/5) + 32) . '˚F'. PHP_EOL;
    echo $json['weather'][0]['description'];
    
?>
