<?php
require './vendor/autoload.php';
$app = (new eliteApiClient\App())->get();
$app->run();
