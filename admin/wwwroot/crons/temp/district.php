<?php

ini_set('max_execution_time', '0');

require __DIR__ . '/../../init.inc.php';

$plugins = $locator->get(PLUGIN_MANAGER);

$perg = array(
	'E' => "/^È/",
	'A' => "/^Ā/",
);

$sql = "SELECT * FROM t_district";
$data = $locator->db->getAll($sql);

$sql = "UPDATE t_district SET district_initial = ? WHERE district_id = ?";

foreach($data as $row) {
	if ($row['district_initial']) {
		continue;
	}

	$map = array('tl' => 'zh-CN', 'text' => $row['district_name']);
	$result = $plugins->funcs->translateGoogleApi($map, $status = true);
	if (!isset($result[0][1][2])) {
		continue;
	}

	$result = $result[0][1][2];
	foreach($perg as $key => $value) {
		if (preg_match($value, $result)) {
			$result = preg_replace($value, $key, $result);
		}
	}

	$initial = strtoupper(substr($result, 0, 1));
	// print_r($result);die;
	$locator->db->exec($sql, $initial, $row['district_id']);
}

// print_r($data);die;
