<?php

$q = $_GET['q'];

//$weather = file_get_contents('http://api.openweathermap.org/data/2.5/weather?appid=f7f3565d3976180bed8a25f9d9281583&q=' . $q);

$weather_url = "http://api.openweathermap.org/data/2.5/weather?q={$q}&appid=f7f3565d3976180bed8a25f9d9281583";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $weather_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);

//$weather = file_get_contents($url);
$extract = file_get_contents('https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro&explaintext&redirects=1&origin=*&titles=' . $q);

$weather = json_decode($extract);
$extract = json_decode($extract);

$temperature_f = ($weather->main->temp - 273.15) * (9/5) + 32; // convert to fehrenheit
$summary = substr(current($extract->query->pages)->extract, 0, 150);

if (!empty($summary)) {
	$summary = $summary . '...';
}

header('Content-Type: application/json');
echo json_encode(array($temperature_f, $summary));
