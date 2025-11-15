<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "time_tracking".
 * Tracks productivity sessions for users with start/stop functionality.
 */
class TimeTracking extends ActiveRecord
{
    public static function tableName()
    {
        return 'time_tracking';
    }

    public function rules()
    {
        return [
            [['user_id', 'start_time'], 'required'],
            [['user_id', 'duration_minutes'], 'integer'],
            [['start_time', 'end_time', 'created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tracking_id' => 'Tracking ID',
            'user_id' => 'User ID',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'duration_minutes' => 'Duration (minutes)',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }

    /**
     * Start a new tracking session
     */
    public static function startTracking($userId)
    {
        // End any active sessions
        $activeSession = self::getActiveSession($userId);
        
        if ($activeSession) {
            $activeSession->stopTracking();
        }

        // Create new tracking session
        $tracking = new self();
        $tracking->user_id = $userId;
        $tracking->start_time = date('Y-m-d H:i:s');
        
        if ($tracking->save()) {
            // Update user status to working
            $user = User::findOne($userId);
            if ($user && property_exists($user, 'current_task_status')) {
                $user->current_task_status = 'working';
                $user->save(false);
            }
            return $tracking;
        }
        return false;
    }

    /**
     * Stop the current tracking session
     * @param int|null $durationMinutes Optional duration override in minutes
     */
    public function stopTracking($durationMinutes = null)
    {
        if (!$this->end_time) {
            $this->end_time = date('Y-m-d H:i:s');
        }

        if ($durationMinutes !== null) {
            $this->duration_minutes = (int)$durationMinutes;
        } else {
            // Calculate duration from start/end timestamps
            if ($this->start_time && $this->end_time) {
                $start = strtotime($this->start_time);
                $end = strtotime($this->end_time);
                $this->duration_minutes = round(($end - $start) / 60);
            }
        }

        if ($this->save()) {
            // Update user status to idle
            $user = User::findOne($this->user_id);
            if ($user && property_exists($user, 'current_task_status')) {
                $user->current_task_status = 'idle';
                $user->save(false);
            }
            return true;
        }

        return false;
    }

    /**
     * Get active tracking session for user
     */
    public static function getActiveSession($userId)
    {
        return self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['end_time' => null])
            ->one();
    }

    /**
     * Get today's tracking sessions for user
     */
    public static function getTodaysSessions($userId)
    {
        return self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['>=', 'DATE(start_time)', date('Y-m-d')])
            ->orderBy(['start_time' => SORT_DESC])
            ->all();
    }

    /**
     * Get total minutes tracked today
     */
    public static function getTotalMinutesToday($userId)
    {
        $total = 0;
        $sessions = self::getTodaysSessions($userId);
        
        foreach ($sessions as $session) {
            if ($session->end_time === null) {
                // Add time from start to now for active sessions
                $start = strtotime($session->start_time);
                $now = time();
                $total += round(($now - $start) / 60);
            } else {
                $total += $session->duration_minutes ?? 0;
            }
        }
        
        return $total;
    }

    /**
     * Calculate elapsed time for active session
     */
    public function getElapsedSeconds()
    {
        if ($this->end_time === null && $this->start_time) {
            $start = strtotime($this->start_time);
            return time() - $start;
        } elseif ($this->start_time && $this->end_time) {
            $start = strtotime($this->start_time);
            $end = strtotime($this->end_time);
            return $end - $start;
        }
        return 0;
    }

    /**
     * Format seconds to HH:MM:SS
     */
    public static function formatTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}