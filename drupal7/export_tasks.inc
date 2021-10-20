<?php
function export_tasks_form($form, &$form_state){
    $form['submit_button'] = array(
        '#type' => 'submit',
        '#value' => 'Export',
     );
    $form['#submit'][] = 'export_tasks_form_submit';
    
    return $form;
}

function export_tasks_form_submit($form, &$form_state){
    drupal_add_http_header('Content-Type', 'text/csv');
    drupal_add_http_header('Content-Disposition', 'attachment; filename=tasks.csv');
    $results = db_query("SELECT * FROM {todolist_task}");
    $csvdata = 'ID,Task Title,Task Content,Status,Date' . PHP_EOL;
    foreach ($results as $record) {
    $row = array();
    $row[] = $record->tid; 
    $row[] = $record->task_title; 
    $row[] = $record->task_content;
    $row[] = $record->task_status;
    $row[] = $record->task_date; 
    $csvdata .= implode(',', $row) . PHP_EOL;
    }
    print $csvdata;
    drupal_exit();

}