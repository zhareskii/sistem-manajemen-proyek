<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\Project;
use app\models\User;
use app\models\Board;
use app\models\Card;
use app\models\Subtask;
use app\models\TimeLog;

class ProjectController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'delete-project' => ['post'],
                    'create-project' => ['post'],
                    'update-project' => ['post'],
                    'update-project-status' => ['post'],
                ],
            ],
        ];
    }

    public function actionAdminProjects()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $projects = Project::find()
            ->with(['teamLead', 'creator'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $projectModel = new Project();
        $users = User::find()->where(['role' => 'member'])->all();

        return $this->render('@app/views/site/admin/projects', [
            'projects' => $projects,
            'projectModel' => $projectModel,
            'users' => $users,
        ]);
    }

    public function actionCreateProject()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $model = new Project();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_by = Yii::$app->user->id;

            if ($model->save()) {
                $board = new Board();
                $board->project_id = $model->project_id;
                $board->board_name = 'Default Board - ' . $model->project_name;

                if (!$board->save()) {
                    Yii::$app->session->setFlash('error', 'Project dibuat, namun gagal membuat board default.');
                } else {
                    Yii::$app->session->setFlash('success', 'Project dan board default berhasil dibuat.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menambahkan project: ' . implode(', ', $model->getFirstErrors()));
            }

            return $this->redirect(['site/admin-projects']);
        }

        return $this->redirect(['site/admin-projects']);
    }

    public function actionUpdateProject()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $id = Yii::$app->request->post('Project')['project_id'] ?? null;
        $model = $id ? Project::findOne($id) : null;

        if (!$model) {
            Yii::$app->session->setFlash('error', 'Project not found.');
            return $this->redirect(['site/admin-projects']);
        }

        $boardIds = Board::find()->where(['project_id' => $model->project_id])->select('board_id')->column();
        $cardIdsQuery = Card::find()->where(['board_id' => $boardIds])->select('card_id');
        $hasActiveCards = Card::find()->where(['board_id' => $boardIds])->andWhere(['in','status',['in_progress','review','done']])->exists();
        $hasSubtasks = Subtask::find()->where(['card_id' => $cardIdsQuery])->exists();
        $hasTimeLogs = TimeLog::find()->where(['card_id' => $cardIdsQuery])->exists();
        $projectCompleted = ($model->status === 'completed');

        if ($projectCompleted || $hasActiveCards || $hasSubtasks || $hasTimeLogs) {
            Yii::$app->session->setFlash('error', 'Completed/active projects cannot be updated.');
            return $this->redirect(['site/admin-projects']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Project updated successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update project.');
        }

        return $this->redirect(['site/admin-projects']);
    }

    public function actionDeleteProject($id)
    //Seperti stored procedure, ini menjalankan serangkaian query terkait untuk memeriksa kondisi sebelum delete.
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $model = Project::findOne($id);

        if (!$model) {
            Yii::$app->session->setFlash('error', 'Project not found.');
            return $this->redirect(['site/admin-projects']);
        }

        $boardIds = Board::find()->where(['project_id' => $model->project_id])->select('board_id')->column();
        $cardIdsQuery = Card::find()->where(['board_id' => $boardIds])->select('card_id');
        $hasActiveCards = Card::find()->where(['board_id' => $boardIds])->andWhere(['in','status',['in_progress','review','done']])->exists();
        $hasSubtasks = Subtask::find()->where(['card_id' => $cardIdsQuery])->exists();
        $hasTimeLogs = TimeLog::find()->where(['card_id' => $cardIdsQuery])->exists();
        $projectCompleted = ($model->status === 'completed');

        if ($projectCompleted || $hasActiveCards || $hasSubtasks || $hasTimeLogs) {
            Yii::$app->session->setFlash('error', 'Completed/active projects cannot be deleted.');
            return $this->redirect(['site/admin-projects']);
        }

        $model->status = 'cancelled';
        @$model->save(false, ['status']);

        if ($model && $model->delete()) {
            Yii::$app->session->setFlash('success', 'Project deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete project.');
        }

        return $this->redirect(['site/admin-projects']);
    }

    public function actionGetProjectDetail($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $projectModel = Project::findOne($id);
            if ($projectModel) {
                try {
                    $projectModel->updateProgress();
                } catch (\Exception $e) {
                    Yii::error('Error updating progress for project ' . $id . ': ' . $e->getMessage());
                }
            }

            $project = Project::find()
                ->select([
                    'projects.*',
                    'teamLead.full_name as team_lead_name',
                    'creator.full_name as created_by_name'
                ])
                ->leftJoin('users as teamLead', 'projects.team_lead_id = teamLead.user_id')
                ->leftJoin('users as creator', 'projects.created_by = creator.user_id')
                ->where(['projects.project_id' => $id])
                ->asArray()
                ->one();

            if (!$project) {
                return ['success' => false, 'message' => 'Project not found'];
            }

            $boardIds = Board::find()->where(['project_id' => $id])->select('board_id')->column();
            $cardIds = Card::find()->where(['board_id' => $boardIds])->select('card_id')->column();
            $totalCards = count($cardIds);
            $inProgressCards = Card::find()->where(['board_id' => $boardIds, 'status' => 'in_progress'])->count();
            $reviewCards = Card::find()->where(['board_id' => $boardIds, 'status' => 'review'])->count();
            $doneCards = Card::find()->where(['board_id' => $boardIds, 'status' => 'done'])->count();

            $totalSubtasks = Subtask::find()->where(['card_id' => $cardIds])->count();
            $completedSubtasks = Subtask::find()->where(['card_id' => $cardIds, 'status' => 'done'])->count();

            $sumMinutes = TimeLog::find()
                ->where(['card_id' => $cardIds])
                ->andWhere('end_time IS NOT NULL')
                ->select(['SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) AS total_minutes'])
                ->scalar();
            $totalTimeSpentMinutes = (int)($sumMinutes ?: 0);

            return [
                'success' => true,
                'project' => $project,
                'stats' => [
                    'total_cards' => $totalCards,
                    'in_progress_cards' => (int)$inProgressCards,
                    'review_cards' => (int)$reviewCards,
                    'done_cards' => (int)$doneCards,
                    'total_subtasks' => (int)$totalSubtasks,
                    'completed_subtasks' => (int)$completedSubtasks,
                    'total_time_spent_minutes' => $totalTimeSpentMinutes,
                ],
            ];
        } catch (\Exception $e) {
            Yii::error('Error in get-project-detail: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Internal error'];
        }
    }

    public function actionUpdateProjectStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $projectId = Yii::$app->request->post('project_id');
        $status = Yii::$app->request->post('status');

        if (!$projectId || !$status) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }

        $project = Project::findOne($projectId);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found'];
        }

        $project->status = $status;
        if ($project->save(false, ['status'])) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Failed to update status'];
    }
}