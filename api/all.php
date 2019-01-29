<?php

require('../config.php');

$city = $_GET['city'];
$state = $_GET['state'];
$country_code = $_GET['country_code'];
$lat = $_GET['lat'];
$lng = $_GET['lng'];

//$weather = file_get_contents('http://api.openweathermap.org/data/2.5/weather?appid=f7f3565d3976180bed8a25f9d9281583&q=' . $q);
//$weather_url = "http://api.openweathermap.org/data/2.5/weather?q={$city},{$state},{$country_code}&appid=f7f3565d3976180bed8a25f9d9281583";
$weather_url = "http://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lng}&appid=" . OPEN_WEATHER_API_API_KEY;
$extract_url = "https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro&explaintext&redirects=1&origin=*&titles=" . urlencode($city . ", " . $state);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $weather_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);

//$weather = file_get_contents($url);

$part1 = $city;
$extract = file_get_contents($extract_url);

//var_dump($extract, $extract_url);

$weather = json_decode($output);
$extract = json_decode($extract);

$temperature_f = ($weather->main->temp - 273.15) * (9/5) + 32; // convert to fehrenheit
//$summary = substr(current($extract->query->pages)->extract, 0, 150);

if (preg_match('/^.{1,150}\b/s', current($extract->query->pages)->extract, $match)) {
	$summary = $match[0] . '...';
} else {
	$summary = "";
}

header('Content-Type: application/json');
echo json_encode(array($temperature_f, $summary, 0, $_GET['hash']));
