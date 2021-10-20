<?php

/**
 * Implementation of Edit Task Form().
 */

function todolist_edit_task_form($form, &$form_state){
    $id = arg(1);

    $result = db_query('SELECT * FROM {todolist_task} WHERE tid = :id', array(':id' => $id));
    $record = $result->fetchObject();
    
    if($record){
        if($record->status != 'closed'){
            $status_index = array_search($record->status, array('new', 'active', 'complete'));

            $form['title'] = array(
                '#type' => 'textfield', 
                '#title' => 'Title',
                '#default_value' => $record->task_title,
                '#required' => TRUE, 
            );  
            
            $form['content'] = array(
                '#type' => 'textarea',
                '#title' => 'Description',
                '#default_value' => $record->task_content,
                '#required' => FALSE,
            );
            
            $form['status'] = array(
                '#type' => 'select',
                '#title' => 'Status',
                '#default_value' => $status_index,
                '#options' => array('new', 'active', 'complete'),
                '#required' => TRUE,
            );

            $form['date'] = array(
                '#type' => 'date_popup',
                '#date_format' => 'Y-m-d',
                '#default_value' => $record->task_date,
                '#required' => TRUE,
            );
            
            // hidden for the body
            $form['id'] = array(
            '#type' => 'hidden',
            '#value' => $id,
            );

            $form['submit_button'] = array(
                '#type' => 'submit',
                '#value' => 'Update ToDo',
            );

            $form['#validate'][] = 'todolist_edit_task_validate';
            $form['#submit'][] = 'todolist_edit_task_submit';
            return $form;
        }else{
            drupal_set_message('The '.$record->task_title.' is a closed Task');
            drupal_goto('todolist/list');
        }
    }else{
        drupal_set_message('Not Found Task!.');
        drupal_goto('todolist/list');
    }
}


/**
 * Implementation of Edit Task Form Validation.
 */
function todolist_edit_task_validate($form, &$form_state) {
      if($form_state['values']['date'] < date('Y-m-d')){
        form_set_error('date', 'The Date cannot be in the past.');
      }
}

/**
 * Implementation of Edit Task Form Submit.
 */

function todolist_edit_task_submit($form, &$form_state)
{
    // print_r($form_state['complete form']['status']['#options'][$form_state['values']['status']]);die;
    $id = $form_state['values']['id'];
    $data = array(
        'task_title' => $form_state['values']['title'],
        'task_content' => $form_state['values']['content'],
        'status' => $form_state['complete form']['status']['#options'][$form_state['values']['status']],
        'task_date' => $form_state['values']['date'],
    );
    db_update('todolist_task')
        ->fields($data)
        ->condition('tid', $id, '=')
        ->execute();
    drupal_set_message('Task has been Updated!');
    drupal_goto('todolist/list');
}

