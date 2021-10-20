<?php

/**
 * Implementation of Delete Task.
 */
function todolist_delete_task(){
    $id = arg(1);
    $num_updated = db_delete('todolist_task')
    ->condition('tid', $id, '=')
    ->execute();
    drupal_set_message(t('Task has been deleted!'));
    drupal_goto('todolist/list');
}