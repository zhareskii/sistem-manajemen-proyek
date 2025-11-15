<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "time_logs".
 */
class TimeLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'time_logs';
    }

    public function rules()
    {
        return [
            [['user_id', 'start_time'], 'required'],
            [['card_id', 'subtask_id', 'user_id', 'duration_minutes'], 'integer'],
            [['start_time', 'end_time', 'created_at'], 'safe'],
            [['description'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'log_id' => 'Log ID',
            'card_id' => 'Card ID',
            'subtask_id' => 'Subtask ID',
            'user_id' => 'User ID',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'duration_minutes' => 'Duration (minutes)',
            'description' => 'Description',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }

    public function getCard()
    {
        return $this->hasOne(Card::class, ['card_id' => 'card_id']);
    }

    public function getSubtask()
    {
        return $this->hasOne(Subtask::class, ['subtask_id' => 'subtask_id']);
    }

    /**
     * Calculate duration in minutes between start_time and end_time
     */
    public function calculateDuration()
    {
        if ($this->start_time && $this->end_time) {
            $start = strtotime($this->start_time);
            $end = strtotime($this->end_time);
            $this->duration_minutes = round(($end - $start) / 60);
        }
    }

    /**
     * Before save, ensure created_at is set
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
    }

    /**
     * After save, update user's current_task_status
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Update user status to 'working' when timer starts
        if ($insert && !$this->end_time) {
            $user = User::findOne($this->user_id);
            if ($user) {
                $user->current_task_status = 'working';
                $user->save(false);
            }
        }
        
        // Update user status to 'idle' when timer stops
        if (!$insert && $this->end_time && isset($changedAttributes['end_time'])) {
            // Check if user has any other running timers
            $hasRunningTimer = TimeLog::find()
                ->where(['user_id' => $this->user_id, 'end_time' => null])
                ->exists();
            
            if (!$hasRunningTimer) {
                $user = User::findOne($this->user_id);
                if ($user) {
                    $user->current_task_status = 'idle';
                    $user->save(false);
                }
            }
        }
    }
}