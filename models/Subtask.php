<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "subtasks".
 */
class Subtask extends ActiveRecord
{
    public static function tableName()
    {
        return 'subtasks';
    }

    public function rules()
    {
        return [
            [['card_id', 'subtask_title'], 'required'],
            [['card_id', 'position', 'created_by'], 'integer'],
            [['description'], 'string'],
            [['estimated_hours', 'actual_hours'], 'number'],
            [['created_at'], 'safe'],
            [['subtask_title'], 'string', 'max' => 100],
            [['status'], 'in', 'range' => ['todo', 'in_progress', 'done']],
            [['status'], 'default', 'value' => 'todo'],
            [['estimated_hours', 'actual_hours'], 'default', 'value' => 0],
            [['position'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'subtask_id' => 'Subtask ID',
            'card_id' => 'Card ID',
            'subtask_title' => 'Subtask Title',
            'description' => 'Description',
            'status' => 'Status',
            'estimated_hours' => 'Estimated Hours',
            'actual_hours' => 'Actual Hours',
            'position' => 'Position',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if ($this->isNewRecord && empty($this->created_by) && !Yii::$app->user->isGuest) {
                $this->created_by = Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }

    /**
     * Update progress for card and project when subtask changes
     */
    public function updateProgressForCardAndProject()
    {
        // Update card progress
        $card = $this->card;
        if ($card) {
            // Recalculate card progress
            $cardProgress = $card->calculateProgress();
            
            // Update project progress
            if ($card->board && $card->board->project) {
                $card->board->project->updateProgress();
            }
        }
    }

    // Override afterSave untuk update progress
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        $this->updateProgressForCardAndProject();
        
        // Auto-update card status based on subtasks
        if ($this->card) {
            $this->card->updateStatusFromSubtasks();
        }
    }

    // Override afterDelete untuk update progress
    public function afterDelete()
    {
        parent::afterDelete();
        
        $this->updateProgressForCardAndProject();
        
        // Auto-update card status based on remaining subtasks
        if ($this->card) {
            $this->card->updateStatusFromSubtasks();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(Card::class, ['card_id' => 'card_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::class, ['user_id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['subtask_id' => 'subtask_id'])
            ->orderBy(['comments.created_at' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimeLogs()
    {
        return $this->hasMany(TimeLog::class, ['subtask_id' => 'subtask_id'])
            ->orderBy(['time_logs.created_at' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelpRequests()
    {
        return $this->hasMany(HelpRequest::class, ['subtask_id' => 'subtask_id'])
            ->orderBy(['help_requests.created_at' => SORT_DESC]);
    }

    public function __get($name)
    {
        if ($name === 'card_title') {
            return $this->card ? $this->card->card_title : null;
        }
        if ($name === 'board') {
            return $this->card ? $this->card->board : null;
        }
        if ($name === 'due_date') {
            return $this->card ? $this->card->due_date : null;
        }
        if ($name === 'assigned_role') {
            return $this->card ? $this->card->assigned_role : null;
        }
        return parent::__get($name);
    }
}