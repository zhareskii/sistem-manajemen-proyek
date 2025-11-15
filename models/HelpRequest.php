<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "help_requests".
 */
class HelpRequest extends ActiveRecord
{
    public static function tableName()
    {
        return 'help_requests';
    }

    public function rules()
    {
        return [
            [['subtask_id', 'user_id', 'issue_description'], 'required'],
            [['subtask_id', 'user_id', 'resolved_by'], 'integer'],
            [['issue_description', 'resolution_notes'], 'string'],
            [['created_at', 'resolved_at'], 'safe'],
            [['status'], 'in', 'range' => ['pending', 'in_progress', 'fixed', 'completed']],
            [['status'], 'default', 'value' => 'pending'], // Pastikan default adalah pending
        ];
    }

    public function attributeLabels()
    {
        return [
            'request_id' => 'Request ID',
            'subtask_id' => 'Subtask ID',
            'user_id' => 'Created By',
            'issue_description' => 'Issue',
            'status' => 'Status',
            'resolved_by' => 'Resolved By',
            'resolution_notes' => 'Resolution Notes',
            'created_at' => 'Created At',
            'resolved_at' => 'Resolved At',
        ];
    }

    public function getSubtask()
    {
        return $this->hasOne(Subtask::class, ['subtask_id' => 'subtask_id']);
    }

    public function getCreator()
    {
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }

    public function getResolver()
    {
        return $this->hasOne(User::class, ['user_id' => 'resolved_by']);
    }

    /**
     * Update progress when help request status changes
     */
    public function updateCardAndProjectProgress()
    {
        try {
            $subtask = $this->subtask;
            if ($subtask && method_exists($subtask, 'updateProgressForCardAndProject')) {
                $subtask->updateProgressForCardAndProject();
            }
        } catch (\Exception $e) {
            Yii::error("Error updating progress in HelpRequest: " . $e->getMessage());
        }
    }

    // Override beforeSave untuk memastikan status baru selalu pending
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                // Untuk help request baru, pastikan status adalah pending
                $this->status = 'pending';
                $this->resolved_by = null; // Pastikan resolved_by null untuk request baru
                $this->resolved_at = null; // Pastikan resolved_at null untuk request baru
            }
            return true;
        }
        return false;
    }

    // Override afterSave untuk update progress
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Update progress ketika status help request berubah (bukan untuk yang baru dibuat)
        if (isset($changedAttributes['status']) && !$insert) {
            $this->updateCardAndProjectProgress();
        }
    }

    /**
     * Check if current user can edit this help request
     */
    public function canEdit()
    {
        if (\Yii::$app->user->isGuest) {
            return false;
        }

        $user = \Yii::$app->user->identity;

        // Admin can edit
        if ($user->role === 'admin') {
            return true;
        }

        // Team lead of the project can edit
        $project = ($this->subtask && $this->subtask->card && $this->subtask->card->board)
            ? $this->subtask->card->board->project
            : null;
        if ($project && (int)$project->team_lead_id === (int)$user->user_id) {
            return true;
        }

        // Creator of the help request can edit
        if ((int)$this->user_id === (int)$user->user_id) {
            return true;
        }

        // Assigned user on the card can edit
        if ($this->subtask && $this->subtask->card) {
            $isAssigned = \app\models\CardAssignment::find()
                ->where(['card_id' => $this->subtask->card->card_id, 'user_id' => $user->user_id])
                ->exists();
            if ($isAssigned) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if current user can delete this help request
     */
    public function canDelete()
    {
        // For now, align delete permission with edit permission
        return $this->canEdit();
    }
}