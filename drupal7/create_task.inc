<?php

/**
 * Implementation of Create Task Form().
 */
function todolist_create_task_form($form, &$form_state) {

    $form['title'] = array(
        '#type' => 'textfield', 
        '#title' => 'Title',
        '#required' => TRUE, 
    );  
    
    $form['content'] = array(
        '#type' => 'textarea',
        '#title' => 'Description',
        '#required' => FALSE,
    );
    $form['date'] = array(
        '#type' => 'date_popup',
        '#date_format' => 'Y-m-d',
        '#required' => TRUE,
    );
    $form['submit_button'] = array(
        '#type' => 'submit',
        '#value' => 'Add ToDo',
    );

    
    $form['#validate'][] = 'todolist_create_task_validate';
    $form['#submit'][] = 'todolist_create_task_submit';

    return $form;
}


/**
 * Implementation of Create Task Form Validation().
 */
function todolist_create_task_validate($form, &$form_state) {
  /*
     //Execute a count query to see if there's other similar values.
    $query = new EntityFieldQuery;
    $query->entityCondition('entity_type', 'field_collection_item')
      ->fieldCondition('field_unique', 'value', $value)
      ->count();
    $count = $query->execute();
    if ($count > 1) {
      form_set_error('field_unique', 'Unique field value is already used.');
    }
   */ 
    
    if($form_state['values']['date'] < date('Y-m-d')){
      form_set_error('date', 'The Date cannot be in the past.');
    }
}

/**
 * Implementation of Create Task Form Submit.
 */
function todolist_create_task_submit($form, &$form_state) {
    global $user;
    
    db_insert('todolist_task') 
            ->fields(array(
            'task_uid' => $user->uid,
            'task_title' => $form_state['values']['title'],
            'task_content' => $form_state['values']['content'],
            'status' => 'new',
            'task_date' => $form_state['values']['date'],
            ))
            ->execute();
    drupal_set_message(t('Task has been added!'));
    drupal_goto('todolist/list');
}
