<?php
$p = __DIR__ . '/cafetin.sql';
$db = new PDO('sqlite:' . $p);
$t = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $t);
