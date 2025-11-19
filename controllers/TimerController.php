<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\Subtask;
use app\models\TimeLog;
use app\models\CardAssignment;
use app\models\User;

class TimerController extends Controller
{
    public function actionStartTimer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        $request = Yii::$app->request;
        $subtaskId = $request->post('subtask_id');
        $userId = Yii::$app->user->id;
        if (!$subtaskId) {
            return ['success' => false, 'message' => 'Subtask ID is required.'];
        }
        $subtask = Subtask::findOne($subtaskId);
        if (!$subtask) {
            return ['success' => false, 'message' => 'Subtask not found.'];
        }
        if (!$this->checkSubtaskAccess($subtask)) {
            return ['success' => false, 'message' => 'Access denied to this subtask.'];
        }
        try {
            $runningTimer = TimeLog::find()->where(['user_id' => $userId, 'end_time' => null])->one();
            if ($runningTimer) {
                $runningTimer->end_time = date('Y-m-d H:i:s');
                $runningTimer->calculateDuration();
                @$runningTimer->save();
            }
            $timeLog = new TimeLog();
            $timeLog->subtask_id = $subtaskId;
            $timeLog->card_id = $subtask->card_id;
            $timeLog->user_id = $userId;
            $timeLog->start_time = date('Y-m-d H:i:s');
            $timeLog->description = $request->post('description', 'Working on subtask');
            if ($timeLog->save()) {
                $user = User::findOne($userId);
                if ($user) {
                    $user->current_task_status = 'working';
                    @$user->save(false, ['current_task_status']);
                }
                if ($subtask->status !== 'in_progress') {
                    $subtask->status = 'in_progress';
                    @$subtask->save(false, ['status']);
                }
                return ['success' => true, 'log_id' => $timeLog->log_id, 'message' => 'Timer started successfully.'];
            }
            return ['success' => false, 'message' => 'Failed to start timer. Please try again.'];
        } catch (\Exception $e) {
            Yii::error('Exception during startTimer: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An internal error occurred.'];
        }
    }

    public function actionStopTimer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        $userId = Yii::$app->user->id;
        $logId = Yii::$app->request->post('log_id');
        $runningTimer = null;
        if ($logId) {
            $runningTimer = TimeLog::find()->where(['log_id' => $logId, 'user_id' => $userId, 'end_time' => null])->one();
        }
        if (!$runningTimer) {
            $runningTimer = TimeLog::find()->where(['user_id' => $userId, 'end_time' => null])->one();
        }
        if (!$runningTimer) {
            return ['success' => false, 'message' => 'No active timer found.'];
        }
        try {
            $runningTimer->end_time = date('Y-m-d H:i:s');
            $runningTimer->calculateDuration();
            if ($runningTimer->save()) {
                $user = User::findOne($userId);
                if ($user) {
                    $user->current_task_status = 'idle';
                    @$user->save(false, ['current_task_status']);
                }
                return ['success' => true, 'log_id' => $runningTimer->log_id, 'message' => 'Timer stopped successfully.'];
            }
            return ['success' => false, 'message' => 'Failed to stop timer. Please try again.'];
        } catch (\Exception $e) {
            Yii::error('Exception during stopTimer: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An internal error occurred.'];
        }
    }

    public function actionCheckRunningTimer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        $userId = Yii::$app->user->id;
        $runningTimer = TimeLog::find()->where(['user_id' => $userId, 'end_time' => null])->with(['subtask'])->one();
        if ($runningTimer) {
            return [
                'success' => true,
                'has_running_timer' => true,
                'timer' => [
                    'log_id' => $runningTimer->log_id,
                    'subtask_id' => $runningTimer->subtask_id,
                    'subtask_title' => $runningTimer->subtask ? $runningTimer->subtask->subtask_title : 'Unknown',
                    'start_time' => $runningTimer->start_time,
                    'start_timestamp' => strtotime($runningTimer->start_time) * 1000,
                    'description' => $runningTimer->description
                ]
            ];
        }
        return ['success' => true, 'has_running_timer' => false, 'message' => 'No running timer found'];
    }

    private function checkSubtaskAccess($subtask)
    {
        if (Yii::$app->user->identity->role === 'admin') {
            return true;
        }
        if (Yii::$app->user->identity->role === 'member') {
            $card = $subtask->card;
            if (!$card || !$card->board || !$card->board->project) {
                return false;
            }
            $project = $card->board->project;
            if ((int)$project->team_lead_id === (int)Yii::$app->user->id) {
                return true;
            }
            $isAssigned = CardAssignment::find()->where(['card_id' => $card->card_id, 'user_id' => Yii::$app->user->id])->exists();
            $isCreator = ((int)$subtask->created_by === (int)Yii::$app->user->id);
            return $isAssigned || $isCreator;
        }
        return false;
    }
}