<?php
require_once "db.php";
require_once "functions.php";

global $db;
$class_category = 3; // 1 - excavator, 2 - Аuto-concrete pumps, 3 - Cranes
$top_category = 2; // 1 - new, 2 - used trucks

$ch = curl_init();

$base_url = "https://en.dindang168.com:8002/goods/";
for ($c = 1; $c <= $class_category; $c++) {
    for ($t = 1; $t <= $top_category; $t++) {
        $page = 1;
        while (true) {
            echo 'Page: ' . $page . PHP_EOL;
            $query_params = [
                'page' => $page,
                'class_category' => $c,
                'top_category' => $t,
            ];
            $query_string = http_build_query($query_params);
            $url = $base_url . '?' . $query_string;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $page++;
            if (curl_errno($ch)) {
                echo 'cURL error: ' . curl_error($ch);
            } else {
                $data = json_decode($response);
                foreach ($data->results as $item) {
                    try {
                        saveItem($item);
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage();
                        continue;
                    }
                }
                if (is_null($data->next)) break;
            }
        }
    }
}
$db->close();