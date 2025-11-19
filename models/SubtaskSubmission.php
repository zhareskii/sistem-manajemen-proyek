<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model untuk tabel "subtask_submissions".
 */
class SubtaskSubmission extends ActiveRecord
{
    public static function tableName()
    {
        return 'subtask_submissions';
    }

    public function rules()
    {
        return [
            [['subtask_id', 'submitted_by', 'reviewer_id'], 'integer'],
            [['subtask_id', 'submitted_by', 'reviewer_id', 'status'], 'required'],
            [['submission_notes', 'review_notes'], 'string'],
            [['created_at', 'reviewed_at'], 'safe'],
            [['status'], 'in', 'range' => ['pending', 'accepted', 'rejected']],
            [['status'], 'default', 'value' => 'pending'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'submission_id' => 'Submission ID',
            'subtask_id' => 'Subtask',
            'submitted_by' => 'Diajukan Oleh',
            'reviewer_id' => 'Reviewer',
            'status' => 'Status',
            'submission_notes' => 'Catatan Pengajuan',
            'review_notes' => 'Catatan Reviewer',
            'created_at' => 'Dibuat',
            'reviewed_at' => 'Direview',
        ];
    }

    public function getSubtask()
    {
        return $this->hasOne(Subtask::class, ['subtask_id' => 'subtask_id']);
    }

    public function getSubmitter()
    {
        return $this->hasOne(User::class, ['user_id' => 'submitted_by']);
    }

    public function getReviewer()
    {
        return $this->hasOne(User::class, ['user_id' => 'reviewer_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->status === 'accepted') {
            $subtask = $this->subtask;
            if ($subtask && $subtask->status !== 'done') {
                $subtask->status = 'done';
                $subtask->save(false, ['status']);
            }
        }
    }
}