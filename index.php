<?php
require_once 'db_config.php';
require_once 'php/pdo.class.php';

$db = Database::getInst();

$action = isset($_GET['action']) ? $_GET['action'] : '';

if($action == 'load-database') {
    try {
        $sql = file_get_contents('db_data.sql');
        $db->exec($sql);
        echo 'Данные успешно загружены !!!<br>';
        echo '<a href="categories.html">Терерь перейдите на страницу задания с деревом категорий</a>';
    } catch (PDOException $e) {
        echo 'Failed import SQL file: ' . $e->getMessage();
    }
}
else {
    echo 'Соединение с базой установлено !!! <br>Выберите действие:<br>';
    echo '<a href="index.php?action=load-database">Загрузить данные из файла: <b>db_data.sql</b></a><br><br>';
    echo '<a href="categories.html">Перейти на страницу задания с деревом категорий</a>';
}



