<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "projects".
 */
class Project extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'projects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_name', 'team_lead_id', 'difficulty_level', 'deadline'], 'required'],
            [['description'], 'string'],
            [['created_by', 'team_lead_id'], 'integer'],
            [['progress_percentage'], 'number'],
            [['created_at', 'deadline'], 'safe'],
            [['project_name'], 'string', 'max' => 100],
            [['difficulty_level'], 'in', 'range' => ['easy', 'medium', 'hard']],
            [['status'], 'in', 'range' => ['planning', 'active', 'completed', 'cancelled']],
            // DEFAULT VALUE UNTUK STATUS
            [['status'], 'default', 'value' => 'planning'],
            [['progress_percentage'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Project ID',
            'project_name' => 'Project Name',
            'description' => 'Description',
            'created_by' => 'Created By',
            'team_lead_id' => 'Team Lead',
            'difficulty_level' => 'Difficulty Level',
            'status' => 'Status',
            'progress_percentage' => 'Progress',
            'created_at' => 'Created At',
            'deadline' => 'Deadline',
        ];
    }

    /**
     * Calculate project progress based on team lead's cards
     */
    public function calculateProgress()
    {
        // Jika status completed, progress 100%
        if ($this->status === 'completed') {
            return 100;
        }
        
        // Jika status cancelled, progress 0%
        if ($this->status === 'cancelled') {
            return 0;
        }

        $boards = Board::find()->where(['project_id' => $this->project_id])->all();
        if (empty($boards)) {
            return 0;
        }

        $totalProgress = 0;
        $cardCount = 0;

        foreach ($boards as $board) {
            // Ambil semua kartu yang dibuat oleh team lead
            $cards = Card::find()->where(['board_id' => $board->board_id, 'created_by' => $this->team_lead_id])->all();

            foreach ($cards as $card) {
                $cardProgress = $this->calculateCardProgress($card);
                $totalProgress += $cardProgress;
                $cardCount++;
            }
        }

        return $cardCount > 0 ? round(($totalProgress / $cardCount), 2) : 0;
    }

    /**
     * Calculate progress for a single card based on subtasks
     */
    private function calculateCardProgress($card)
    {
        $subtasks = Subtask::find()->where(['card_id' => $card->card_id])->all();
        
        if (empty($subtasks)) {
            return 0;
        }

        $doneCount = 0;
        $totalSubtasks = count($subtasks);

        foreach ($subtasks as $subtask) {
            if ($subtask->status === 'done') {
                $doneCount++;
            }
        }

        return $totalSubtasks > 0 ? round(($doneCount / $totalSubtasks) * 100, 2) : 0;
    }

    /**
     * Update project progress and save to database
     */
    public function updateProgress()
    {
        $this->progress_percentage = $this->calculateProgress();
        return $this->save(false, ['progress_percentage']);
    }

    /**
     * Override beforeSave untuk auto-update progress
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Update progress sebelum save
            if ($this->status === 'completed') {
                $this->progress_percentage = 100;
            } elseif ($this->status === 'cancelled') {
                $this->progress_percentage = 0;
            } else {
                $this->progress_percentage = $this->calculateProgress();
            }
            return true;
        }
        return false;
    }

    /**
     * Get cards created by team lead for this project
     */
    public function getTeamLeadCards()
    {
        $teamLeadCards = [];
        $boards = Board::find()->where(['project_id' => $this->project_id])->all();
        
        foreach ($boards as $board) {
            $cards = Card::find()
                ->where(['board_id' => $board->board_id, 'created_by' => $this->team_lead_id])
                ->all();
            $teamLeadCards = array_merge($teamLeadCards, $cards);
        }
        
        return $teamLeadCards;
    }

    /**
     * Get project comments
     */
    public function getProjectComments()
    {
        $comments = [];
        $boards = Board::find()->where(['project_id' => $this->project_id])->all();
        
        foreach ($boards as $board) {
            $cards = Card::find()->where(['board_id' => $board->board_id])->all();
            
            foreach ($cards as $card) {
                $cardComments = Comment::find()
                    ->where(['card_id' => $card->card_id])
                    ->with(['user'])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->all();
                $comments = array_merge($comments, $cardComments);
                
                $cardSubtasks = Subtask::find()->where(['card_id' => $card->card_id])->all();
                foreach ($cardSubtasks as $subtask) {
                    $subtaskComments = Comment::find()
                        ->where(['subtask_id' => $subtask->subtask_id])
                        ->with(['user'])
                        ->orderBy(['created_at' => SORT_DESC])
                        ->all();
                    $comments = array_merge($comments, $subtaskComments);
                }
            }
        }
        
        usort($comments, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        
        return $comments;
    }

    /**
     * Get project help requests
     */
    public function getProjectHelpRequests()
    {
        $helpRequests = [];
        $boards = Board::find()->where(['project_id' => $this->project_id])->all();
        
        foreach ($boards as $board) {
            $cards = Card::find()->where(['board_id' => $board->board_id])->all();
            
            foreach ($cards as $card) {
                $cardSubtasks = Subtask::find()->where(['card_id' => $card->card_id])->all();
                foreach ($cardSubtasks as $subtask) {
                    $subtaskHelpRequests = HelpRequest::find()
                        ->where(['subtask_id' => $subtask->subtask_id])
                        ->with(['creator', 'resolver'])
                        ->orderBy(['created_at' => SORT_DESC])
                        ->all();
                    $helpRequests = array_merge($helpRequests, $subtaskHelpRequests);
                }
            }
        }
        
        usort($helpRequests, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        
        return $helpRequests;
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity($limit = 10)
    {
        $activity = [];
        
        $comments = $this->getProjectComments();
        foreach ($comments as $comment) {
            $activity[] = [
                'type' => 'comment',
                'object' => $comment,
                'created_at' => $comment->created_at
            ];
        }
        
        $helpRequests = $this->getProjectHelpRequests();
        foreach ($helpRequests as $helpRequest) {
            $activity[] = [
                'type' => 'help_request',
                'object' => $helpRequest,
                'created_at' => $helpRequest->created_at
            ];
        }
        
        usort($activity, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activity, 0, $limit);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeamLead()
    {
        return $this->hasOne(User::class, ['user_id' => 'team_lead_id']);
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
    public function getBoards()
    {
        return $this->hasMany(Board::class, ['project_id' => 'project_id']);
    }

    /**
     * Get all cards in this project
     */
    public function getCards()
    {
        $cards = [];
        $boards = $this->getBoards()->all();
        
        foreach ($boards as $board) {
            $boardCards = Card::find()->where(['board_id' => $board->board_id])->all();
            $cards = array_merge($cards, $boardCards);
        }
        
        return $cards;
    }

    /**
     * Check if project is overdue
     */
    public function isOverdue()
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return false;
        }
        
        return strtotime($this->deadline) < time();
    }

    /**
     * Get days remaining until deadline
     */
    public function getDaysRemaining()
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return 0;
        }
        
        $currentTime = time();
        $deadlineTime = strtotime($this->deadline);
        $diff = $deadlineTime - $currentTime;
        
        return $diff > 0 ? ceil($diff / (60 * 60 * 24)) : 0;
    }

    /**
     * Get project duration in days
     */
    public function getProjectDuration()
    {
        $startTime = strtotime($this->created_at);
        $endTime = $this->status === 'completed' ? strtotime($this->updated_at) : time();
        
        $diff = $endTime - $startTime;
        return ceil($diff / (60 * 60 * 24));
    }
}