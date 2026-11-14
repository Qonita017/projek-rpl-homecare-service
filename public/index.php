<?php
session_start();
require_once "../app/Config/config.php";
require_once "../app/Config/Database.php";
require_once "../app/Core/Controller.php";
require_once "../app/Core/Router.php";

$router = require_once "../routes/web.php";
$url = isset($_GET["url"]) ? "/" . rtrim($_GET["url"], "/") : "/";
$router->dispatch($url);
