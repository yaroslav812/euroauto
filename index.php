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
?>
Ура !!! Соединение с базой установлено...<br><br>Выберите действие:
<ol>
  <li>
    <a href="index.php?action=load-database">Загрузить данные из файла: db_data.sql</a> - DROP/CREATE TABLE category + INSERT(data)
  </li>
  <li>
    <a href="categories.html">Перейти на страницу задания с деревом категорий</a>
  </li>
</ol>
<?php } ?>