<?php

/**
 * @file
 * Contains \Drupal\todolist\Controller\TodoListController.
 */

namespace Drupal\todolist\Controller;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller routines for todolist routes.
 */
class TodoListController {

  /**
   * Returns all tasks.
   *
   * @return array
   */
  public function index() {
    //create table header
    $header_table = array(
      'id' => t('ID'),
      'title' => t('Title'),
      'content' => t('Content'),
      'status' => t('Status'),
      'date' => t('Date'),
      'edit' => t('Edit'),
      'close' => t('Close'),
      'delete' => t('Delete'),
    );

    // get data from database
    $query = \Drupal::database()->select('todolist_task', 'todo');
    $query->fields('todo', ['task_uid', 'task_title', 'task_content', 'status', 'task_date']);
    
    // check if user can manage all tasks
    if (!\Drupal::currentUser()->hasPermission('manage all tasks'))
      $query->condition('task_uid', \Drupal::currentUser()->uid, '=');
    
    $results = $query->execute()->fetchAll();

    $rows = array();
    foreach ($results as $data) {
      $url_edit = Url::fromRoute('todolist.edit', ['id' => $data->task_uid], []);
      $url_close = Url::fromRoute('todolist.close', ['id' => $data->task_uid], []);
      $url_delete = Url::fromRoute('todolist.delete', ['id' => $data->task_uid], []);
      $linkEdit = Link::fromTextAndUrl('Edit', $url_edit);
      $linkClose = Link::fromTextAndUrl('Close', $url_close);
      $linkDelete = Link::fromTextAndUrl('Delete', $url_delete);

      //get data
      $rows[] = array(
        'id' => $data->tid,
        'title' => $data->task_title,
        'content' => $data->task_content,
        'status' => $data->status,
        'date' => $data->task_date,
        'edit' =>  $linkEdit,
        'close' => $linkClose,
        'delete' => $linkDelete,
      );
    }

    // render table
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => t('No results found.'),
    ];
    return $form;
  }

  /**
   * Delete task.
   *
   * @param int $id
   * @return void
   */
  public function delete($id = null) {
    \Drupal::database()
      ->delete('todolist_task')
      ->condition('tid', $id)
      ->execute();

    $messenger = \Drupal::messenger();
    $messenger->addMessage('Task has been deleted!');

    // Redirect to home
    return $this->redirect('<front>');
  }

  /**
   * Close task.
   *
   * @param int $id
   * @return void
   */
  public function close($id = null) {
    \Drupal::database()
      ->update('todolist_task')
      ->fields(array(
        'status' => 'closed',
      ))
      ->condition('tid', $id)
      ->execute();

    $messenger = \Drupal::messenger();
    $messenger->addMessage('Task has been closed!');

    // Redirect to home
    return $this->redirect('<front>');
  }
}
