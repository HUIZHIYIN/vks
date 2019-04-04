<?php
require_once __DIR__ . '/includes/database.inc.php';

$db = new Database();

$queryCountries = "SELECT country.code as id,  country.name as text  FROM world.Country As country limit 20;";
$countries = $db->get_all($queryCountries);


echo (json_encode($countries));