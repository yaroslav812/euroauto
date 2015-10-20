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

header("Content-type: application/json; charset=utf8");

switch ($_POST['action']) {
    case 'get-all-categories':
        $ajax->getAllCategories();
        break;
    case 'get-filtered-categories':
        $ajax->getFilteredCategories($filter);
        break;
    case 'remove-category':
        $ajax->removeCategory($id_category);
        break;
}