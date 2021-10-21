<?php

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class EditForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   */
  public function getFormId() {
    return 'edit_task_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param int $id
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
    $conn = Database::getConnection();
    $data = array();
    if (isset($id)) {
      $query = $conn->select('todolist_task', 'todo')
        ->condition('tid', $id)
        ->fields('todo');
      $data = $query->execute()->fetchAssoc();
    }

    $form['title'] = array(
      '#type' => 'textfield', 
      '#title' => 'Title',
      '#required' => TRUE, 
      '#default_value' => (isset($data['task_title'])) ? $data['task_title'] : '',
    );  
    
    $form['content'] = array(
        '#type' => 'textarea',
        '#title' => 'Description',
        '#required' => FALSE,
        '#default_value' => (isset($data['task_content'])) ? $data['task_content'] : '',
    );

    $form['status'] = array(
        '#type' => 'select',
        '#title' => 'Status',
        '#default_value' => (isset($data['status'])) ? $data['status'] : '',
        '#options' => array('new', 'active', 'complete'),
        '#required' => TRUE,
    );

    $form['date'] = array(
        '#type' => 'date',
        '#date_format' => 'Y-m-d',
        '#required' => TRUE,
        '#default_value' => (isset($data['task_date'])) ? $data['task_date'] : '',
    );

    $form['submit_button'] = array(
        '#type' => 'submit',
        '#value' => 'Update ToDo',
    );
    
    $form['#validate'][] = 'todolist_edit_task_validate';
    $form['#submit'][] = 'todolist_edit_task_submit';

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

    if ($form_state->getValue('date') < date('Y-m-d')){
      $form_state->setErrorByName('date', $this->t('The Date cannot be in the past.'));
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param int $id
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $id = null) {
    $statuses = array('new', 'active', 'complete');

    $data = array(
      'task_title' => $form_state->getValue('title'),
      'task_content' => $form_state->getValue('content'),
      'status' => $statuses[$form_state->getValue('status')],
      'task_date' => $form_state->getValue('date'),
    );

    if (isset($id)) {
      \Drupal::database()
        ->update('todolist_task')
        ->fields($data)
        ->condition('tid', $id)
        ->execute();
    }

    $messenger = \Drupal::messenger();
    $messenger->addMessage('Task has been Updated!');

    // Redirect to home
    $form_state->setRedirect('<front>');
  }
}