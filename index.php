<?php

echo phpinfo();
define('APP_DIR', __DIR__ . '/');
include APP_DIR . "clases/Status.php";
include APP_DIR . "clases/ToDo.php";



try {
    $list = new ToDo('tomorrow');
    $list->addTask('buy milk', 10);
    $list->addTask('meet Fred', 5);
    $list->addTask('take umbrella', 10);
    $list->addTask('phone Dave', 8);
    $list->addTask('set alarm', 5);
    $list->completeTask('65b0267d041d6');
    $list->completeTask('65aeaf8397107');
    $list->deleteTask('65b0267d041ee');
} catch (Exception $exception) {
    var_dump($exception->getMessage());
}

$tasks = $list->getTasks();