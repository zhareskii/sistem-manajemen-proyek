<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cards".
 */
class Card extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cards';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['board_id', 'card_title', 'created_by', 'assigned_role'], 'required'],
            [['board_id', 'position', 'created_by'], 'integer'],
            [['description'], 'string'],
            [['card_title'], 'string', 'max' => 100],
            [['assigned_role'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 20],
            [['priority'], 'string', 'max' => 10],
            [['estimated_hours', 'actual_hours'], 'number'],
            [['created_at', 'due_date'], 'safe'],
            [['status'], 'default', 'value' => 'todo'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'card_id' => 'Card ID',
            'board_id' => 'Board ID',
            'card_title' => 'Card Title',
            'description' => 'Description',
            'position' => 'Position',
            'created_by' => 'Created By',
            'assigned_role' => 'Assigned Role',
            'created_at' => 'Created At',
            'due_date' => 'Due Date',
            'status' => 'Status',
            'priority' => 'Priority',
            'estimated_hours' => 'Estimated Hours',
            'actual_hours' => 'Actual Hours',
        ];
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
    public function getBoard()
    {
        return $this->hasOne(Board::class, ['board_id' => 'board_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignments()
    {
        return $this->hasMany(CardAssignment::class, ['card_id' => 'card_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignedUsers()
    {
        return $this->hasMany(User::class, ['user_id' => 'user_id'])
            ->viaTable('card_assignments', ['card_id' => 'card_id']);
    }

    public function getSubtasks()
    {
        return $this->hasMany(Subtask::class, ['card_id' => 'card_id'])
            ->orderBy(['subtasks.position' => SORT_ASC]);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::class, ['card_id' => 'card_id'])
            ->orderBy(['comments.created_at' => SORT_ASC]);
    }

    public function getTimeLogs()
    {
        return $this->hasMany(TimeLog::class, ['card_id' => 'card_id'])
            ->orderBy(['time_logs.created_at' => SORT_DESC]);
    }

    /**
     * Calculate card progress based on subtasks
     */
    public function calculateProgress()
    {
        $subtasks = $this->subtasks;
        if (empty($subtasks)) {
            return 0;
        }

        $totalSubtasks = count($subtasks);
        $completedSubtasks = 0;

        foreach ($subtasks as $subtask) {
            if ($subtask->status === 'done') {
                $completedSubtasks++;
            }
        }

        return $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100, 2) : 0;
    }

    /**
     * Update card status based on subtask progress
     */
    public function updateStatusFromSubtasks()
    {
        $subtasks = $this->subtasks;
        if (empty($subtasks)) {
            return;
        }

        $totalSubtasks = count($subtasks);
        $completedSubtasks = 0;
        $inProgressSubtasks = 0;

        foreach ($subtasks as $subtask) {
            if ($subtask->status === 'done') {
                $completedSubtasks++;
            } elseif ($subtask->status === 'in_progress') {
                $inProgressSubtasks++;
            }
        }

        // Auto-progress card status based on subtasks
        if ($totalSubtasks > 0 && $completedSubtasks === $totalSubtasks) {
            $this->status = 'done';
        } elseif ($inProgressSubtasks > 0 || $completedSubtasks > 0) {
            $this->status = 'in_progress';
        } else {
            $this->status = 'todo';
        }

        $this->save(false, ['status']);
    }

    /**
     * Update actual hours based on sum of subtask actual_hours
     */
    public function updateActualHours()
    {
        $totalActual = Subtask::find()
            ->where(['card_id' => $this->card_id])
            ->sum('actual_hours');
        $this->actual_hours = $totalActual ? (float)$totalActual : 0.0;
        $this->save(false, ['actual_hours']);
    }

    /**
     * Get all comments for this card and its subtasks
     */
    public function getCardComments()
    {
        $comments = $this->comments;
        
        // Get comments from subtasks
        foreach ($this->subtasks as $subtask) {
            $subtaskComments = Comment::find()
                ->where(['subtask_id' => $subtask->subtask_id])
                ->with(['user'])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
            $comments = array_merge($comments, $subtaskComments);
        }
        
        // Sort all comments by creation date
        usort($comments, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        
        return $comments;
    }

    /**
     * Get all help requests for this card's subtasks
     */
    public function getCardHelpRequests()
    {
        $helpRequests = [];
        foreach ($this->subtasks as $subtask) {
            $requests = HelpRequest::find()
                ->where(['subtask_id' => $subtask->subtask_id])
                ->with(['creator', 'resolver'])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
            $helpRequests = array_merge($helpRequests, $requests);
        }
        
        // Sort by creation date
        usort($helpRequests, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        
        return $helpRequests;
    }

    /**
     * Update project progress when card is created or updated
     */
    public function updateProjectProgress()
    {
        if ($this->board && $this->board->project) {
            $project = $this->board->project;
            $project->updateProgress();
        }
    }

    // Override afterSave untuk update progress project
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        if ($insert) {
            // Update project progress when new card is created
            $this->updateProjectProgress();
        }
    }
}