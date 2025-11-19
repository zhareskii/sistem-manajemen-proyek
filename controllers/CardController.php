<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\Project;
use app\models\Board;
use app\models\Card;
use app\models\CardAssignment;
use app\models\Subtask;
use app\models\TimeLog;
use app\models\User;
use app\models\ProjectMember;

class CardController extends Controller
{
    public function actionMemberCards()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $projects = Project::find()
            ->where(['team_lead_id' => Yii::$app->user->id])
            ->all();

        $cards = [];
        foreach ($projects as $project) {
            $boards = Board::find()->where(['project_id' => $project->project_id])->all();
            foreach ($boards as $board) {
                $boardCards = Card::find()
                    ->where(['board_id' => $board->board_id])
                    ->with(['creator', 'assignedUsers'])
                    ->orderBy(['position' => SORT_ASC])
                    ->all();
                $cards = array_merge($cards, $boardCards);
            }
        }

        $cardModel = new Card();
        $users = User::find()->where(['role' => 'member'])->all();
        
        return $this->render('@app/views/site/member/cards', [
            'cards' => $cards,
            'cardModel' => $cardModel,
            'users' => $users,
        ]);
    }

    public function actionCreateCard()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $model = new Card();
        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'todo';
            $model->created_by = Yii::$app->user->id;
            $model->actual_hours = 0;

            $projectId = Yii::$app->request->post('Card')['project_id'];
            $project = Project::findOne($projectId);
            if (!$project) {
                Yii::$app->session->setFlash('error', 'Project not found.');
                return $this->redirect(['site/member-cards']);
            }
            if ((int)$project->team_lead_id !== (int)Yii::$app->user->id) {
                Yii::$app->session->setFlash('error', 'Only the Project Team Lead can create cards.');
                return $this->redirect(['site/member-cards']);
            }
            if ($project->status === 'completed') {
                Yii::$app->session->setFlash('error', 'Cannot create cards in a completed project.');
                return $this->redirect(['site/member-cards']);
            }

            $defaultBoard = Board::find()
                ->where(['project_id' => $projectId])
                ->orderBy(['board_id' => SORT_ASC])
                ->one();

            if (!$defaultBoard) {
                Yii::$app->session->setFlash('error', 'Project has no boards. Please create a board first.');
                return $this->redirect(['site/member-cards']);
            }

            $model->board_id = $defaultBoard->board_id;

            $assignedUserId = Yii::$app->request->post('assigned_user_id');
            if (!$assignedUserId) {
                Yii::$app->session->setFlash('error', 'You must select one user as developer/designer.');
                return $this->redirect(['site/member-cards']);
            }

            $hasActiveAssignment = CardAssignment::find()
                ->alias('ca')
                ->joinWith('card c', false)
                ->where(['ca.user_id' => (int)$assignedUserId])
                ->andWhere(['in', 'c.status', ['todo','in_progress','review']])
                ->exists();

            if ($hasActiveAssignment) {
                $userName = User::findOne($assignedUserId)->full_name;
                Yii::$app->session->setFlash('error', 'Failed to add card: user ' . $userName . ' already has an active task.');
                return $this->redirect(['site/member-cards']);
            }
            
            if ($model->save()) {
                // Trigger: Otomatis buat assignment
                $assign = new CardAssignment();
                $assign->card_id = $model->card_id;
                $assign->user_id = (int)$assignedUserId;
                @$assign->save();

                $project = Project::findOne($projectId);
                // Trigger: Update project status
                if ($project && $project->status === 'planning') {
                    $project->status = 'active';
                    @$project->save(false, ['status']);
                }

                Yii::$app->session->setFlash('success', 'Card added successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to add card: ' . implode(', ', $model->getFirstErrors()));
            }
            
            return $this->redirect(['site/member-cards']);
        }
        
        return $this->redirect(['site/member-cards']);
    }

    public function actionUpdateCard()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $id = Yii::$app->request->post('Card')['card_id'];
        $model = Card::findOne($id);
        
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Card not found.');
            return $this->redirect(['site/member-cards']);
        }

        $worked = Subtask::find()->where(['card_id' => $model->card_id])->exists()
            || TimeLog::find()->where(['card_id' => $model->card_id])->exists()
            || ($model->status !== 'todo');
        if ($worked) {
            Yii::$app->session->setFlash('error', 'Card has been worked on and cannot be updated.');
            return $this->redirect(['site/member-cards']);
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $assignedUserId = Yii::$app->request->post('assigned_user_id');
            
            if ($assignedUserId) {
                $hasActiveAssignment = CardAssignment::find()
                    ->alias('ca')
                    ->joinWith('card c', false)
                    ->where(['ca.user_id' => (int)$assignedUserId])
                    ->andWhere(['in', 'c.status', ['todo','in_progress','review']])
                    ->andWhere(['<>', 'ca.card_id', $model->card_id])
                    ->exists();

                if ($hasActiveAssignment) {
                    $userName = User::findOne($assignedUserId)->full_name;
                    Yii::$app->session->setFlash('error', 'Failed to update card: user ' . $userName . ' already has an active task.');
                    return $this->redirect(['site/member-cards']);
                }

                CardAssignment::deleteAll(['card_id' => $model->card_id]);
                $assign = new CardAssignment();
                $assign->card_id = $model->card_id;
                $assign->user_id = (int)$assignedUserId;
                @$assign->save();
            }

            Yii::$app->session->setFlash('success', 'Card updated successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update card.');
        }
        
        return $this->redirect(['site/member-cards']);
    }

    public function actionGetCardDetail($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $card = Card::find()
                ->with([
                    'creator', 
                    'assignedUsers', 
                    'board.project',
                    'subtasks.creator'
                ])
                ->where(['card_id' => $id])
                ->one();
                
            if (!$card) {
                return ['success' => false, 'message' => 'Card not found'];
            }
            
            $project = $card->board->project;
            if (Yii::$app->user->identity->role === 'member') {
                $isTeamLead = ($project && (int)$project->team_lead_id === (int)Yii::$app->user->id);
                $isCreator = ((int)$card->created_by === (int)Yii::$app->user->id);
                $isAssigned = CardAssignment::find()
                    ->where(['card_id' => $card->card_id, 'user_id' => Yii::$app->user->id])
                    ->exists();
                if (!$isTeamLead && !$isCreator && !$isAssigned) {
                    return ['success' => false, 'message' => 'Access denied'];
                }
            }
            
            $assignedUser = $card->assignedUsers ? $card->assignedUsers[0] : null;
            $assignedUserId = $assignedUser ? $assignedUser->user_id : null;
            $assignedUserName = $assignedUser ? $assignedUser->full_name : 'Not assigned';
            
            $projectName = $card->board && $card->board->project ? $card->board->project->project_name : 'Unknown';
            $projectId = $card->board && $card->board->project ? $card->board->project->project_id : null;
            
            $subtasks = Subtask::find()
                ->with(['creator'])
                ->where(['card_id' => $card->card_id])
                ->orderBy(['position' => SORT_ASC])
                ->all();
            
            $progress = $card->calculateProgress();
            
            $allComments = $card->getCardComments();
            $commentsData = [];
            foreach ($allComments as $comment) {
                $subtaskTitle = 'Card';
                if ($comment->subtask_id) {
                    $subtask = Subtask::findOne($comment->subtask_id);
                    $subtaskTitle = $subtask ? $subtask->subtask_title : 'Subtask';
                }
                
                $commentsData[] = [
                    'comment_id' => $comment->comment_id,
                    'user_name' => $comment->user ? $comment->user->full_name : 'Unknown',
                    'comment_text' => $comment->comment_text,
                    'subtask_title' => $subtaskTitle,
                    'created_at' => date('d M Y H:i', strtotime($comment->created_at))
                ];
            }
            
            $allHelpRequests = $card->getCardHelpRequests();
            $helpRequestsData = [];
            foreach ($allHelpRequests as $request) {
                $subtask = Subtask::findOne($request->subtask_id);
                $helpRequestsData[] = [
                    'request_id' => $request->request_id,
                    'creator_name' => $request->creator ? $request->creator->full_name : 'Unknown',
                    'subtask_title' => $subtask ? $subtask->subtask_title : 'Unknown',
                    'issue_description' => $request->issue_description,
                    'resolution_notes' => $request->resolution_notes,
                    'status' => $request->status,
                    'created_at' => date('d M Y H:i', strtotime($request->created_at)),
                    'resolved_by_name' => $request->resolver ? $request->resolver->full_name : null
                ];
            }

            $projectMembers = [];
            if ($projectId) {
                $boardIds = (new \yii\db\Query())
                    ->select('board_id')
                    ->from('boards')
                    ->where(['project_id' => $projectId])
                    ->column();

                $cardIds = !empty($boardIds)
                    ? (new \yii\db\Query())
                        ->select('card_id')
                        ->from('cards')
                        ->where(['board_id' => $boardIds])
                        ->column()
                    : [];

                if (!empty($cardIds)) {
                    $userIds = (new \yii\db\Query())
                        ->select('user_id')
                        ->distinct(true)
                        ->from('card_assignments')
                        ->where(['card_id' => $cardIds])
                        ->column();

                    foreach ($userIds as $uid) {
                        $user = User::findOne($uid);
                        if (!$user) { continue; }

                        $roleRows = (new \yii\db\Query())
                            ->select('c.assigned_role')
                            ->distinct(true)
                            ->from(['ca' => 'card_assignments'])
                            ->innerJoin(['c' => 'cards'], 'c.card_id = ca.card_id')
                            ->where(['ca.user_id' => $uid])
                            ->andWhere(['c.card_id' => $cardIds])
                            ->column();
                        $roles = array_values(array_unique(array_filter($roleRows)));

                        $firstAssignDate = (new \yii\db\Query())
                            ->select(['min_created' => 'MIN(c.created_at)'])
                            ->from(['ca' => 'card_assignments'])
                            ->innerJoin(['c' => 'cards'], 'c.card_id = ca.card_id')
                            ->where(['ca.user_id' => $uid])
                            ->andWhere(['c.card_id' => $cardIds])
                            ->scalar();

                        $pm = ProjectMember::find()
                            ->where(['project_id' => $projectId, 'user_id' => $uid])
                            ->one();
                        if (!$pm) {
                            $pm = new ProjectMember();
                            $pm->project_id = (int)$projectId;
                            $pm->user_id = (int)$uid;
                            $pm->joined_at = $firstAssignDate ?: $project->created_at;
                            @$pm->save();
                        }

                        $projectMembers[] = [
                            'user_id' => (int)$uid,
                            'full_name' => (string)$user->full_name,
                            'roles' => $roles,
                            'joined_at' => $firstAssignDate ? date('d M Y H:i', strtotime($firstAssignDate)) : null,
                        ];
                    }
                }

                if ($project && !empty($project->team_lead_id)) {
                    $tlId = (int)$project->team_lead_id;
                    $tlUser = User::findOne($tlId);
                    if ($tlUser) {
                        if ($project->status === 'active') {
                            $pm = ProjectMember::find()->where(['project_id' => $projectId, 'user_id' => $tlId])->one();
                            if (!$pm) {
                                $pm = new ProjectMember();
                                $pm->project_id = (int)$projectId;
                                $pm->user_id = (int)$tlId;
                                $pm->joined_at = $project->created_at;
                                @$pm->save();
                            }
                        }

                        $found = false;
                        foreach ($projectMembers as &$m) {
                            if ((int)$m['user_id'] === $tlId) {
                                $m['roles'] = array_values(array_unique(array_merge($m['roles'] ?: [], ['team_lead'])));
                                $m['joined_at'] = $project->created_at ? date('d M Y H:i', strtotime($project->created_at)) : $m['joined_at'];
                                $found = true;
                                break;
                            }
                        }
                        unset($m);

                        if (!$found) {
                            $projectMembers[] = [
                                'user_id' => $tlId,
                                'full_name' => (string)$tlUser->full_name,
                                'roles' => ['team_lead'],
                                'joined_at' => $project->created_at ? date('d M Y H:i', strtotime($project->created_at)) : null,
                            ];
                        }
                    }
                }
            }
            
            $responseData = [
                'success' => true,
                'card' => [
                    'card_id' => $card->card_id,
                    'card_title' => $card->card_title,
                    'description' => $card->description ?: '-',
                    'board_id' => $card->board_id,
                    'board_name' => $card->board ? $card->board->board_name : 'Unknown',
                    'project_id' => $projectId,
                    'project_name' => $projectName,
                    'created_by' => $card->creator ? $card->creator->full_name : 'Unknown',
                    'assigned_role' => $card->assigned_role,
                    'assigned_user_id' => $assignedUserId,
                    'assigned_user' => $assignedUserName,
                    'status' => $card->status,
                    'priority' => $card->priority,
                    'estimated_hours' => (float)$card->estimated_hours,
                    'actual_hours' => (float)$card->actual_hours,
                    'progress_percentage' => $progress,
                    'created_at' => $card->created_at,
                    'due_date' => $card->due_date,
                    'subtasks' => array_map(function($s){
                        return [
                            'subtask_id' => (int)$s->subtask_id,
                            'subtask_title' => (string)$s->subtask_title,
                            'description' => (string)$s->description,
                            'status' => (string)$s->status,
                            'estimated_hours' => (float)$s->estimated_hours,
                            'actual_hours' => (float)$s->actual_hours,
                            'due_date' => (string)$s->due_date,
                            'created_at' => (string)$s->created_at,
                            'created_by_name' => $s->creator ? $s->creator->full_name : 'Unknown',
                        ];
                    }, $subtasks),
                    'comments' => $commentsData,
                    'help_requests' => $helpRequestsData,
                    'project_members' => $projectMembers,
                ]
            ];
            
            return $responseData;

        } catch (\Exception $e) {
            Yii::error('Error in get-card-detail: ' . $e->getMessage());
            Yii::error('Stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()];
        }
    }

    public function actionDeleteCard($id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $model = Card::findOne($id);
        
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Card not found.');
            return $this->redirect(['site/member-cards']);
        }

        $worked = Subtask::find()->where(['card_id' => $model->card_id])->exists()
            || TimeLog::find()->where(['card_id' => $model->card_id])->exists()
            || ($model->status !== 'todo');
        if ($worked) {
            Yii::$app->session->setFlash('error', 'Card has been worked on and cannot be deleted.');
            return $this->redirect(['site/member-cards']);
        }
        
        if ($model && $model->delete()) {
            Yii::$app->session->setFlash('success', 'Card deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete card.');
        }
        
        return $this->redirect(['site/member-cards']);
    }
}