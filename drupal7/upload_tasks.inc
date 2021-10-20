<?php
function upload_tasks_form($form, &$form_state){
  $form['csv_upload'] = array(
    '#type' => 'file',
    '#title' => 'Choose a file',
    '#size' => 22,
    '#upload_validators' => array('file_clean_name' => array()),
  );
  
  $form['submit_button'] = array(
    '#type' => 'submit',
    '#value' => 'Submit',
 );

  $form['#validate'][] = 'upload_tasks_form_validate';
  $form['#submit'][] = 'upload_tasks_form_submit';
  return $form;
}

function upload_tasks_form_validate($form, &$form_state) {
  $validators = array('file_validate_extensions' => array('csv'));
  // Check for a new uploaded file.
  $file = file_save_upload('csv_upload', $validators);
  if (isset($file)) {
    // File upload was attempted.
    if ($file) {
      // Put the temporary file in form_values so we can save it on submit.
      $form_state['values']['csv_upload_file'] = $file;

    }
    else {
      // File upload failed.
      form_set_error('csv_upload', t('The file could not be uploaded.'));
    }
  }
}

function upload_tasks_form_submit($form, &$form_state) {
    $file = $form_state['values']['csv_upload_file'];
    $file->status = FILE_STATUS_PERMANENT;
    $file->filename = str_replace(' ', '_', $file->filename);
    file_save($file);

    $csv_file = file_load($file->fid);
    $file = fopen($csv_file->uri, "r");


    while(! feof($file))
      {
        $customer = fgetcsv($file);
        db_insert('todolist_task') 
            ->fields(array(
            'task_title' => $customer[0],
            'task_content' => $customer[1],
            'status' => $customer[2],
            'task_date' => date('Y-m-d',strtotime($customer[3])),
            ))
            ->execute();
      }

    fclose($file);
    drupal_set_message('CSV data added to the database');
    drupal_goto('todolist/list');
}

function file_clean_name($file) {
  $file->filename = str_replace(' ', '_', $file->filename);
}
