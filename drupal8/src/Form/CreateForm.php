<?php

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CreateForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   */
  public function getFormId() {
    return 'create_task_form';
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
        '#type' => 'date',
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
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

    $query = \Drupal::database();
    $query->insert('todolist_task')
      ->fields(array(
          'task_uid' => $user->get('uid')->value,
          'task_title' => $form_state->getValue('title'),
          'task_content' => $form_state->getValue('content'),
          'status' => 'new',
          'task_date' => $form_state->getValue('date'),
      ))
      ->execute();

    $messenger = \Drupal::messenger();
    $messenger->addMessage('Task has been added!');

    // Redirect to home
    $form_state->setRedirect('<front>');
  }
}