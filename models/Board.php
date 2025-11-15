<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "boards".
 */
class Board extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'boards';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'board_name'], 'required'],
            [['project_id'], 'integer'],
            [['created_at'], 'safe'],
            [['board_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'board_id' => 'Board ID',
            'project_id' => 'Project ID',
            'board_name' => 'Board Name',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['project_id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::class, ['board_id' => 'board_id']);
    }

    /**
     * Get cards grouped by status for kanban view
     */
    public function getCardsByStatus()
    {
        $cards = $this->getCards()->with(['creator', 'assignedUsers'])->all();
        
        $grouped = [
            'todo' => [],
            'in_progress' => [],
            'review' => [],
            'done' => []
        ];
        
        foreach ($cards as $card) {
            $status = $card->status ?? 'todo';
            if (isset($grouped[$status])) {
                $grouped[$status][] = $card;
            }
        }
        
        return $grouped;
    }

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
}