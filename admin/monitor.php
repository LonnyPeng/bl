<?php

$key = "123";

if (file_get_contents('php://input') != $key) {
	die('403');
}

echo "Hello Lonny";