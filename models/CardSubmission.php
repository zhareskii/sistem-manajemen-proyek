<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "card_submissions".
 *
 * @property int $submission_id
 * @property int $card_id
 * @property int $submitted_by
 * @property int $reviewer_id
 * @property string $status
 * @property string|null $submission_notes
 * @property string|null $review_notes
 * @property string $created_at
 * @property string|null $reviewed_at
 *
 * @property Card $card
 * @property User $submitter
 * @property User $reviewer
 */
class CardSubmission extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'card_submissions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['card_id', 'submitted_by', 'reviewer_id'], 'required'],
            [['card_id', 'submitted_by', 'reviewer_id'], 'integer'],
            [['submission_notes', 'review_notes'], 'string'],
            [['created_at', 'reviewed_at'], 'safe'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'default', 'value' => 'pending'],
            [['status'], 'in', 'range' => ['pending', 'accepted', 'rejected']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'submission_id' => 'Submission ID',
            'card_id' => 'Card',
            'submitted_by' => 'Submitted By',
            'reviewer_id' => 'Reviewer',
            'status' => 'Status',
            'submission_notes' => 'Submission Notes',
            'review_notes' => 'Review Notes',
            'created_at' => 'Created At',
            'reviewed_at' => 'Reviewed At',
        ];
    }

    /**
     * Gets query for [[Card]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(Card::class, ['card_id' => 'card_id']);
    }

    /**
     * Gets query for [[Submitter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubmitter()
    {
        return $this->hasOne(User::class, ['user_id' => 'submitted_by']);
    }

    /**
     * Gets query for [[Reviewer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReviewer()
    {
        return $this->hasOne(User::class, ['user_id' => 'reviewer_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
    }

    /**
     * Check if submission can be edited
     */
    public function canEdit()
    {
        return $this->status === 'pending' && Yii::$app->user->id === $this->submitted_by;
    }

    /**
     * Check if submission can be deleted
     */
    public function canDelete()
    {
        return $this->status === 'pending' && Yii::$app->user->id === $this->submitted_by;
    }

    /**
     * Approve the card submission
     */
    public function approve($reviewNotes = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->status = 'accepted';
            $this->review_notes = $reviewNotes;
            $this->reviewed_at = date('Y-m-d H:i:s');
            
            if (!$this->save()) {
                throw new \Exception('Failed to save submission: ' . implode(', ', $this->getFirstErrors()));
            }
            
            // Update card status to done
            if ($this->card) {
                $this->card->status = 'done';
                if (!$this->card->save()) {
                    throw new \Exception('Failed to update card status: ' . implode(', ', $this->card->getFirstErrors()));
                }
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error approving card submission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject the card submission
     */
    public function reject($reviewNotes = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->status = 'rejected';
            $this->review_notes = $reviewNotes;
            $this->reviewed_at = date('Y-m-d H:i:s');
            
            if (!$this->save()) {
                throw new \Exception('Failed to save submission: ' . implode(', ', $this->getFirstErrors()));
            }
            
            // Update card status back to done (bukan completed) agar bisa diajukan ulang
            if ($this->card) {
                $this->card->status = 'done';
                if (!$this->card->save()) {
                    throw new \Exception('Failed to update card status: ' . implode(', ', $this->card->getFirstErrors()));
                }
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error rejecting card submission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get pending submissions for admin
     */
    public static function getPendingForAdmin($adminId)
    {
        return self::find()
            ->where(['reviewer_id' => $adminId, 'status' => 'pending'])
            ->with(['card', 'submitter', 'card.board.project'])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
    }

    /**
     * Get all submissions for admin
     */
    public static function getAllForAdmin()
    {
        return self::find()
            ->with(['card', 'submitter', 'reviewer', 'card.board.project'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }
}