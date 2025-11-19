<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\User;
use app\models\TimeTracking;

class TrackingController extends Controller
{
    public function actionStartTracking()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        $userId = Yii::$app->user->id;
        $tracking = TimeTracking::startTracking($userId);
        if ($tracking) {
            return [
                'success' => true,
                'tracking_id' => $tracking->tracking_id,
                'start_time' => $tracking->start_time,
            ];
        }
        return ['success' => false, 'message' => 'Failed to start tracking'];
    }

    public function actionStopTracking()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        $trackingId = Yii::$app->request->post('tracking_id');
        $durationMinutes = Yii::$app->request->post('duration_minutes');
        if (!$trackingId) {
            return ['success' => false, 'message' => 'Tracking ID is required'];
        }
        $tracking = TimeTracking::findOne($trackingId);
        if (!$tracking) {
            return ['success' => false, 'message' => 'Tracking session not found'];
        }
        if ($tracking->user_id != Yii::$app->user->id) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        if ($tracking->stopTracking($durationMinutes)) {
            return [
                'success' => true,
                'message' => 'Tracking stopped successfully',
                'duration_minutes' => $tracking->duration_minutes,
            ];
        }
        return ['success' => false, 'message' => 'Failed to stop tracking'];
    }

    public function actionGetTrackingStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        $userId = Yii::$app->user->id;
        $tracking = TimeTracking::getActiveSession($userId); 
        // FUNCTION Mengecek status tracking aktif dan menghitung berapa lama user sudah bekerja.
        if (!$tracking) {
            return [
                'success' => true,
                'is_tracking' => false,
                'message' => 'No active session',
            ];
        }
        return [
            'success' => true,
            'is_tracking' => true,
            'tracking_id' => $tracking->tracking_id,
            'start_time' => $tracking->start_time,
            'start_timestamp' => strtotime($tracking->start_time) * 1000,
            'elapsed_seconds' => $tracking->getElapsedSeconds(),
        ];
    }

    public function actionUpdateTaskStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        $status = Yii::$app->request->post('status');
        $userId = Yii::$app->user->id;
        $user = User::findOne($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        $user->current_task_status = $status;
        if ($user->save(false, ['current_task_status'])) {
            return ['success' => true, 'message' => 'Task status updated'];
        }
        return ['success' => false, 'message' => 'Failed to update task status'];
    }

    public function actionStartActivity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        $userId = Yii::$app->user->id;
        $user = User::findOne($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        try {
            $existingActiveTracking = TimeTracking::find()
                ->where(['user_id' => $userId, 'is_active' => 1])
                ->one();
            if ($existingActiveTracking) {
                $existingActiveTracking->end_time = date('Y-m-d H:i:s');
                $existingActiveTracking->is_active = 0;
                $start = new \DateTime($existingActiveTracking->start_time);
                $end = new \DateTime($existingActiveTracking->end_time);
                $interval = $start->diff($end);
                $existingActiveTracking->duration_minutes = ($interval->h * 60) + $interval->i;
                $existingActiveTracking->save();
            }
            $tracking = new TimeTracking();
            $tracking->user_id = $userId;
            $tracking->start_time = date('Y-m-d H:i:s');
            $tracking->is_active = 1;
            $tracking->last_ping = $tracking->start_time;
            if ($tracking->save()) {
                $user->current_task_status = 'active';
                $user->save(false, ['current_task_status']);
                return [
                    'success' => true,
                    'tracking_id' => $tracking->tracking_id,
                    'start_time' => $tracking->start_time,
                ];
            }
            Yii::error('Failed to start activity: ' . json_encode($tracking->getErrors()));
            return ['success' => false, 'message' => 'Failed to start activity'];
        } catch (\Exception $e) {
            Yii::error('Exception during startActivity: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error starting activity: ' . $e->getMessage()];
        }
    }

    public function actionPingActivity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        $userId = Yii::$app->user->id;
        $trackingId = Yii::$app->request->post('tracking_id') ?? Yii::$app->request->get('tracking_id');
        if (!$trackingId) {
            return ['success' => false, 'message' => 'Tracking ID is required'];
        }
        try {
            $tracking = TimeTracking::findOne($trackingId);
            if (!$tracking || $tracking->user_id !== $userId || !$tracking->is_active) {
                return ['success' => false, 'message' => 'Active tracking session not found for this user.'];
            }
            $tracking->last_ping = date('Y-m-d H:i:s');
            if ($tracking->save()) {
                $user = User::findOne($userId);
                if ($user) {
                    $user->current_task_status = 'active';
                    $user->save(false, ['current_task_status']);
                }
                return [
                    'success' => true,
                    'message' => 'Ping successful',
                    'tracking_id' => $tracking->tracking_id,
                ];
            }
            Yii::error('Failed to update last_ping for tracking ID ' . $trackingId . ': ' . json_encode($tracking->getErrors()));
            return ['success' => false, 'message' => 'Failed to ping activity'];
        } catch (\Exception $e) {
            Yii::error('Exception during pingActivity: ' . $e->getMessage());
            Yii::error($e->getTraceAsString());
            return ['success' => false, 'message' => 'Error pinging activity: ' . $e->getMessage()];
        }
    }
}