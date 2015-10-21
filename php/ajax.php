<?php
require_once '../db_config.php';
require_once 'pdo.class.php';
require_once 'ajax.class.php';

// если action не задан то выход
if (!isset($_POST['action'])) {
    exit;
}

$ajax = new AjaxData();

// обрабатываем $_POST параметры
$id_category = isset($_POST['id']) ? $ajax->mystrip($_POST['id']) : -1;
$filter = isset($_POST['filt']) ? $ajax->mystrip($_POST['filt']) : '';
$name = isset($_POST['name']) ? $ajax->mystrip($_POST['name']) : '';
$level = isset($_POST['deep']) ? $ajax->mystrip($_POST['deep']) : 0;

// устанавливаем заголовки
header("Content-type: application/json; charset=utf8");
header('Cache-Control: no-store, no-cache');
header('Expires: ' . date('r'));

// отдаем на клиент данные в JSON
switch ($_POST['action']) {
    case 'get-all-categories':
        $ajax->getAllCategories($level);
        break;
    case 'get-filtered-categories':
        $ajax->getFilteredCategories($filter, $level);
        break;
    case 'edit-category':
        $ajax->editCategory($id_category, $name);
        break;
    case 'remove-category':
        $ajax->removeCategory($id_category);
        break;
}