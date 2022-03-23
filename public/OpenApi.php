<?php
require("../vendor/autoload.php");
$openapi = \OpenApi\scan('../app');
header('Content-Type: application/json');
echo $openapi->toJson();