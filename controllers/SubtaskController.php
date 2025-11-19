<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\Subtask;
use app\models\Comment;
use app\models\HelpRequest;
use app\models\TimeLog;
use app\models\CardAssignment;

class SubtaskController extends Controller
{
    public function actionMemberSubtasks()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $userCardIds = \app\models\CardAssignment::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->select('card_id')
            ->column();

        $subtasks = Subtask::find()
            ->where([
                'or',
                ['card_id' => $userCardIds],
                ['created_by' => Yii::$app->user->id]
            ])
            ->with([
                'card',
                'card.board',
                'card.board.project',
                'creator',
                'comments.user',
                'timeLogs',
                'helpRequests'
            ])
            ->orderBy([
                new \yii\db\Expression('CASE status WHEN "todo" THEN 0 WHEN "in_progress" THEN 1 ELSE 2 END'),
                'card_id' => SORT_ASC,
                'position' => SORT_ASC
            ])
            ->all();

        $availableCards = \app\models\Card::find()
            ->alias('c')
            ->joinWith(['board b', 'board.project p'])
            ->where(['c.card_id' => $userCardIds])
            ->andWhere(['<>', 'p.status', 'completed'])
            ->with(['board', 'board.project'])
            ->all();

        $subtaskModel = new Subtask();

