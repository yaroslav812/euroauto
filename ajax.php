<?php
require('ajax.class.php');

header("Content-type: application/json; charset=utf8");

$ajax = new AjaxData();

switch ($_POST['data']) {
    case 'get-all-categories':
        $ajax->getAllCategories();
        break;
    case 'remove-category':
        $ajax->removeCategory(555);
        break;
}