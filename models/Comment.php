<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "comments".
 */
class Comment extends ActiveRecord
{
    public static function tableName()
    {
        return 'comments';
    }

    public function rules()
    {
        return [
            [['user_id', 'comment_text', 'comment_type'], 'required'],
            [['card_id', 'subtask_id', 'user_id'], 'integer'],
            [['comment_text'], 'string'],
            [['created_at'], 'safe'],
            [['comment_type'], 'in', 'range' => ['card', 'subtask']],
            ['card_id', 'required', 'when' => function($model) { return $model->comment_type === 'card'; }, 'whenClient' => "function(){return $('#comment_type').val()==='card';}"],
            ['subtask_id', 'required', 'when' => function($model) { return $model->comment_type === 'subtask'; }, 'whenClient' => "function(){return $('#comment_type').val()==='subtask';}"],
        ];
    }

    public function attributeLabels()
    {
        return [
            'comment_id' => 'Comment ID',
            'card_id' => 'Card ID',
            'subtask_id' => 'Subtask ID',
            'user_id' => 'User ID',
            'comment_text' => 'Comment',
            'comment_type' => 'Type',
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
     * Check if user can edit this comment
     */
    public function canEdit()
    {
        if (Yii::$app->user->isGuest) return false;
        
        $user = Yii::$app->user->identity;
        if ($user->role === 'admin') return true;
        
        // Creator can edit within 30 minutes of creation
        if ($this->user_id == $user->user_id) {
            $createdTime = strtotime($this->created_at);
            $currentTime = time();
            return ($currentTime - $createdTime) <= 1800; // 30 minutes
        }
        
        return false;
    }

    /**
     * Check if user can delete this comment
     */
    public function canDelete()
    {
        if (Yii::$app->user->isGuest) return false;
        
        $user = Yii::$app->user->identity;
        if ($user->role === 'admin') return true;
        
        // Only creator can delete and only within 30 minutes
        if ($this->user_id == $user->user_id) {
            $createdTime = strtotime($this->created_at);
            $currentTime = time();
            return ($currentTime - $createdTime) <= 1800; // 30 minutes
        }
        
        return false;
    }
}