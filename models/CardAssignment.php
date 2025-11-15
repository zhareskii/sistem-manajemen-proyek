<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "card_assignments".
 */
class CardAssignment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'card_assignments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['card_id', 'user_id'], 'required'],
            [['card_id', 'user_id'], 'integer'],
            [['assigned_at', 'started_at', 'completed_at'], 'safe'],
            [['assignment_status'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'assignment_id' => 'Assignment ID',
            'card_id' => 'Card ID',
            'user_id' => 'User ID',
            'assigned_at' => 'Assigned At',
            'assignment_status' => 'Assignment Status',
            'started_at' => 'Started At',
            'completed_at' => 'Completed At',
        ];
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
    public function getUser()
    {
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }
}
