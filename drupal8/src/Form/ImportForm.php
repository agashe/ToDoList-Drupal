<?php

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ImportForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   */
  public function getFormId() {
    return 'import_tasks_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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

  /**
   * Validate the form
   * 
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * 
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

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

  /**
   * Form submission handler.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
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

    $messenger = \Drupal::messenger();
    $messenger->addMessage('CSV data added to the database');

    // Redirect to home
    $form_state->setRedirect('<front>');
  }
}

function file_clean_name($file) {
  $file->filename = str_replace(' ', '_', $file->filename);
}