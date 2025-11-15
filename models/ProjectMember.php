<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * ProjectMember ActiveRecord for table `project_members`.
 *
 * @property int $member_id
 * @property int $project_id
 * @property int $user_id
 * @property string|null $joined_at
 *
 * @property Project $project
 * @property User $user
 */
class ProjectMember extends ActiveRecord
{
    public static function tableName()
    {
        return 'project_members';
    }

    public static function primaryKey()
    {
        return ['member_id'];
    }

    public function rules()
    {
        return [
            [['project_id', 'user_id'], 'required'],
            [['project_id', 'user_id'], 'integer'],
            [['joined_at'], 'safe'],
            [['project_id', 'user_id'], 'unique', 'targetAttribute' => ['project_id', 'user_id'], 'message' => 'User sudah terdaftar di project ini.'],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'project_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'member_id' => 'ID',
            'project_id' => 'Project',
            'user_id' => 'User',
            'joined_at' => 'Joined At',
        ];
    }

    public function getProject()
    {
        return $this->hasOne(Project::class, ['project_id' => 'project_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }
}