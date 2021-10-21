<?php

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ExportForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   */
  public function getFormId() {
    return 'export_tasks_form';
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
    $form['submit_button'] = array(
      '#type' => 'submit',
      '#value' => 'Export',
    );

    $form['#submit'][] = 'export_tasks_form_submit';
    
    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
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
}