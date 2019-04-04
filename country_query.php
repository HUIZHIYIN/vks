<?php
require_once __DIR__ . '/includes/database.inc.php';

$db = new Database();

$country = $_POST['country'] ? $_POST['country'] : null;

if(is_null($country)){
    $output = array("Please select a country first");
}else{
    $queryCountries = "SELECT Name FROM world.City where CountryCode ='". $country ."';";
    $output = $db->get_all($queryCountries);
}

echo json_encode($output);