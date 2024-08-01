<?php


/**
 * @param $item
 * @return void
 * @throws Exception
 */
function saveItem($item): void
{
    global $db;
    $year = getYear($item->made_time);
    if ($year === "2024" && count($item->image_list) > 0) {
        $tags = explode(",", $item->tags);
        $year = '';
        $model = null;
        $mass = null;
        $mass_typeEn = '';
        $mass_typeRu = '';
        $mass_typeZh = '';
        $categoryEn = '';
        $categoryRu = '';
        $categoryZh = '';
        $stateEn = '';
        $stateRu = '';
        $stateZh = '';
        if (str_contains($item->category->name, 'used')) {
            $stateEn = 'Used';
            $stateRu = 'Б/у';
            $stateZh = '用過的';
        } elseif (str_contains($item->category->name, 'new')) {
            $stateEn = 'New';
            $stateRu = 'Новый';
            $stateZh = '新的';
        }
        if (preg_match('/^[A-Z][A-Z0-9-]+$/', $item->name)) {
            preg_match('/^[A-Z][A-Z0-9-]+$/', $item->name, $matches);
            $model = $model ?? $matches[0];
        }
        foreach ($tags as $tag) {
            if (preg_match('/^[A-Z][A-Z0-9-]+$/', $tag)) {
                $model = $tags[count($tags) - 1];
            } elseif (preg_match('/^[A-Z][A-Za-z0-9-]+$/', $tag)) {
                $model = $model ?? $tag;
            }
            if (ctype_digit($tag)) {
                $year = $tag;
            }
            if (str_contains($tag, 'ton')) {
                $mass = $mass ?? $tag;
            }
            if (str_contains($tag, 'crane')) {
                $categoryEn = 'Cranes';
                $categoryRu = 'Краны';
                $categoryZh = '起重机械';
                $mass_typeEn = 'Tons';
                $mass_typeRu = 'Тонны';
                $mass_typeZh = '吨';
            } elseif (str_contains($tag, 'excavator')) {
                $categoryEn = 'Сonstruction machines';
                $categoryRu = 'Строительные машины';
                $categoryZh = '工程机械';
                $mass_typeEn = 'Tons';
                $mass_typeRu = 'Тонны';
                $mass_typeZh = '吨';
            } elseif (str_contains($tag, 'concrete machinery')) {
                $categoryEn = 'Аuto-concrete pumps';
                $categoryRu = 'Авто–бетононасосы';
                $categoryZh = '混凝土机械';
                $mass_typeEn = 'Meter';
                $mass_typeRu = 'метр';
                $mass_typeZh = '仪表';
            }
            if (preg_match('/^[0-9]+[a-z]+$/', $tag)) {
                $mass = $mass ?? $tag;
            }
        }
        $carData = [
            'source_id' => $item->id,
            'name' => json_encode(['en' => $item->name, 'ru' => '', 'zh-CN' => '']),
            'model' => $model,
            'category' => json_encode(['en' => $categoryEn, 'ru' => $categoryRu, 'zh-CN' => $categoryZh]),
            'year' => $year,
            'state' => json_encode(['en' => $stateEn, 'ru' => $stateRu, 'zh-CN' => $stateZh]),
            'full_weight' => $mass ? json_encode(
                ['en' => (int)$mass . ' ' . $mass_typeEn,
                    'ru' => (int)$mass . ' ' . $mass_typeRu,
                    'zh-CN' => (int)$mass . ' ' . $mass_typeZh
                ]) : null,
            'origin' => json_encode(['en' => 'China', 'ru' => 'Китай', 'zh-CN' => '中國']),
            'created_at' => date("Y-m-d H:i:s"),
        ];
        $stmt = $db->prepare("INSERT INTO cars (source_id, name, model, category, year, state, full_weight, origin, created_at) 
                VALUES (:source_id, :name, :model, :category, :year, :state, :full_weight, :origin, :created_at)");
        $stmt->bindParam(':source_id', $carData["source_id"]);
        $stmt->bindParam(':name', $carData["name"]);
        $stmt->bindParam(':model', $carData["model"]);
        $stmt->bindParam(':category', $carData["category"]);
        $stmt->bindParam(':year', $carData["year"]);
        $stmt->bindParam(':state', $carData["state"]);
        $stmt->bindParam(':full_weight', $carData["full_weight"]);
        $stmt->bindParam(':origin', $carData["origin"]);
        $stmt->bindParam(':created_at', $carData["created_at"]);
        $stmt->execute();
        $carId = $db->lastInsertId();
        $cover = saveImage($item->cover);
        $stmt = $db->prepare("INSERT INTO images (car_id, path, type, size) VALUES (:car_id, :path, :type, :size)");
        $stmt->bindParam(':car_id', $carId);
        $stmt->bindParam(':path', $cover['path']);
        $stmt->bindParam(':type', $cover['type']);
        $stmt->bindParam(':size', $cover['size']);
        $stmt->execute();
        foreach ($item->image_list as $order => $url) {
            $image = saveImage($url);
            $ord = $order + 1;
            $stmt = $db->prepare("INSERT INTO images (car_id, path, `order`, type, size) VALUES (:car_id, :path, :order, :type, :size)");
            $stmt->bindParam(':car_id', $carId);
            $stmt->bindParam(':path', $image['path']);
            $stmt->bindParam(':order', $ord);
            $stmt->bindParam(':type', $image['type']);
            $stmt->bindParam(':size', $image['size']);
            $stmt->execute();
        }
    }
}

/**
 * @throws Exception
 */
function getYear(string $date): string
{
    $dateTimeString = $date;
    $dateTime = new DateTime($dateTimeString);
    return $dateTime->format('Y');
}

/**
 * @param string $imageUrl
 * @return array
 * @throws Exception
 */
function saveImage(string $imageUrl): array
{
    set_time_limit(60);
    $chImg = curl_init($imageUrl);
    curl_setopt($chImg, CURLOPT_RETURNTRANSFER, true);
    $imageData = curl_exec($chImg);
    if ($imageData === false) {
        throw new Exception(curl_error($chImg));
    }
    curl_close($chImg);
    $urlParse = explode('.', $imageUrl);
    $fileExtension = end($urlParse);
    $imageHash = md5($imageUrl) . '.' . $fileExtension;
    $savePath = 'images/' . $imageHash;
    $result = file_put_contents($savePath, $imageData);
    if ($result === false) {
        throw new Exception('Failed to save image.');
    }
    return ['path' => $savePath, 'type' => $fileExtension, 'size' => filesize($savePath)];
}