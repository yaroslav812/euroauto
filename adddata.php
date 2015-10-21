<?php
require ('pdo.class.php');
/*
$a = ['Зеркало','Масла','Шины','Диски','Колеса','Глушители','Капот','Щетки','Аккумуляторы',
    'Бампер','Колодки','Мотор','Двигатель','Руль','Фары','Багажник','Сидение','Огнетушитель',
    'Домкрат','Магнитола','Ксенон','Аэрография','Коробка передач', 'Аптечка', 'Тосол', 'Антифриз',
'Подушка безопасности','Мотошины','Автохимия','Аксессуары','Мото аксессуары', 'Инструмент'];
*/

$db = Database::getInst();
$a = array(326,327,328,329,330,331,332);
foreach($a as $v) {
    for ($i = 1; $i < 200; $i++) {
        //$sql = "INSERT INTO category(parent_category_id, name) VALUES(298, 'Очень глубоко №$i');";
        $sql = sprintf("INSERT INTO category(parent_category_id, name) VALUES(%d, 'Много-%03d');", $v, $i);
        $upd = 0;
        try {
            $upd = $db->exec($sql);
        } catch (Exception $e) {
            die('PDO query error: ' . $sql);
        }
    }
}

echo'ok - '.$upd;123
//for


