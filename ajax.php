<?php
require('ajax.class.php');

// если action не задан то выходим
if(!isset($_POST['action'])) {
    exit;
}

$ajax = new AjaxData();

// обрабатываем $_POST параметры
$id_category = isset($_POST['id']) ? $ajax->mystrip($_POST['id']) : -1;
$filter = isset($_POST['filt']) ? $ajax->mystrip($_POST['filt']) : '';
$level = isset($_POST['deep']) ? $ajax->mystrip($_POST['deep']) : 0;

header("Content-type: application/json; charset=utf8");
header('Cache-Control: no-store, no-cache');
header('Expires: ' . date('r'));

switch ($_POST['action']) {
    case 'get-all-categories':
        $ajax->getAllCategories($level);
        break;
    case 'get-filtered-categories':
        $ajax->getFilteredCategories($filter, $level);
        break;
    case 'remove-category':
        $ajax->removeCategory($id_category);
        break;
}