        return $this->render('@app/views/site/member/subtasks', [
            'allSubtasks' => $subtasks,
            'availableCards' => $availableCards,
            'subtaskModel' => $subtaskModel,
        ]);
    }

    public function actionSubtasks($card_id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $card = \app\models\Card::findOne($card_id);
        if (!$card) {
            throw new \yii\web\NotFoundHttpException('Card not found');
        }

        $hasAccess = false;
        if (Yii::$app->user->identity->role === 'member') {
            $project = $card->board->project;
            if ($project->team_lead_id == Yii::$app->user->id) {
                $hasAccess = true;
            } else {
                $userAssignments = \app\models\CardAssignment::find()
                    ->where(['card_id' => $card_id, 'user_id' => Yii::$app->user->id])
                    ->exists();
                $hasAccess = $userAssignments;
            }
        } elseif (Yii::$app->user->identity->role === 'admin') {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            Yii::$app->session->setFlash('error', 'You do not have access to this card.');
            return $this->redirect(['site/dashboard-member']);
        }

        $subtasks = Subtask::find()
            ->where(['card_id' => $card_id])
            ->with(['creator', 'comments.user', 'timeLogs', 'helpRequests'])
            ->orderBy(['position' => SORT_ASC])
            ->all();

        $subtaskModel = new Subtask();
        $commentModel = new \app\models\Comment();
        $helpRequestModel = new \app\models\HelpRequest();

        return $this->render('@app/views/site/subtasks', [
            'card' => $card,
            'subtasks' => $subtasks,
            'subtaskModel' => $subtaskModel,
            'commentModel' => $commentModel,
            'helpRequestModel' => $helpRequestModel,
        ]);
    }

    public function actionCreateSubtask()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $model = new Subtask();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_by = Yii::$app->user->id;

            $card = \app\models\Card::findOne($model->card_id);
            if (!$card) {
                Yii::$app->session->setFlash('error', 'Card for subtask not found.');
                return $this->redirect(['site/dashboard-member']);
            }

            $project = $card->board ? \app\models\Project::findOne($card->board->project_id) : null;
            $isTeamLead = ($project && $project->team_lead_id == Yii::$app->user->id);
            $isAssigned = \app\models\CardAssignment::find()->where(['card_id' => $card->card_id, 'user_id' => Yii::$app->user->id])->exists();
            if (!$isTeamLead && !$isAssigned) {
                Yii::$app->session->setFlash('error', 'You are not the Team Lead or not assigned to this card.');
                return $this->redirect(['site/subtasks', 'card_id' => $model->card_id]);
            }
            if ($project && $project->status === 'completed') {
                Yii::$app->session->setFlash('error', 'Cannot create subtasks in a completed project.');
                return $this->redirect(['site/subtasks', 'card_id' => $model->card_id]);
            }

            if ($model->save()) {
                if ($card && $card->status === 'todo') {
                    $card->status = 'in_progress';
                    $card->save(false);
                }

                Yii::$app->session->setFlash('success', 'Subtask added successfully.');
                return $this->redirect(['site/member-subtasks']);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to add subtask: ' . implode(', ', $model->getFirstErrors()));
                return $this->redirect(['site/member-subtasks']);
            }
        }

        return $this->redirect(['site/dashboard-member']);
    }

    public function actionUpdateSubtask($id = null)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        if ($id === null) {
            $id = Yii::$app->request->post('Subtask')['subtask_id'] ?? null;
        }

        $model = Subtask::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Subtask not found.');
            return $this->redirect(['site/dashboard-member']);
        }

        $hasAccess = $this->checkSubtaskAccess($model);
        if (!$hasAccess) {
            Yii::$app->session->setFlash('error', 'You do not have access to this subtask.');
            return $this->redirect(['site/dashboard-member']);
        }

        if ($model->status === 'done') {
            Yii::$app->session->setFlash('error', 'Completed subtasks cannot be edited.');
            return $this->redirect(['site/member-subtasks']);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $model->updateProgressForCardAndProject();
                Yii::$app->session->setFlash('success', 'Subtask updated successfully.');
                return $this->redirect(['site/member-subtasks']);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to update subtask: ' . implode(', ', $model->getFirstErrors()));
            }
        }

        return $this->redirect(['site/member-subtasks']);
    }

    public function actionUpdateSubtaskStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        $subtaskId = Yii::$app->request->post('subtask_id');
        $status = Yii::$app->request->post('status');

        $model = Subtask::findOne($subtaskId);
        if (!$model) {
            return ['success' => false, 'message' => 'Subtask not found'];
        }

        $hasAccess = $this->checkSubtaskAccess($model);
        if (!$hasAccess) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        if ($model->status === 'done') {
            return ['success' => false, 'message' => 'Completed subtasks cannot be modified'];
        }

        $model->status = $status;
        if ($model->save()) {
            $model->updateProgressForCardAndProject();
            return ['success' => true, 'message' => 'Subtask status updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update subtask status'];
        }
    }

    public function actionDeleteSubtask($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = Subtask::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Subtask not found.');
            return $this->redirect(['site/dashboard-member']);
        }

        $hasAccess = $this->checkSubtaskAccess($model);
        if (!$hasAccess) {
            Yii::$app->session->setFlash('error', 'You do not have access to this subtask.');
            return $this->redirect(['site/dashboard-member']);
        }
        if ($model->status === 'done') {
            Yii::$app->session->setFlash('error', 'Completed subtasks cannot be deleted.');
            return $this->redirect(['site/subtasks', 'card_id' => $model->card_id]);
        }

        $card_id = $model->card_id;
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Subtask deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete subtask.');
        }

        return $this->redirect(['site/subtasks', 'card_id' => $card_id]);
    }

    public function actionMemberSubtasksBoard()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $projects = \app\models\Project::find()
            ->where(['team_lead_id' => Yii::$app->user->id])
            ->andWhere(['<>', 'status', 'completed'])
            ->all();

        $subtasksByStatus = [
            'todo' => [],
            'in_progress' => [],
            'review' => [],
            'done' => []
        ];

        foreach ($projects as $project) {
            $boards = \app\models\Board::find()->where(['project_id' => $project->project_id])->all();
            foreach ($boards as $board) {
                $boardSubtasks = Subtask::find()
                    ->joinWith(['card'])
                    ->where(['cards.board_id' => $board->board_id])
                    ->andWhere([
                        'or',
                        ['subtasks.created_by' => Yii::$app->user->id],
                        ['cards.created_by' => Yii::$app->user->id]
                    ])
                    ->with(['creator', 'card', 'card.board', 'card.board.project'])
                    ->orderBy(['subtasks.position' => SORT_ASC, 'subtasks.created_at' => SORT_DESC])
                    ->all();

                foreach ($boardSubtasks as $subtask) {
                    $status = $subtask->status ?? 'todo';
                    if (isset($subtasksByStatus[$status])) {
                        $subtasksByStatus[$status][] = $subtask;
                    }
                }
            }
        }

        $availableCards = \app\models\Card::find()
            ->joinWith(['board'])
            ->where(['boards.project_id' => array_map(function($p) { return $p->project_id; }, $projects)])
            ->andWhere(['cards.created_by' => Yii::$app->user->id])
            ->with(['board', 'board.project'])
            ->all();

        $subtaskModel = new Subtask();

        return $this->render('@app/views/site/member/subtasks-board', [
            'subtasksByStatus' => $subtasksByStatus,
            'availableCards' => $availableCards,
            'subtaskModel' => $subtaskModel,
            'projects' => $projects,
        ]);
    }
    

    public function actionGetSubtaskDetail($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $subtask = Subtask::find()
            ->with(['creator', 'comments.user', 'timeLogs', 'helpRequests.creator', 'card.board.project'])
            ->where(['subtask_id' => $id])
            ->one();

        if (!$subtask) {
            return ['success' => false, 'message' => 'Subtask tidak ditemukan'];
        }

        if (!$this->checkSubtaskAccess($subtask)) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        $comments = [];
        foreach ($subtask->comments as $comment) {
            $comments[] = [
                'comment_id' => $comment->comment_id,
                'user_name' => $comment->user ? $comment->user->full_name : 'Unknown',
                'comment_text' => $comment->comment_text,
                'created_at' => date('d M Y H:i', strtotime($comment->created_at)),
                'can_edit' => $comment->canEdit(),
                'can_delete' => $comment->canDelete(),
            ];
        }

        $helpRequests = [];
        foreach ($subtask->helpRequests as $help) {
            $helpRequests[] = [
                'request_id' => $help->request_id,
                'creator_name' => $help->creator ? $help->creator->full_name : 'Unknown',
                'status' => $help->status,
                'issue_description' => $help->issue_description,
                'resolution_notes' => $help->resolution_notes,
                'created_at' => date('d M Y H:i', strtotime($help->created_at)),
                'can_edit' => $help->canEdit(),
                'can_delete' => $help->canDelete(),
            ];
        }

        $runningTimer = TimeLog::find()
            ->where(['subtask_id' => $id, 'user_id' => Yii::$app->user->id, 'end_time' => null])
            ->one();

        $project = ($subtask && $subtask->card && $subtask->card->board) ? $subtask->card->board->project : null;

        return [
            'success' => true,
            'subtask' => [
                'subtask_id' => $subtask->subtask_id,
                'subtask_title' => $subtask->subtask_title,
                'description' => $subtask->description,
                'status' => $subtask->status,
                'estimated_hours' => (float)$subtask->estimated_hours,
                'actual_hours' => (float)$subtask->actual_hours,
                'position' => $subtask->position,
                'created_by' => $subtask->creator ? $subtask->creator->full_name : 'Unknown',
                'card_title' => $subtask->card ? $subtask->card->card_title : 'Unknown Card',
                'project_name' => $project ? $project->project_name : 'Unknown Project',
            ],
            'project' => $project ? [
                'project_id' => $project->project_id,
                'project_name' => $project->project_name,
                'team_lead_id' => (int)$project->team_lead_id,
            ] : null,
            'comments' => $comments,
            'help_requests' => $helpRequests,
            'running_timer' => $runningTimer ? [
                'log_id' => $runningTimer->log_id,
                'start_time' => $runningTimer->start_time,
                'start_timestamp' => strtotime($runningTimer->start_time) * 1000,
            ] : null,
        ];
    }

    public function actionGetSubtaskComments($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $subtask = Subtask::findOne($id);
        if (!$subtask) {
            return ['success' => false, 'message' => 'Subtask not found'];
        }
        
        if (!$this->checkSubtaskAccess($subtask)) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        
        $comments = Comment::find()
            ->where(['subtask_id' => $id])
            ->with(['user'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        
        $commentsData = [];
        foreach ($comments as $comment) {
            $commentsData[] = [
                'comment_id' => $comment->comment_id,
                'comment_text' => $comment->comment_text,
                'created_at' => Yii::$app->formatter->asDatetime($comment->created_at),
                'user_name' => $comment->user ? $comment->user->full_name : 'Unknown',
            ];
        }
        
        return ['success' => true, 'comments' => $commentsData];
    }

    public function actionGetSubtaskHelpRequests($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $subtask = Subtask::findOne($id);
        if (!$subtask) {
            return ['success' => false, 'message' => 'Subtask not found'];
        }
        
        if (!$this->checkSubtaskAccess($subtask)) {
            return ['success' => false, 'message' => 'Access denied'];
        }
        
        $helpRequests = HelpRequest::find()
            ->where(['subtask_id' => $id])
            ->with(['creator', 'resolver'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        
        $helpRequestsData = [];
        foreach ($helpRequests as $help) {
            $helpRequestsData[] = [
                'request_id' => $help->request_id,
                'issue_description' => $help->issue_description,
                'status' => $help->status,
                'resolution_notes' => $help->resolution_notes,
                'created_at' => Yii::$app->formatter->asDatetime($help->created_at),
                'resolved_at' => $help->resolved_at ? Yii::$app->formatter->asDatetime($help->resolved_at) : null,
                'creator_name' => $help->creator ? $help->creator->full_name : 'Unknown',
                'resolved_by_name' => $help->resolver ? $help->resolver->full_name : null,
            ];
        }
        
        return ['success' => true, 'help_requests' => $helpRequestsData];
    }

    private function checkSubtaskAccess($subtask)
    {
        if (Yii::$app->user->identity->role === 'admin') {
            return true;
        }

        if (Yii::$app->user->identity->role === 'member') {
            $card = $subtask->card;
            if (!$card || !$card->board || !$card->board->project) {
                return false;
            }
            $project = $card->board->project;
            
            if ($project->team_lead_id == Yii::$app->user->id) {
                return true;
            }
            
            $isAssigned = CardAssignment::find()
                ->where(['card_id' => $card->card_id, 'user_id' => Yii::$app->user->id])
                ->exists();
            
            $isCreator = $subtask->created_by == Yii::$app->user->id;
            
            return $isAssigned || $isCreator;
        }

        return false;
    }
}