<?php

$key = "123";

if (!isset($_GET['key'])) {
	die('403');
} elseif ($_GET['key'] != $key) {
	die('403');
}

echo "Hello Lonny";