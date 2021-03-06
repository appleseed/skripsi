<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sprintController
 *
 * @author arkananta
 */
class SprintController extends adminController {

    public $layout = '//layouts/column2';

    //put your code here
    public function actionCreate() {
        $sprint = new sprint;
        if (isset($_POST['sprint'])) {
            $sprint->attributes = $_POST['sprint'];
            if ($sprint->validate()) {
                $sprint->save(false);
                if (isset($_POST['task_sprint'])) {
                    if (is_array($_POST['task_sprint']) && count($_POST['task_sprint']) > 0) {
                        foreach ($_POST['task_sprint'] as $row_sprint) {
                            //save ke task_sprint
                            Yii::app()->db->createCommand()->insert('task_sprint', array('task_task_id' => $row_sprint,
                                'sprint_sprint_id' => $sprint->sprint_id));
                        }
                    }
                }
                //berhasil nambah data sprint
                Yii::app()->user->setFlash('success', 'Sprint Berhasil ditambah');
                $this->redirect(array('/sprint/kanban', 'id' => $sprint->sprint_id));
            }
        }
        $task_project = task::model()->getAllTask();
        $this->title = 'Buat Sprint';
        $this->render('sprint_create', array('sprint' => $sprint,
            'task_project' => $task_project));
    }

    /**
     * 
     */
    public function actionUpdate($id) {
        $sprint = sprint::model()->findByPk($id);
        if (isset($_POST['sprint'])) {
            $sprint->attributes = $_POST['sprint'];
            if ($sprint->validate()) {
                $sprint->save(false);
                if (is_array($_POST['task_sprint']) && count($_POST['task_sprint']) > 0) {
                    //delete all task in sprint 
                    Yii::app()->db->createCommand()->delete('task_sprint', 'sprint_sprint_id = :sprint_id', array(':sprint_id' => $id));
                    foreach ($_POST['task_sprint'] as $row_sprint) {
                        //save ke task_sprint
                        Yii::app()->db->createCommand()->insert('task_sprint', array('task_task_id' => $row_sprint,
                            'sprint_sprint_id' => $sprint->sprint_id));
                    }
                }
                Yii::app()->user->setFlash('success', 'Succeed updating sprint');
                $url = $this->createUrl('/sprint/kanban', array('id' => $id));
                $this->redirect($url);
            }
        }
        $task_sprint = task::model()->getAllTaskBySprintId($id);
        $task_project = task::model()->getAllTask();
        $task_project = $this->filterTaskSprint($task_project, $task_sprint);
        $this->title = "Update " . $sprint->sprint_name;
        $this->render('sprint_create', array('sprint' => $sprint,
            'task_sprint' => $task_sprint,
            'task_project' => $task_project));
    }

    /**
     * fungsi buat view data sprint
     */
    public function actionView($id) {
        $sprint = sprint::model()->getSprintBySprintId($id);
        $task_sprint = task::model()->getAllTaskBySprintId($id);
        $this->title = $sprint['sprint_name'];
        $this->render('sprint_view', array('sprint' => $sprint,
            'task_sprint' => $task_sprint));
    }

    /**
     * ajax request buat assign task
     */
    public function actionAssign_task() {
        $task_id = $_POST['task_id'];
        //get the task 
        $task = task::model()->getTaskById($task_id);
        if (count($task) > 0) {
            if (empty($task['task_assign_user_id'])) {
                //assign to user
                Yii::app()->db->createCommand()->update('task', array('task_assign_user_id' => $this->admin_auth->user_id), 'task_id=:id', array(':id' => $task_id));
                echo json_encode(array('error' => false,
                    'username' => $this->admin_auth->username,
                    'btn' => 'btn-danger'));
            } elseif ($task['task_assign_user_id'] == $this->admin_auth->user_id) {
                Yii::app()->db->createCommand()->update('task', array('task_assign_user_id' => null), 'task_id=:id', array(':id' => $task_id));
                echo json_encode(array('error' => false,
                    'btn' => 'btn-success'));
            } else {
                echo json_encode(array('error' => true,
                    'message' => 'Its not your task'));
            }
        } else {
            echo json_encode(array('error' => true,
                'message' => 'task not found'));
        }
    }

    /**
     * action ajax buat start data
     */
    public function actionStart_task() {
        if (isset($_POST['task_id'])) {
            $data = task::model()->getTaskById($_POST['task_id']);
            if (count($data) > 1) {
                if ($this->admin_auth->user_id == $data['task_assign_user_id']) {
                    Yii::app()->db->createCommand()->update('task', array('task_start_datetime' => date("Y-m-d H:i:s")), "task_id = :task_id", array(':task_id' => $_POST['task_id']));
                    echo CJavaScript::jsonEncode(array('error' => false));
                }
                else
                    echo CJavaScript::jsonEncode(array('error' => true));
            }
            else
                echo CJavaScript::jsonEncode(array('error' => true));
        } else {
            echo CJavaScript::jsonEncode(array('error' => true));
        }
    }

    /**
     * method buat kanban chart
     */
    public function actionKanban($id) {
        $this->layout = '//layouts/column_scrum_view';
        $sprint = sprint::model()->getSprintBySprintId($id);
        $task_sprint = task::model()->getAllTaskBySprintId($id);
        $this->title = $sprint['sprint_name'];
        $this->render('sprint_kanban', array('sprint' => $sprint,
            'task_sprint' => $task_sprint));
    }

    /**
     * buat filter task dari semua task di kurangin task yang ada di sprint
     * @param Array $task_project daftar semua task
     * @param Array $task_sprint daftar semua task dalam sprint
     */
    private function filterTaskSprint($task_project = array(), $task_sprint = array()) {
        $new_project = array();
        foreach ($task_project as $row_project) {
            $is_found = FALSE;
            foreach ($task_sprint as $row_sprint) {
                if ($row_sprint['task_id'] == $row_project['task_id']) {
                    $is_found = TRUE;
                }
            }
            if (!$is_found) {
                $new_project[] = $row_project;
            }
        }
        return $new_project;
    }
    /**
     * action buat update task progress
     */
    public function actionUpdate_kanban_progress() {
       $data_feedback = array('error' => false);
       $feedback = sprint::model()->updateKanbanStatus($_POST['task_id'], $_POST['status']);
       if(!$feedback) {
           $data_feedback = array('error' => true,
                                  'message' => 'error');
       }
       echo CJavaScript::jsonEncode($data_feedback);
       Yii::app()->end();
    }
}

?>
