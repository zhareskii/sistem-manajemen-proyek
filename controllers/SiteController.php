<?php
namespace app\controllers;

use Yii;
use yii\helpers\Html;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\User;
use app\models\Project;
use app\models\Card;
use app\models\CardAssignment;
use app\models\Board;
use app\models\Subtask;
use app\models\Comment;
use app\models\TimeLog;
use app\models\HelpRequest;
use app\models\ProjectMember;
use app\models\TimeTracking; 
use app\models\CardSubmission;


class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'dashboard-admin', 'dashboard-member', 'admin-projects', 'admin-boards', 'create-project', 'update-project', 'delete-project', 'member-cards', 'create-card', 'update-card', 'delete-card', 'get-card-detail', 'get-project-detail', 'member-boards', 'create-board', 'update-board', 'delete-board', 'get-board-detail', 'admin-users', 'create-user', 'update-user', 'delete-user', 'subtasks', 'member-subtasks', 'create-subtask', 'update-subtask', 'delete-subtask', 'start-timer', 'stop-timer', 'check-running-timer', 'add-comment', 'create-help-request', 'update-help-request', 'get-subtask-detail', 'get-subtask-comments', 'get-subtask-help-requests', 'reports-member', 'reports-admin', 'dashboard-enhanced', 'start-tracking', 'stop-tracking', 'get-tracking-status', 'update-my-profile'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['dashboard-member', 'member-cards', 'create-card', 'update-card', 'delete-card', 'get-card-detail', 'get-project-detail', 'member-boards', 'subtasks', 'member-subtasks', 'create-subtask', 'update-subtask', 'delete-subtask', 'start-timer', 'stop-timer', 'check-running-timer', 'add-comment', 'create-help-request', 'update-help-request', 'get-subtask-detail', 'get-subtask-comments', 'get-subtask-help-requests', 'reports-member', 'dashboard-enhanced', 'start-tracking', 'stop-tracking', 'get-tracking-status', 'update-my-profile'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === 'member';
                        }
                    ],
                    [
                        'actions' => ['dashboard-admin', 'admin-projects', 'admin-boards', 'create-project', 'update-project', 'delete-project', 'get-project-detail', 'get-card-detail', 'admin-users', 'create-user', 'update-user', 'delete-user', 'update-project-status', 'reports-admin', 'add-comment', 'update-help-request', 'update-my-profile'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === 'admin';
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'delete-project' => ['post'],
                    'create-project' => ['post'],
                    'update-project' => ['post'],
                    'delete-card' => ['post'],
                    'create-card' => ['post'],
                    'update-card' => ['post'],
                    'delete-board' => ['post'],
                    'create-board' => ['post'],
                    'update-board' => ['post'],
                    'delete-user' => ['post'],
                    'create-user' => ['post'],
                    'update-user' => ['post'],
                    'update-my-profile' => ['post'],
                    'create-subtask' => ['post'],
                    'update-subtask' => ['post'],
                    'delete-subtask' => ['post'],
                    'start-timer' => ['post'],
                    'stop-timer' => ['post'],
                    'add-comment' => ['post'],
                    'create-help-request' => ['post'],
                    'update-help-request' => ['post'],
                    'update-project-status' => ['post'],
                    'start-tracking' => ['post'],
                    'stop-tracking' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('landing');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            return $user->role === 'admin'
                ? $this->redirect(['site/dashboard-admin'])
                : $this->redirect(['site/dashboard-member']);
        }

        $model = new LoginForm();
        $error = null;

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = Yii::$app->user->identity;
            return $user->role === 'admin'
                ? $this->redirect(['site/dashboard-admin'])
                : $this->redirect(['site/dashboard-member']);
        } elseif (Yii::$app->request->isPost) {
            $error = 'Username atau password salah.';
        }

        $model->password = '';
        return $this->render('login', ['model' => $model, 'error' => $error]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['site/index']);
    }

    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        if (Yii::$app->request->isPost) {
            $username = Yii::$app->request->post('username');
            $email = Yii::$app->request->post('email');
            $full_name = Yii::$app->request->post('full_name');
            $password = Yii::$app->request->post('password');

            if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
                Yii::$app->session->setFlash('error', 'Semua field harus diisi.');
                return $this->render('register');
            }

            if (User::registerUser($username, $email, $full_name, $password)) {
                Yii::$app->session->setFlash('success', 'Registrasi berhasil. Silakan login.');
                return $this->redirect(['site/login']);
            } else {
                Yii::$app->session->setFlash('error', 'Username sudah digunakan atau terjadi kesalahan.');
            }
        }

        return $this->render('register');
    }

    public function actionDashboardAdmin()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }
        
        $totalProjects = Project::find()->count();
        $completedProjects = Project::find()->where(['status' => 'completed'])->count();
        $inProgressProjects = Project::find()->where(['status' => 'active'])->count();
        $totalUsers = User::find()->where(['role' => 'member'])->count();
        
        $recentComments = Comment::find()
            ->with(['user', 'subtask.card.board.project'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();
        
        $pendingHelpRequests = HelpRequest::find()
            ->where(['status' => 'pending'])
            ->with(['creator', 'subtask.card.board.project'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();
        
        return $this->render('admin/dashboard', [
            'totalProjects' => $totalProjects,
            'completedProjects' => $completedProjects,
            'inProgressProjects' => $inProgressProjects,
            'totalUsers' => $totalUsers,
            'recentComments' => $recentComments,
            'pendingHelpRequests' => $pendingHelpRequests
        ]);
    }

    public function actionDashboardMember()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
        return $this->redirect(['site/login']);
    }
    
    $userId = Yii::$app->user->id;
    $projects = Project::find()
        ->innerJoin('boards b', 'b.project_id = projects.project_id')
        ->innerJoin('cards c', 'c.board_id = b.board_id')
        ->innerJoin('card_assignments ca', 'ca.card_id = c.card_id')
        ->where(['ca.user_id' => $userId])
        ->orderBy(['projects.created_at' => SORT_DESC])
        ->distinct(true)
        ->all();

    // Get assigned cards count
    $assignedCardsCount = CardAssignment::find()
        ->where(['user_id' => $userId])
        ->count();
    
    // Get completed subtasks count
    $completedSubtasksCount = Subtask::find()
        ->where(['created_by' => $userId, 'status' => 'done'])
        ->count();
    
    // Get time tracking data
    $currentTracking = TimeTracking::getActiveSession($userId);
    $totalMinutesToday = TimeTracking::getTotalMinutesToday($userId);
    
    // Recent created items (cards/subtasks) by member
    $recentCards = Card::find()
        ->where(['created_by' => $userId])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(3)
        ->all();
    $recentSubtasks = Subtask::find()
        ->where(['created_by' => $userId])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(3)
        ->all();
    $recentActivities = [];
    foreach ($recentCards as $c) {
        $recentActivities[] = [
            'type' => 'card',
            'title' => $c->card_title,
            'created_at' => $c->created_at,
        ];
    }
    foreach ($recentSubtasks as $s) {
        $recentActivities[] = [
            'type' => 'subtask',
            'title' => $s->subtask_title,
            'created_at' => $s->created_at,
        ];
    }
    usort($recentActivities, function($a,$b){
        return strtotime($b['created_at']) <=> strtotime($a['created_at']);
    });
    $recentActivities = array_slice($recentActivities, 0, 2);

    // Latest comment by member
    $latestComment = Comment::find()
        ->with(['subtask'])
        ->where(['user_id' => $userId])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(1)
        ->one();

    // Latest help request created or resolved by member
    $latestBlocker = HelpRequest::find()
        ->where(['or',
            ['user_id' => $userId],
            ['resolved_by' => $userId]
        ])
        ->orderBy([new \yii\db\Expression('COALESCE(resolved_at, created_at) DESC')])
        ->limit(1)
        ->one();
    
    return $this->render('member/dashboard', [
        'projects' => $projects,
        'assignedCardsCount' => $assignedCardsCount,
        'completedSubtasksCount' => $completedSubtasksCount,
        'recentActivities' => $recentActivities,
        'latestComment' => $latestComment,
        'latestBlocker' => $latestBlocker,
        'currentTracking' => $currentTracking,
        'totalMinutesToday' => $totalMinutesToday
    ]);
}

    // Project Methods (keeping existing code)
    public function actionAdminProjects()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
        return $this->redirect(['site/login']);
    }

    $projects = Project::find()
        ->with(['teamLead', 'creator'])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    // Debug: cek data project pertama
    if (!empty($projects)) {
        $firstProject = $projects[0];
        Yii::error('DEBUG FIRST PROJECT:');
        Yii::error('Project Name: ' . $firstProject->project_name);
        Yii::error('Team Lead ID: ' . $firstProject->team_lead_id);
        Yii::error('Created By: ' . $firstProject->created_by);
        Yii::error('Team Lead Object: ' . ($firstProject->teamLead ? 'EXISTS - ' . $firstProject->teamLead->full_name : 'NULL'));
        Yii::error('Creator Object: ' . ($firstProject->creator ? 'EXISTS - ' . $firstProject->creator->full_name : 'NULL'));
    }
        
    $projectModel = new Project();
    $users = User::find()->where(['role' => 'member'])->all();
    
    return $this->render('admin/projects', [
        'projects' => $projects,
        'projectModel' => $projectModel,
        'users' => $users
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
                // Auto-create default board
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

        $id = Yii::$app->request->post('Project')['project_id'];
        $model = Project::findOne($id);
        
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Project not found.');
            return $this->redirect(['site/admin-projects']);
        }

        // Block update if project is completed or has worked content
        $boardIds = \app\models\Board::find()->where(['project_id' => $model->project_id])->select('board_id')->column();
        $cardIdsQuery = \app\models\Card::find()->where(['board_id' => $boardIds])->select('card_id');
        $hasActiveCards = \app\models\Card::find()->where(['board_id' => $boardIds])->andWhere(['in','status',['in_progress','review','done']])->exists();
        $hasSubtasks = \app\models\Subtask::find()->where(['card_id' => $cardIdsQuery])->exists();
        $hasTimeLogs = \app\models\TimeLog::find()->where(['card_id' => $cardIdsQuery])->exists();
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
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $model = Project::findOne($id);
        
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Project not found.');
            return $this->redirect(['site/admin-projects']);
        }

        // Block delete if project is completed or has worked content
        $boardIds = \app\models\Board::find()->where(['project_id' => $model->project_id])->select('board_id')->column();
        $cardIdsQuery = \app\models\Card::find()->where(['board_id' => $boardIds])->select('card_id');
        $hasActiveCards = \app\models\Card::find()->where(['board_id' => $boardIds])->andWhere(['in','status',['in_progress','review','done']])->exists();
        $hasSubtasks = \app\models\Subtask::find()->where(['card_id' => $cardIdsQuery])->exists();
        $hasTimeLogs = \app\models\TimeLog::find()->where(['card_id' => $cardIdsQuery])->exists();
        $projectCompleted = ($model->status === 'completed');

        if ($projectCompleted || $hasActiveCards || $hasSubtasks || $hasTimeLogs) {
            Yii::$app->session->setFlash('error', 'Completed/active projects cannot be deleted.');
            return $this->redirect(['site/admin-projects']);
        }
        
        // Soft cancel status sebelum delete
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
        // Pastikan progress project dihitung ulang dan disimpan ke database
        $projectModel = Project::findOne($id);
        if ($projectModel) {
            try {
                // Menghitung progress sesuai rumus dan menyimpannya
                $projectModel->updateProgress();
            } catch (\Exception $e) {
                Yii::error('Error updating progress for project ' . $id . ': ' . $e->getMessage());
                // Continue without updating progress
            }
        }

        // Query project dengan join manual untuk memastikan data user terambil
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
            return ['success' => false, 'message' => 'Project not found.'];
        }

        // Get cards created by team lead in this project
        $teamLeadCards = [];
        if ($project['team_lead_id']) {
            $teamLeadCards = Card::find()
                ->select([
                    'cards.*',
                    'boards.board_name'
                ])
                ->leftJoin('boards', 'cards.board_id = boards.board_id')
                ->where(['boards.project_id' => $id])
                ->andWhere(['cards.created_by' => $project['team_lead_id']])
                ->orderBy(['cards.status' => SORT_ASC, 'cards.created_at' => SORT_DESC])
                ->asArray()
                ->all();

            // Enrich each card with calculated progress_percentage
            $enrichedCards = [];
            foreach ($teamLeadCards as $cardRow) {
                try {
                    $totalSubtasks = (int) Subtask::find()->where(['card_id' => $cardRow['card_id']])->count();
                    $doneSubtasks = (int) Subtask::find()->where(['card_id' => $cardRow['card_id'], 'status' => 'done'])->count();
                    $progress = $totalSubtasks > 0 ? round(($doneSubtasks / $totalSubtasks) * 100, 2) : 0;
                    $cardRow['progress_percentage'] = $progress;
                } catch (\Exception $e) {
                    Yii::error('Error calculating card progress: ' . $e->getMessage());
                    $cardRow['progress_percentage'] = 0;
                }
                $enrichedCards[] = $cardRow;
            }
            $teamLeadCards = $enrichedCards;
        }

        // Collect project members from card assignments across all boards in the project
        $boardIds = (new \yii\db\Query())
            ->select('board_id')
            ->from('boards')
            ->where(['project_id' => $id])
            ->column();

        $cardIds = [];
        if (!empty($boardIds)) {
            $cardIds = (new \yii\db\Query())
                ->select('card_id')
                ->from('cards')
                ->where(['board_id' => $boardIds])
                ->column();
        }

        $assignedUsers = [];
        if (!empty($cardIds)) {
            // Get distinct users assigned to any card in this project
            $userIds = (new \yii\db\Query())
                ->select('user_id')
                ->distinct(true)
                ->from('card_assignments')
                ->where(['card_id' => $cardIds])
                ->column();

            foreach ($userIds as $uid) {
                $user = User::findOne($uid);
                if (!$user) { continue; }

                // Roles derived from assigned cards' assigned_role using a join
                $roleRows = (new \yii\db\Query())
                    ->select('c.assigned_role')
                    ->distinct(true)
                    ->from(['ca' => 'card_assignments'])
                    ->innerJoin(['c' => 'cards'], 'c.card_id = ca.card_id')
                    ->where(['ca.user_id' => $uid])
                    ->andWhere(['c.card_id' => $cardIds])
                    ->column();
                $roles = array_values(array_unique(array_filter($roleRows)));

                // Ambil tanggal pertama user diassign ke card dalam project ini
                $firstAssignDate = (new \yii\db\Query())
                    ->select(['min_created' => 'MIN(c.created_at)'])
                    ->from(['ca' => 'card_assignments'])
                    ->innerJoin(['c' => 'cards'], 'c.card_id = ca.card_id')
                    ->where(['ca.user_id' => $uid])
                    ->andWhere(['c.card_id' => $cardIds])
                    ->scalar();

                // Persist ke project_members jika project aktif dan belum ada
                if ($projectModel && $projectModel->status === 'active') {
                    $pm = ProjectMember::find()->where(['project_id' => $id, 'user_id' => $uid])->one();
                    if (!$pm) {
                        $pm = new ProjectMember();
                        $pm->project_id = (int)$id;
                        $pm->user_id = (int)$uid;
                        $pm->joined_at = $firstAssignDate ?: $projectModel->created_at;
                        @$pm->save();
                    }
                }

                // Tampilkan joined_at dari created_at card pertama
                $assignedUsers[] = [
                    'user_id' => (int)$uid,
                    'full_name' => (string)$user->full_name,
                    'roles' => $roles,
                    'joined_at' => $firstAssignDate ? date('d M Y H:i', strtotime($firstAssignDate)) : null,
                ];
            }
        }

        // Ensure Team Lead is included and persisted for active projects
        if (!empty($project['team_lead_id'])) {
            $tlId = (int)$project['team_lead_id'];
            $tlUser = User::findOne($tlId);
            if ($tlUser) {
                // Persist only for active projects
                if ($projectModel && $projectModel->status === 'active') {
                    $pm = ProjectMember::find()->where(['project_id' => $id, 'user_id' => $tlId])->one();
                    if (!$pm) {
                        $pm = new ProjectMember();
                        $pm->project_id = (int)$id;
                        $pm->user_id = (int)$tlId;
                        @$pm->save();
                    }
                }

                // Merge into project_members payload
                $found = false;
                foreach ($assignedUsers as &$au) {
                    if ((int)$au['user_id'] === $tlId) {
                        $au['roles'] = array_values(array_unique(array_merge($au['roles'] ?: [], ['team_lead'])));
                        // Override joined_at untuk team lead dari created_at project
                        $au['joined_at'] = $projectModel && $projectModel->created_at
                            ? date('d M Y H:i', strtotime($projectModel->created_at))
                            : ($project['created_at'] ? date('d M Y H:i', strtotime($project['created_at'])) : null);
                        $found = true;
                        break;
                    }
                }
                unset($au);

                if (!$found) {
                    $assignedUsers[] = [
                        'user_id' => $tlId,
                        'full_name' => (string)$tlUser->full_name,
                        'roles' => ['team_lead'],
                        // joined_at team lead dari created_at project
                        'joined_at' => $projectModel && $projectModel->created_at
                            ? date('d M Y H:i', strtotime($projectModel->created_at))
                            : ($project['created_at'] ? date('d M Y H:i', strtotime($project['created_at'])) : null),
                    ];
                }
            }
        }

        // Format dates
        $createdAt = $project['created_at'] ? date('d/m/Y, H.i', strtotime($project['created_at'])) : '-';
        $deadline = $project['deadline'] ? date('d/m/Y', strtotime($project['deadline'])) : 'Not set';

        // Gunakan progress dari database, jangan paksa update jika error
        $progressPercentage = $project['progress_percentage'] ?: 0;
        if ($projectModel && $projectModel->progress_percentage) {
            $progressPercentage = $projectModel->progress_percentage;
        }

        $responseData = [
            'project_id' => $project['project_id'],
            'project_name' => $project['project_name'],
            'description' => $project['description'] ?: '-',
            'status' => $project['status'] ?: 'planning',
            'progress_percentage' => $progressPercentage,
            'team_lead_id' => $project['team_lead_id'],
            'team_lead' => $project['team_lead_name'] ?: ($project['team_lead_id'] ? 'User ID: ' . $project['team_lead_id'] : 'Not Assigned'),
            'created_by' => $project['created_by_name'] ?: ($project['created_by'] ? 'User ID: ' . $project['created_by'] : 'Unknown'),
            'created_at' => $createdAt,
            'deadline' => $deadline,
            'difficulty_level' => $project['difficulty_level'] ?: 'medium',
            'team_lead_cards' => $teamLeadCards,
            'project_members' => $assignedUsers
        ];

        return ['success' => true, 'project' => $responseData];

    } catch (\Exception $e) {
        Yii::error('Error in get-project-detail: ' . $e->getMessage());
        Yii::error('Stack trace: ' . $e->getTraceAsString());
        return ['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()];
    }
}

    // Subtask Methods
    public function actionMemberSubtasks()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
        return $this->redirect(['site/login']);
    }

    // Get all cards where user is assigned
    $userCardIds = CardAssignment::find()
        ->where(['user_id' => Yii::$app->user->id])
        ->select('card_id')
        ->column();

    // Get all subtasks for these cards OR created by user
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

    // Get available cards for creating new subtasks (only from non-completed projects)
    $availableCards = Card::find()
        ->alias('c')
        ->joinWith(['board b', 'board.project p'])
        ->where(['c.card_id' => $userCardIds])
        ->andWhere(['<>', 'p.status', 'completed'])
        ->with(['board', 'board.project'])
        ->all();

    $subtaskModel = new Subtask();

    return $this->render('member/subtasks', [
        'allSubtasks' => $subtasks,
        'availableCards' => $availableCards,
        'subtaskModel' => $subtaskModel
    ]);
}

    public function actionSubtasks($card_id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $card = Card::findOne($card_id);
        if (!$card) {
            throw new \yii\web\NotFoundHttpException('Card not found');
        }

        // Check access
        $hasAccess = false;
        if (Yii::$app->user->identity->role === 'member') {
            $project = $card->board->project;
            if ($project->team_lead_id == Yii::$app->user->id) {
                $hasAccess = true;
            } else {
                $userAssignments = CardAssignment::find()
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
        $commentModel = new Comment();
        $helpRequestModel = new HelpRequest();

        return $this->render('subtasks', [
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

            // Validasi akses: hanya Team Lead project atau user yang di-assign ke card
            $card = Card::findOne($model->card_id);
            if (!$card) {
                Yii::$app->session->setFlash('error', 'Card for subtask not found.');
                return $this->redirect(['site/dashboard-member']);
            }

            $project = $card->board ? Project::findOne($card->board->project_id) : null;
            $isTeamLead = ($project && $project->team_lead_id == Yii::$app->user->id);
            $isAssigned = CardAssignment::find()->where(['card_id' => $card->card_id, 'user_id' => Yii::$app->user->id])->exists();
            if (!$isTeamLead && !$isAssigned) {
                Yii::$app->session->setFlash('error', 'You are not the Team Lead or not assigned to this card.');
                return $this->redirect(['site/subtasks', 'card_id' => $model->card_id]);
            }
            if ($project && $project->status === 'completed') {
                Yii::$app->session->setFlash('error', 'Cannot create subtasks in a completed project.');
                return $this->redirect(['site/subtasks', 'card_id' => $model->card_id]);
            }

            if ($model->save()) {
                // Update card status to in_progress if it's the first subtask
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
            
            // Redirect ke member subtasks page
            return $this->redirect(['site/member-subtasks']);
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update subtask: ' . implode(', ', $model->getFirstErrors()));
        }
    }

    // Jika bukan POST request, redirect ke member subtasks
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

    // Tambahkan method ini di SiteController.php

    public function actionMemberSubtasksBoard()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        // Get projects where current user is team lead
        $projects = Project::find()
            ->where(['team_lead_id' => Yii::$app->user->id])
            ->all();

        // Get all subtasks for these projects
        $subtasksByStatus = [
            'todo' => [],
            'in_progress' => [],
            'review' => [],
            'done' => []
        ];

        foreach ($projects as $project) {
            $boards = Board::find()->where(['project_id' => $project->project_id])->all();
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

        // Get available cards for creating new subtasks
        $availableCards = Card::find()
            ->joinWith(['board'])
            ->where(['boards.project_id' => array_map(function($p) { return $p->project_id; }, $projects)])
            ->andWhere(['cards.created_by' => Yii::$app->user->id])
            ->with(['board', 'board.project'])
            ->all();

        $subtaskModel = new Subtask();

        return $this->render('member/subtasks-board', [
            'subtasksByStatus' => $subtasksByStatus,
            'availableCards' => $availableCards,
            'subtaskModel' => $subtaskModel,
            'projects' => $projects
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

        // Get comments dengan permission check
        $comments = [];
        foreach ($subtask->comments as $comment) {
            $comments[] = [
                'comment_id' => $comment->comment_id,
                'user_name' => $comment->user ? $comment->user->full_name : 'Unknown',
                'comment_text' => $comment->comment_text,
                'created_at' => date('d M Y H:i', strtotime($comment->created_at)),
                'can_edit' => $comment->canEdit(),
                'can_delete' => $comment->canDelete()
            ];
        }

        // Get ALL help requests untuk subtask ini
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
                'can_delete' => $help->canDelete()
            ];
        }

        // Get running timer
        $runningTimer = TimeLog::find()
            ->where(['subtask_id' => $id, 'user_id' => Yii::$app->user->id, 'end_time' => null])
            ->one();

        // Project info for access checks in UI
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
                'project_name' => $project ? $project->project_name : 'Unknown Project'
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
                'start_timestamp' => strtotime($runningTimer->start_time) * 1000
            ] : null
        ];
    }

    // Comment Methods
    public function actionAddComment()
    {
        $request = Yii::$app->request;
        $acceptsJson = stripos($request->headers->get('Accept', ''), 'application/json') !== false;
        $isAjax = $request->isAjax || $acceptsJson;

        if (Yii::$app->user->isGuest) {
            if ($isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'Anda belum login. Silakan login terlebih dahulu.'];
            }
            return $this->redirect(['site/login']);
        }

        $model = new Comment();
        
        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            
            // Determine target project and check access for Admin or Team Lead only
            $project = null;
            $hasAccess = false;

            if ($model->comment_type === 'card') {
                if (!empty($model->card_id)) {
                    $card = Card::findOne($model->card_id);
                    if ($card) {
                        $hasAccess = $this->checkCardAccess($card);
                        if ($card->board) {
                            $project = Project::findOne($card->board->project_id);
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Card tidak ditemukan.');
                        $hasAccess = false; // Explicitly set false if card not found
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Card ID tidak boleh kosong untuk komentar tipe card.');
                    $hasAccess = false;
                }
            } elseif ($model->comment_type === 'subtask') {
                if (!empty($model->subtask_id)) {
                    $subtask = Subtask::findOne($model->subtask_id);
                    if ($subtask) {
                        $hasAccess = $this->checkSubtaskAccess($subtask);
                        if ($subtask->card && $subtask->card->board) {
                            $project = Project::findOne($subtask->card->board->project_id);
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Subtask tidak ditemukan.');
                        $hasAccess = false; // Explicitly set false if subtask not found
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Subtask ID tidak boleh kosong untuk komentar tipe subtask.');
                    $hasAccess = false;
                }
            } else {
                Yii::$app->session->setFlash('error', 'Tipe komentar tidak valid.');
                $hasAccess = false;
            }
            
            // Proceed only if access is granted and relevant IDs are present
            if ($hasAccess && $model->validate()) {
                if ($model->save()) {
                    if ($isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return ['success' => true, 'message' => 'Komentar berhasil ditambahkan'];
                    }
                    Yii::$app->session->setFlash('success', 'Komentar berhasil ditambahkan.');
                } else {
                    if ($isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return ['success' => false, 'message' => 'Gagal menambahkan komentar.'];
                    }
                    Yii::$app->session->setFlash('error', 'Gagal menambahkan komentar.');
                }
            } elseif (!$hasAccess && !Yii::$app->session->getFlash('error')) { // Avoid duplicate error messages
                if ($isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['success' => false, 'message' => 'Anda tidak memiliki akses untuk berkomentar pada item ini.'];
                }
                Yii::$app->session->setFlash('error', 'Anda tidak memiliki akses untuk berkomentar pada item ini.');
            }
        }
        
        if ($isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => false, 'message' => 'Permintaan tidak valid atau data komentar kosong.'];
        }

        // Determine the referrer to redirect back to the correct page
        $referrer = Yii::$app->request->referrer;
        if (!$referrer) {
            // Default redirect if referrer is not set
            if (Yii::$app->user->identity->role === 'admin') {
                $referrer = ['site/dashboard-admin'];
            } else {
                $referrer = ['site/dashboard-member'];
            }
        }
        return $this->redirect($referrer);
    }

    // Help Request Methods
   public function actionCreateHelpRequest()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
        return $this->redirect(['site/login']);
    }

    $model = new HelpRequest();
    
    if ($model->load(Yii::$app->request->post())) {
        $model->user_id = Yii::$app->user->id;
        // Jangan set status di sini, biarkan default dari model (pending)
        // $model->status = 'pending'; // HAPUS BARIS INI
        
        $subtask = Subtask::findOne($model->subtask_id);
        if (!$subtask || !$this->checkSubtaskAccess($subtask)) {
            Yii::$app->session->setFlash('error', 'Anda tidak memiliki akses ke subtask ini.');
            return $this->redirect(['site/dashboard-member']);
        }
        
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Permintaan bantuan berhasil dikirim dengan status: PENDING');
        } else {
            Yii::$app->session->setFlash('error', 'Gagal mengirim permintaan bantuan: ' . implode(', ', $model->getFirstErrors()));
        }
    }
    
    $referrer = Yii::$app->request->referrer ?: ['site/dashboard-member'];
    return $this->redirect($referrer);
}

    public function actionUpdateHelpRequest()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    try {
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        $requestId = Yii::$app->request->post('HelpRequest')['request_id'] ?? null;
        $newStatus = Yii::$app->request->post('HelpRequest')['status'] ?? null;
        $issueDesc = Yii::$app->request->post('HelpRequest')['issue_description'] ?? null;
        $resolutionNotes = Yii::$app->request->post('HelpRequest')['resolution_notes'] ?? null;
        
        Yii::info("UpdateHelpRequest started - Request: {$requestId}, New Status: {$newStatus}, User: " . Yii::$app->user->id);

        $model = HelpRequest::findOne($requestId);
        
        if (!$model) {
            Yii::error("Help request not found: {$requestId}");
            return ['success' => false, 'message' => 'Permintaan bantuan tidak ditemukan.'];
        }

        // Check access
        $subtask = $model->subtask;
        if (!$subtask) {
            Yii::error("Subtask not found for help request: {$requestId}");
            return ['success' => false, 'message' => 'Subtask tidak ditemukan.'];
        }

        $card = $subtask->card;
        if (!$card) {
            Yii::error("Card not found for subtask: {$subtask->subtask_id}");
            return ['success' => false, 'message' => 'Card tidak ditemukan.'];
        }

        $board = $card->board;
        if (!$board) {
            Yii::error("Board not found for card: {$card->card_id}");
            return ['success' => false, 'message' => 'Board tidak ditemukan.'];
        }

        $project = $board->project;
        if (!$project) {
            Yii::error("Project not found for board: {$board->board_id}");
            return ['success' => false, 'message' => 'Project tidak ditemukan.'];
        }

        $isAdmin = Yii::$app->user->identity->role === 'admin';
        $isTeamLead = $project->team_lead_id == Yii::$app->user->id;
        $isCreator = $model->user_id == Yii::$app->user->id;
        
        Yii::info("Access check - Admin: {$isAdmin}, TeamLead: {$isTeamLead}, Creator: {$isCreator}");

        if (!$isAdmin && !$isTeamLead && !$isCreator) {
            return ['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengupdate permintaan bantuan ini.'];
        }

        $didUpdate = false;

        // Handle optional content edits
        if ($issueDesc !== null) {
            if ($isCreator || $isAdmin || $isTeamLead) {
                if ($isCreator && in_array($model->status, ['fixed', 'completed'])) {
                    return ['success' => false, 'message' => 'Anda tidak dapat mengedit deskripsi setelah status fixed/completed.'];
                }
                $model->issue_description = $issueDesc;
                $didUpdate = true;
            } else {
                return ['success' => false, 'message' => 'Anda tidak diizinkan mengedit deskripsi masalah.'];
            }
        }

        if ($resolutionNotes !== null) {
            if ($isAdmin || $isTeamLead) {
                $model->resolution_notes = $resolutionNotes;
                $didUpdate = true;
            } else {
                return ['success' => false, 'message' => 'Hanya Team Lead/Admin yang dapat mengedit catatan penyelesaian.'];
            }
        }

        // Handle status update when provided
        if ($newStatus !== null) {
            $allowedStatuses = [];
            if ($isAdmin || $isTeamLead) {
                $allowedStatuses = ['pending', 'in_progress', 'fixed', 'completed'];
            } elseif ($isCreator) {
                if ($model->status === 'fixed') {
                    $allowedStatuses = ['completed'];
                } else {
                    $allowedStatuses = [$model->status];
                }
            }

            if (!in_array($newStatus, $allowedStatuses)) {
                Yii::error("Invalid status transition: {$model->status} -> {$newStatus} for user " . Yii::$app->user->id);
                return ['success' => false, 'message' => 'Status tidak valid atau tidak diizinkan untuk role Anda.'];
            }

            $oldStatus = $model->status;
            $model->status = $newStatus;

            if (in_array($newStatus, ['fixed', 'completed']) && !$model->resolved_by) {
                $model->resolved_by = Yii::$app->user->id;
                $model->resolved_at = date('Y-m-d H:i:s');
            }

            $didUpdate = true;
        }

        if (!$didUpdate) {
            return ['success' => false, 'message' => 'Tidak ada perubahan yang dikirimkan.'];
        }

        if ($model->save()) {
            Yii::info("Help request {$requestId} successfully updated");
            $msg = $newStatus ? ('Status berhasil diupdate menjadi: ' . $newStatus) : 'Help request berhasil diperbarui.';
            return ['success' => true, 'message' => $msg];
        } else {
            $errors = implode(', ', $model->getFirstErrors());
            Yii::error("Failed to save help request {$requestId}: {$errors}");
            return ['success' => false, 'message' => 'Gagal mengupdate: ' . $errors];
        }

    } catch (\Exception $e) {
        Yii::error("Exception in updateHelpRequest: " . $e->getMessage());
        Yii::error($e->getTraceAsString());
        return ['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
    }
}
    // Cards Methods (existing - keeping your code)
    public function actionMemberCards()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        // Get projects where current user is team lead
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
        
        return $this->render('member/cards', [
            'cards' => $cards,
            'cardModel' => $cardModel,
            'users' => $users
        ]);
    }

    public function actionCreateCard()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
        return $this->redirect(['site/login']);
    }

    $model = new Card();
    
    if ($model->load(Yii::$app->request->post())) {
        // Status otomatis todo
        $model->status = 'todo';
        $model->created_by = Yii::$app->user->id;
        $model->actual_hours = 0;

        // Get project_id from form and find the default board for that project
        $projectId = Yii::$app->request->post('Card')['project_id'];
        $project = Project::findOne($projectId);
        if (!$project) {
            Yii::$app->session->setFlash('error', 'Project not found.');
            return $this->redirect(['site/member-cards']);
        }
        // Only Team Lead can create cards; project must not be completed
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

        // Get assigned user ID (single user)
        $assignedUserId = Yii::$app->request->post('assigned_user_id');
        if (!$assignedUserId) {
            Yii::$app->session->setFlash('error', 'You must select one user as developer/designer.');
            return $this->redirect(['site/member-cards']);
        }

        // Check if user already has active assignment
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
            // Handle assigned user (single user)
            $assign = new CardAssignment();
            $assign->card_id = $model->card_id;
            $assign->user_id = (int)$assignedUserId;
            @$assign->save();

            // Mark project as active when first card created
            $project = Project::findOne($projectId);
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

    // Blokir update jika card sudah dikerjakan
    $worked = Subtask::find()->where(['card_id' => $model->card_id])->exists()
        || TimeLog::find()->where(['card_id' => $model->card_id])->exists()
        || ($model->status !== 'todo');
    if ($worked) {
        Yii::$app->session->setFlash('error', 'Card has been worked on and cannot be updated.');
        return $this->redirect(['site/member-cards']);
    }
    
    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        // Get assigned user ID (single user)
        $assignedUserId = Yii::$app->request->post('assigned_user_id');
        
        if ($assignedUserId) {
            // Check if user already has active assignment (excluding current card)
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

            // Update assigned user
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

// Update actionGetCardDetail di SiteController.php

// Update method actionGetCardDetail di SiteController.php

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
        
        // Check access
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
        
        // Get assigned user (first user only)
        $assignedUser = $card->assignedUsers ? $card->assignedUsers[0] : null;
        $assignedUserId = $assignedUser ? $assignedUser->user_id : null;
        $assignedUserName = $assignedUser ? $assignedUser->full_name : 'Not assigned';
        
        // Get project info from board
        $projectName = $card->board && $card->board->project ? $card->board->project->project_name : 'Unknown';
        $projectId = $card->board && $card->board->project ? $card->board->project->project_id : null;
        
        // Get all subtasks for this card
        $subtasks = Subtask::find()
            ->with(['creator'])
            ->where(['card_id' => $card->card_id])
            ->orderBy(['position' => SORT_ASC])
            ->all();
        
        // Calculate progress percentage
        $progress = $card->calculateProgress();
        
        // Get all comments for this card and its subtasks
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
        
        // Get all help requests for this card's subtasks
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

        // Project members for this card's project
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

                    // Ambil tanggal pertama user diassign card dalam project
                    $firstAssignDate = (new \yii\db\Query())
                        ->select(['min_created' => 'MIN(c.created_at)'])
                        ->from(['ca' => 'card_assignments'])
                        ->innerJoin(['c' => 'cards'], 'c.card_id = ca.card_id')
                        ->where(['ca.user_id' => $uid])
                        ->andWhere(['c.card_id' => $cardIds])
                        ->scalar();

                    // Ensure membership exists and persist joined_at jika project aktif
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
                        // Tampilkan joined_at dari created_at card pertama yang diassign
                        'joined_at' => $firstAssignDate ? date('d M Y H:i', strtotime($firstAssignDate)) : null,
                    ];
                }
            }

            // Include Team Lead for active projects
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
                            // Override joined_at untuk team lead dari created_at project
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
        
        // Format response data
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
                'project_members' => $projectMembers
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

        // Blokir delete jika card sudah dikerjakan
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

    // Get all active member users
    public function getActiveMembers() {
        return User::find()
            ->select(['user_id', 'full_name', 'username', 'email'])
            ->where([
                'is_active' => 1,
                'role' => 'member'
            ])
            ->orderBy('full_name')
            ->all();
    }

    // Board Methods
    // Board Methods untuk Member
public function actionMemberBoards()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
        return $this->redirect(['site/login']);
    }

    $userId = Yii::$app->user->id;

    // Tampilkan hanya task milik sendiri (dibuat oleh user)
    $cardsQuery = Card::find()
        ->with(['creator', 'assignedUsers', 'board.project'])
        ->where(['cards.created_by' => $userId])
        ->orderBy(['cards.created_at' => SORT_DESC]);

    $cards = $cardsQuery->all();
    $cardsByStatus = [
        'todo' => [],
        'in_progress' => [],
        'review' => [],
        'done' => []
    ];
    foreach ($cards as $card) {
        $status = $card->status ?? 'todo';
        if (isset($cardsByStatus[$status])) {
            $cardsByStatus[$status][] = $card;
        }
    }

    // Subtasks milik sendiri (dibuat oleh user)
    $subtasks = Subtask::find()->distinct(true)
        ->with(['card.board.project'])
        ->where(['subtasks.created_by' => $userId])
        ->orderBy([
            new \yii\db\Expression('CASE status WHEN "todo" THEN 0 WHEN "in_progress" THEN 1 WHEN "review" THEN 2 ELSE 3 END'),
            'position' => SORT_ASC,
            'created_at' => SORT_DESC
        ])
        ->all();

    $subtasksByStatus = [
        'todo' => [],
        'in_progress' => [],
        'review' => [],
        'done' => []
    ];
    $seenIds = [];
    foreach ($subtasks as $st) {
        if (in_array($st->subtask_id, $seenIds, true)) { continue; }
        $seenIds[] = $st->subtask_id;
        $status = $st->status ?? 'todo';
        if (isset($subtasksByStatus[$status])) {
            $subtasksByStatus[$status][] = $st;
        }
    }

    return $this->render('member/boards', [
        'cardsByStatus' => $cardsByStatus,
        'subtasksByStatus' => $subtasksByStatus
    ]);
}

// Board Methods untuk Admin
public function actionAdminBoards()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
        return $this->redirect(['site/login']);
    }

    // Group projects by status untuk kanban view
    $projectsByStatus = [
        'planning' => [],
        'active' => [],
        'completed' => [],
        'cancelled' => [],
    ];

    $projects = Project::find()
        ->with(['teamLead', 'boards.cards'])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    foreach ($projects as $project) {
        $status = $project->status ?: 'planning';
        if (array_key_exists($status, $projectsByStatus)) {
            $projectsByStatus[$status][] = $project;
        } else {
            $projectsByStatus['planning'][] = $project;
        }
    }

    return $this->render('admin/boards', [
        'projectsByStatus' => $projectsByStatus,
    ]);
}

    public function actionUpdateProjectStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request method.'];
        }

        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return ['success' => false, 'message' => 'Access denied.'];
        }

        $projectId = Yii::$app->request->post('project_id');
        $newStatus = Yii::$app->request->post('status');

        $project = Project::findOne($projectId);

        if (!$project) {
            return ['success' => false, 'message' => 'Project not found.'];
        }

        $allowedStatuses = ['planning', 'active', 'completed', 'cancelled'];
        if (!in_array($newStatus, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Invalid status provided.'];
        }

        $project->status = $newStatus;

        if ($project->save()) {
            if ($newStatus === 'completed') {
                 if ($project->progress_percentage < 100) {
                     $project->progress_percentage = 100;
                     $project->save(false);
                 }
                 // Revoke Team Lead and developer/designer assignments when project is completed
                 $project->team_lead_id = null;
                 $project->save(false, ['team_lead_id']);
                 $boardIds = Board::find()->where(['project_id' => $project->project_id])->select('board_id')->column();
                 $cardIds = Card::find()->where(['board_id' => $boardIds])->select('card_id')->column();
                 if (!empty($cardIds)) {
                     CardAssignment::deleteAll(['card_id' => $cardIds]);
                 }
            }
            
            return ['success' => true, 'message' => 'Project status updated successfully.'];
        } else {
            return ['success' => false, 'message' => 'Failed to update project status.', 'errors' => $project->getErrors()];
        }
    }

    // User Management
    public function actionAdminUsers()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $users = User::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $userModel = new User();

        return $this->render('admin/users', [
            'users' => $users,
            'userModel' => $userModel
        ]);
    }

    public function actionCreateUser()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $model = new User();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = date('Y-m-d H:i:s');
            $model->is_active = 1;

            if (!empty($model->password)) {
                $model->setPassword($model->password);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'User berhasil ditambahkan.');
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menambahkan user.');
            }

            return $this->redirect(['site/admin-users']);
        }

        return $this->redirect(['site/admin-users']);
    }

    public function actionUpdateUser()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $id = Yii::$app->request->post('User')['user_id'];
        $model = User::findOne($id);

        if (!$model) {
            Yii::$app->session->setFlash('error', 'User tidak ditemukan.');
            return $this->redirect(['site/admin-users']);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->password)) {
                $model->setPassword($model->password);
            } else {
                $model->password = $model->getOldAttribute('password');
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'User berhasil diupdate.');
            } else {
                Yii::$app->session->setFlash('error', 'Gagal mengupdate user.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Gagal mengupdate user.');
        }

        return $this->redirect(['site/admin-users']);
    }

    public function actionDeleteUser($id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $model = User::findOne($id);

        if ($model) {
            if ($model->user_id == Yii::$app->user->id) {
                Yii::$app->session->setFlash('error', 'Tidak dapat menghapus user yang sedang login.');
                return $this->redirect(['site/admin-users']);
            }

            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'User berhasil dihapus.');
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menghapus user.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'User tidak ditemukan.');
        }

        return $this->redirect(['site/admin-users']);
    }

    public function actionGetUserDetail($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = User::findOne($id);

        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan'];
        }

        return [
            'success' => true,
            'user' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
                'profile_picture' => $user->profile_picture
            ]
        ];
    }

    public function actionUpdateMyProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        $user = User::findOne(Yii::$app->user->id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        $post = Yii::$app->request->post('User', []);
        if (!empty($post['username'])) {
            $user->username = $post['username'];
        }
        if (!empty($post['email'])) {
            $user->email = $post['email'];
        }
        if (!empty($post['password'])) {
            $user->setPassword($post['password']);
        }
        if ($user->save()) {
            return [
                'success' => true,
                'user' => [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                ]
            ];
        }
        return ['success' => false, 'message' => 'Failed to update profile'];
    }

    // Reports Methods
    public function actionMemberReports()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
        return $this->redirect(['site/login']);
    }

    $userId = Yii::$app->user->id;
    
    // Get projects where user is team lead - only get project IDs and names for productivity data
    $projectIds = Project::find()
        ->select('project_id')
        ->where(['team_lead_id' => $userId])
        ->orWhere(['created_by' => $userId])
        ->column();
    
    $productivityData = [];
    
    // Optimize: Use SQL aggregation to calculate productivity data per project
    // This replaces the nested loops and N+1 queries
    if (!empty($projectIds)) {
        // Get project names
        $projectNames = Project::find()
            ->select(['project_id', 'project_name'])
            ->where(['project_id' => $projectIds])
            ->indexBy('project_id')
            ->asArray()
            ->all();
        
        // Get completed subtasks count per project using aggregation
        // Use subquery for better performance and accuracy
        $completedSubtasksData = (new \yii\db\Query())
            ->select([
                'p.project_id',
                'COUNT(DISTINCT s.subtask_id) as completed_subtasks'
            ])
            ->from('projects p')
            ->leftJoin('boards b', 'b.project_id = p.project_id')
            ->leftJoin('cards c', 'c.board_id = b.board_id')
            ->leftJoin('subtasks s', 's.card_id = c.card_id AND s.status = "done"')
            ->where(['p.project_id' => $projectIds])
            ->groupBy('p.project_id')
            ->indexBy('project_id')
            ->all();
        
        // Get total working hours per project from time_logs using aggregation
        // Join through subtasks -> cards -> boards -> projects
        // Use leftJoin to include projects even if they have no time logs
        $workingHoursData = (new \yii\db\Query())
            ->select([
                'p.project_id',
                'COALESCE(SUM(tl.duration_minutes), 0) as total_minutes'
            ])
            ->from('projects p')
            ->leftJoin('boards b', 'b.project_id = p.project_id')
            ->leftJoin('cards c', 'c.board_id = b.board_id')
            ->leftJoin('subtasks s', 's.card_id = c.card_id')
            ->leftJoin('time_logs tl', 'tl.subtask_id = s.subtask_id AND tl.duration_minutes IS NOT NULL')
            ->where(['p.project_id' => $projectIds])
            ->groupBy('p.project_id')
            ->indexBy('project_id')
            ->all();
        
        // Build productivity data array
        foreach ($projectIds as $projectId) {
            $projectName = $projectNames[$projectId]['project_name'] ?? 'Unknown Project';
            $completedSubtasks = isset($completedSubtasksData[$projectId]) 
                ? (int)$completedSubtasksData[$projectId]['completed_subtasks'] 
                : 0;
            $totalMinutes = isset($workingHoursData[$projectId]) 
                ? (int)$workingHoursData[$projectId]['total_minutes'] 
                : 0;
            $totalHours = round($totalMinutes / 60, 2);
            
            $productivityData[] = [
                'project_id' => $projectId,
                'project_name' => $projectName,
                'total_hours' => $totalHours,
                'completed_subtasks' => $completedSubtasks
            ];
        }
        
        // Sort by total hours descending
        usort($productivityData, function($a, $b) {
            return $b['total_hours'] <=> $a['total_hours'];
        });
    }
    
    $cardsCreatedCount = Card::find()
        ->where(['created_by' => $userId])
        ->count();

    $subtasksCreatedCount = Subtask::find()
        ->where(['created_by' => $userId])
        ->count();

    $cardsCompletedCount = Card::find()
        ->where(['created_by' => $userId, 'status' => 'done'])
        ->count();

    $subtasksCompletedCount = Subtask::find()
        ->where(['created_by' => $userId, 'status' => 'done'])
        ->count();

    $subtaskAcceptedCount = \app\models\SubtaskSubmission::find()
        ->where(['submitted_by' => $userId, 'status' => 'accepted'])
        ->count();

    $subtaskRejectedCount = \app\models\SubtaskSubmission::find()
        ->where(['submitted_by' => $userId, 'status' => 'rejected'])
        ->count();

    $cardAcceptedCount = \app\models\CardSubmission::find()
        ->where(['submitted_by' => $userId, 'status' => 'accepted'])
        ->count();

    $cardRejectedCount = \app\models\CardSubmission::find()
        ->where(['submitted_by' => $userId, 'status' => 'rejected'])
        ->count();

    $memberCounts = [
        'cards_created' => (int)$cardsCreatedCount,
        'subtasks_created' => (int)$subtasksCreatedCount,
        'cards_completed' => (int)$cardsCompletedCount,
        'subtasks_completed' => (int)$subtasksCompletedCount,
    ];

    $submissionCounts = [
        'subtask_accepted' => (int)$subtaskAcceptedCount,
        'subtask_rejected' => (int)$subtaskRejectedCount,
        'card_accepted' => (int)$cardAcceptedCount,
        'card_rejected' => (int)$cardRejectedCount,
    ];

    // Get all time tracking data grouped by date (using created_at)
    $rawDailyAll = (new \yii\db\Query())
        ->select([
            'date' => new \yii\db\Expression('DATE(created_at)'),
            'minutes' => new \yii\db\Expression('COALESCE(SUM(duration_minutes), 0)')
        ])
        ->from('time_tracking')
        ->where(['user_id' => $userId])
        ->andWhere(['NOT', ['duration_minutes' => null]])
        ->groupBy(new \yii\db\Expression('DATE(created_at)'))
        ->orderBy(['date' => SORT_DESC])
        ->all();

    // Store all data with date as key
    $dailyWorkingAll = [];
    foreach ($rawDailyAll as $row) {
        $dateKey = $row['date'];
        $dailyWorkingAll[$dateKey] = (int)($row['minutes'] ?? 0);
    }

    // Get last 7 days (most recent) - already sorted DESC from query
    $dailyWorking = [];
    $count = 0;
    foreach ($dailyWorkingAll as $date => $minutes) {
        if ($count < 7) {
            $dailyWorking[$date] = $minutes;
            $count++;
        } else {
            break;
        }
    }

    // Get detailed task lists for the report
    // Tasks created (subtasks and cards)
    $subtasksCreated = Subtask::find()
        ->where(['created_by' => $userId])
        ->with(['card.board.project'])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(10)
        ->all();
    
    $cardsCreated = Card::find()
        ->where(['created_by' => $userId])
        ->with(['board.project'])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    // Tasks completed (subtasks and cards with status 'done')
    $subtasksCompleted = Subtask::find()
        ->where(['created_by' => $userId, 'status' => 'done'])
        ->with(['card.board.project'])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(10)
        ->all();
    
    $cardsCompleted = Card::find()
        ->where(['created_by' => $userId, 'status' => 'done'])
        ->with(['board.project'])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    // Tasks accepted (subtasks and cards with accepted submissions)
    $subtasksAccepted = Subtask::find()
        ->innerJoin('subtask_submissions ss', 'ss.subtask_id = subtasks.subtask_id')
        ->where(['ss.submitted_by' => $userId, 'ss.status' => 'accepted'])
        ->with(['card.board.project'])
        ->orderBy(['ss.reviewed_at' => SORT_DESC])
        ->limit(10)
        ->all();
    
    $cardsAccepted = Card::find()
        ->innerJoin('card_submissions cs', 'cs.card_id = cards.card_id')
        ->where(['cs.submitted_by' => $userId, 'cs.status' => 'accepted'])
        ->with(['board.project'])
        ->orderBy(['cs.reviewed_at' => SORT_DESC])
        ->limit(10)
        ->all();

    // Tasks rejected (subtasks and cards with rejected submissions)
    $subtasksRejected = Subtask::find()
        ->innerJoin('subtask_submissions ss', 'ss.subtask_id = subtasks.subtask_id')
        ->where(['ss.submitted_by' => $userId, 'ss.status' => 'rejected'])
        ->with(['card.board.project'])
        ->orderBy(['ss.reviewed_at' => SORT_DESC])
        ->limit(10)
        ->all();
    
    $cardsRejected = Card::find()
        ->innerJoin('card_submissions cs', 'cs.card_id = cards.card_id')
        ->where(['cs.submitted_by' => $userId, 'cs.status' => 'rejected'])
        ->with(['board.project'])
        ->orderBy(['cs.reviewed_at' => SORT_DESC])
        ->limit(10)
        ->all();

    // Datasets untuk layout fungsi Team Lead dan Developer/Designer
    $eligibleSubtasks = \app\models\Subtask::find()
        ->alias('s')
        ->joinWith(['card c'])
        ->leftJoin('card_assignments ca', 'ca.card_id = s.card_id')
        ->where(['or', ['s.created_by' => $userId], ['ca.user_id' => $userId]])
        ->andWhere(['!=', 's.status', 'done'])
        ->orderBy(['s.created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    $eligibleCards = \app\models\Card::find()
        ->alias('c')
        ->joinWith(['board b', 'board.project p'])
        ->where(['p.team_lead_id' => $userId, 'c.status' => 'done'])
        ->orderBy(['c.created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    $mySubmissions = \app\models\SubtaskSubmission::find()
        ->where(['submitted_by' => $userId])
        ->with(['subtask', 'subtask.card', 'reviewer'])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    $myCardSubmissions = \app\models\CardSubmission::find()
        ->where(['submitted_by' => $userId])
        ->with(['card', 'card.board', 'reviewer'])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    $pendingSubtaskSubmissions = \app\models\SubtaskSubmission::find()
        ->where(['reviewer_id' => $userId, 'status' => 'pending'])
        ->with(['subtask', 'subtask.card', 'submitter'])
        ->orderBy(['created_at' => SORT_ASC])
        ->limit(10)
        ->all();

    $reviewedSubtaskSubmissions = \app\models\SubtaskSubmission::find()
        ->where(['reviewer_id' => $userId])
        ->andWhere(['!=', 'status', 'pending'])
        ->with(['subtask', 'subtask.card', 'submitter'])
        ->orderBy(['reviewed_at' => SORT_DESC, 'created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    return $this->render('member/reports', [
        'memberCounts' => $memberCounts,
        'submissionCounts' => $submissionCounts,
        'dailyWorking' => $dailyWorking,
        'dailyWorkingAll' => $dailyWorkingAll,
        'subtasksCreated' => $subtasksCreated,
        'cardsCreated' => $cardsCreated,
        'subtasksCompleted' => $subtasksCompleted,
        'cardsCompleted' => $cardsCompleted,
        'subtasksAccepted' => $subtasksAccepted,
        'cardsAccepted' => $cardsAccepted,
        'subtasksRejected' => $subtasksRejected,
        'cardsRejected' => $cardsRejected,
        'eligibleSubtasks' => $eligibleSubtasks,
        'eligibleCards' => $eligibleCards,
        'mySubmissions' => $mySubmissions,
        'myCardSubmissions' => $myCardSubmissions,
        'pendingSubtaskSubmissions' => $pendingSubtaskSubmissions,
        'reviewedSubtaskSubmissions' => $reviewedSubtaskSubmissions,
    ]);
}

    public function actionReportsAdmin()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
        return $this->redirect(['site/login']);
    }

    $projects = Project::find()->with(['creator','teamLead'])->all();
    $projectData = [];
    
    foreach ($projects as $project) {
        // Get boards for this project
        $boards = Board::find()->where(['project_id' => $project->project_id])->all();
        $cardData = [];
        $totalHours = 0;
        $cardCount = 0;
        $subtaskCount = 0;
        
        foreach ($boards as $board) {
            // Get cards for this board
            $cards = Card::find()->where(['board_id' => $board->board_id])->all();
            
            foreach ($cards as $card) {
                $cardCount++;
                
                // Get subtasks for this card
                $subtasks = Subtask::find()->where(['card_id' => $card->card_id])->all();
                $subtaskCount += count($subtasks);
                
                // Calculate time logs for this card
                $timeLogs = TimeLog::find()->where(['card_id' => $card->card_id])->all();
                $cardHours = 0;
                foreach ($timeLogs as $log) {
                    if ($log->duration_minutes) {
                        $cardHours += $log->duration_minutes / 60;
                    }
                }
                $totalHours += $cardHours;
                
                // Normalize card and subtask data to arrays for JSON encoding
                $cardItem = [
                    'card_id' => $card->card_id,
                    'board_id' => $card->board_id,
                    'card_title' => $card->card_title,
                    'description' => $card->description,
                    'created_at' => $card->created_at,
                    'due_date' => $card->due_date,
                    'status' => $card->status,
                    'priority' => $card->priority,
                    'estimated_hours' => (float)($card->estimated_hours ?? 0),
                    'actual_hours' => (float)($card->actual_hours ?? 0),
                    'assigned_role' => $card->assigned_role,
                ];

                $subsArr = [];
                foreach ($subtasks as $subtask) {
                    // Get blockers (help requests with pending or in_progress status)
                    $blockers = HelpRequest::find()
                        ->where(['subtask_id' => $subtask->subtask_id])
                        ->andWhere(['in', 'status', ['pending', 'in_progress']])
                        ->with(['creator'])
                        ->all();
                    
                    $blockersArr = [];
                    foreach ($blockers as $blocker) {
                        $blockersArr[] = [
                            'request_id' => $blocker->request_id,
                            'issue_description' => $blocker->issue_description,
                            'status' => $blocker->status,
                            'created_at' => $blocker->created_at,
                            'creator_name' => $blocker->creator ? $blocker->creator->full_name : 'Unknown',
                        ];
                    }
                    $helpRequests = HelpRequest::find()
                        ->where(['subtask_id' => $subtask->subtask_id])
                        ->with(['creator', 'resolver'])
                        ->orderBy(['created_at' => SORT_ASC])
                        ->all();
                    $helpRequestsArr = [];
                    foreach ($helpRequests as $help) {
                        $helpRequestsArr[] = [
                            'request_id' => $help->request_id,
                            'issue_description' => $help->issue_description,
                            'status' => $help->status,
                            'created_at' => $help->created_at,
                            'creator_name' => $help->creator ? $help->creator->full_name : 'Unknown',
                            'resolved_at' => $help->resolved_at,
                            'resolved_by_name' => $help->resolver ? $help->resolver->full_name : null,
                            'resolution_notes' => $help->resolution_notes,
                        ];
                    }
                    
                    $subsArr[] = [
                        'subtask_id' => $subtask->subtask_id,
                        'subtask_title' => $subtask->subtask_title,
                        'status' => $subtask->status,
                        'estimated_hours' => (float)($subtask->estimated_hours ?? 0),
                        'actual_hours' => (float)($subtask->actual_hours ?? 0),
                        'created_at' => $subtask->created_at,
                        'blockers' => $blockersArr,
                        'help_requests' => $helpRequestsArr,
                    ];
                }

                $cardData[] = [
                    'card' => $cardItem,
                    'subtasks' => $subsArr
                ];
            }
        }
        
        // Normalize project to array with nested creator and teamLead names
        $projectArr = [
            'project_id' => $project->project_id,
            'project_name' => $project->project_name,
            'description' => $project->description,
            'created_by' => $project->created_by,
            'team_lead_id' => $project->team_lead_id,
            'difficulty_level' => $project->difficulty_level,
            'status' => $project->status,
            'progress_percentage' => (float)($project->progress_percentage ?? 0),
            'created_at' => $project->created_at,
            'deadline' => $project->deadline,
            'updated_at' => $project->updated_at ?? null,
            'creator' => [
                'full_name' => $project->creator ? $project->creator->full_name : null,
            ],
            'createdBy' => [
                'full_name' => $project->creator ? $project->creator->full_name : null,
            ],
            'teamLead' => [
                'full_name' => $project->teamLead ? $project->teamLead->full_name : null,
            ],
        ];

        $projectData[] = [
            'project' => $projectArr,
            'cards' => $cardData,
            'total_hours' => round($totalHours, 2),
            'card_count' => $cardCount,
            'subtask_count' => $subtaskCount
        ];
    }

    // Get member productivity data
    $members = User::find()->where(['role' => 'member'])->all();
    $productivityData = [];
    
    foreach ($members as $member) {
        $cardsCreated = Card::find()->where(['created_by' => $member->user_id])->count();
        $subtasksCreated = Subtask::find()->where(['created_by' => $member->user_id])->count();
        $subtasksCompleted = Subtask::find()->where(['created_by' => $member->user_id, 'status' => 'done'])->count();
        
        $totalMinutes = TimeLog::find()
            ->where(['user_id' => $member->user_id])
            ->andWhere(['NOT', ['duration_minutes' => null]])
            ->sum('duration_minutes');
        
        $workingTime = TimeTracking::find()
            ->where(['user_id' => $member->user_id])
            ->sum('duration_minutes');
        
        $productivityData[] = [
            'user_id' => $member->user_id,
            'user_name' => $member->full_name,
            'cards_created' => $cardsCreated,
            'subtasks_created' => $subtasksCreated,
            'subtasks_completed' => $subtasksCompleted,
            'actual_hours' => round(($totalMinutes ?? 0) / 60, 2),
            'working_hours' => round(($workingTime ?? 0) / 60, 2)
        ];
    }
    
    usort($productivityData, function($a, $b) {
        return $b['actual_hours'] <=> $a['actual_hours'];
    });

    return $this->render('admin/reports', [
        'projectData' => $projectData,
        'productivityData' => $productivityData
    ]);
}


    /**
     * Halaman pengumpulan untuk member: ajukan subtask ke team lead.
     */
public function actionSubmissionsMember()
{
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }
    $user = Yii::$app->user->identity;

    // Ambil subtasks yang dibuat oleh user atau user ditugaskan pada card-nya
    $eligibleSubtasks = \app\models\Subtask::find()
        ->alias('s')
        ->joinWith(['card c'])
        ->leftJoin('card_assignments ca', 'ca.card_id = s.card_id')
        ->where(['or', ['s.created_by' => $user->user_id], ['ca.user_id' => $user->user_id]])
        ->andWhere(['!=', 's.status', 'done']) // Hanya subtask yang belum selesai
        ->orderBy(['s.created_at' => SORT_DESC])
        ->all();

    $eligibleProjects = [];
    $projectsLead = \app\models\Project::find()
        ->where(['team_lead_id' => $user->user_id])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();
    foreach ($projectsLead as $proj) {
        $cardsInProject = \app\models\Card::find()->joinWith('board')
            ->where(['boards.project_id' => $proj->project_id])
            ->all();
        if (empty($cardsInProject)) { continue; }
        $allDone = true;
        foreach ($cardsInProject as $card) {
            $hasSubtasks = \app\models\Subtask::find()->where(['card_id' => $card->card_id])->exists();
            if (!$hasSubtasks) { $allDone = false; break; }
            $hasUndone = \app\models\Subtask::find()->where(['card_id' => $card->card_id])
                ->andWhere(['!=','status','done'])->exists();
            if ($hasUndone) { $allDone = false; break; }
        }
        if ($allDone) { $eligibleProjects[] = $proj; }
    }

    // Handle submit subtask
    if (Yii::$app->request->isPost && Yii::$app->request->post('subtask_id')) {
        $subtaskId = (int)Yii::$app->request->post('subtask_id');
        $notes = (string)Yii::$app->request->post('submission_notes');
        $subtask = \app\models\Subtask::findOne($subtaskId);
        if ($subtask && $subtask->card && $subtask->card->board && $subtask->card->board->project) {
            $project = $subtask->card->board->project;
            $submission = new \app\models\SubtaskSubmission();
            $submission->subtask_id = $subtask->subtask_id;
            $submission->submitted_by = $user->user_id;
            $submission->reviewer_id = (int)$project->team_lead_id;
            $submission->status = 'pending';
            $submission->submission_notes = $notes;
            if ($submission->save()) {
                Yii::$app->session->setFlash('success', 'Subtask berhasil diajukan ke Team Lead.');
                return $this->redirect(['site/submissions-member']);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal mengajukan subtask.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Subtask tidak valid atau proyek tidak ditemukan.');
        }
    }

    // Handle submit card (team lead -> admin)
    if (Yii::$app->request->isPost && Yii::$app->request->post('submit_card_id')) {
        $cardId = (int)Yii::$app->request->post('submit_card_id');
        $notes = (string)Yii::$app->request->post('submission_notes_card');
        $card = \app\models\Card::findOne($cardId);
        if ($card) {
            $admin = \app\models\User::find()->where(['role' => 'admin'])->one();
            if (!$admin) {
                Yii::$app->session->setFlash('error', 'Admin tidak ditemukan.');
            } else {
                $submission = new \app\models\CardSubmission();
                $submission->card_id = $cardId;
                $submission->submitted_by = $user->user_id;
                $submission->reviewer_id = $admin->user_id;
                $submission->status = 'pending';
                $submission->submission_notes = $notes;
                if ($submission->save()) {
                    Yii::$app->session->setFlash('success', 'Card berhasil diajukan ke Admin.');
                    return $this->redirect(['site/submissions-member']);
                } else {
                    Yii::$app->session->setFlash('error', 'Gagal mengajukan card.');
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'Card tidak valid.');
        }
    }

    if (Yii::$app->request->isPost && Yii::$app->request->post('submit_project_id')) {
        $projectId = (int)Yii::$app->request->post('submit_project_id');
        $notes = (string)Yii::$app->request->post('submission_notes_project');
        $project = \app\models\Project::findOne($projectId);
        if ($project && (int)$project->team_lead_id === (int)$user->user_id) {
            $cardsInProject = \app\models\Card::find()->joinWith('board')
                ->where(['boards.project_id' => $projectId])
                ->all();
            if (empty($cardsInProject)) {
                Yii::$app->session->setFlash('error', 'Project tidak memiliki card untuk diajukan.');
                return $this->redirect(['site/submissions-member']);
            }
            $allDone = true;
            foreach ($cardsInProject as $card) {
                $hasSubtasks = \app\models\Subtask::find()->where(['card_id' => $card->card_id])->exists();
                if (!$hasSubtasks) { $allDone = false; break; }
                $hasUndone = \app\models\Subtask::find()->where(['card_id' => $card->card_id])
                    ->andWhere(['!=','status','done'])->exists();
                if ($hasUndone) { $allDone = false; break; }
            }
            if (!$allDone) {
                Yii::$app->session->setFlash('error', 'Masih ada subtask yang belum selesai di project ini.');
                return $this->redirect(['site/submissions-member']);
            }
            $admin = \app\models\User::find()->where(['role' => 'admin'])->one();
            if (!$admin) {
                Yii::$app->session->setFlash('error', 'Admin tidak ditemukan.');
                return $this->redirect(['site/submissions-member']);
            }
            $created = 0;
            foreach ($cardsInProject as $card) {
                $exists = \app\models\CardSubmission::find()
                    ->where(['card_id' => $card->card_id, 'status' => 'pending'])
                    ->exists();
                if ($exists) { continue; }
                $submission = new \app\models\CardSubmission();
                $submission->card_id = $card->card_id;
                $submission->submitted_by = $user->user_id;
                $submission->reviewer_id = $admin->user_id;
                $submission->status = 'pending';
                $submission->submission_notes = 'Pengajuan Project: ' . ($project->project_name ?? '-') . ($notes ? (' — ' . $notes) : '');
                if ($submission->save()) { $created++; }
            }
            if ($created > 0) {
                Yii::$app->session->setFlash('success', 'Project berhasil diajukan ke Admin.');
            } else {
                Yii::$app->session->setFlash('warning', 'Tidak ada card yang diajukan (mungkin sudah pending).');
            }
            return $this->redirect(['site/submissions-member']);
        } else {
            Yii::$app->session->setFlash('error', 'Project tidak valid atau bukan milik Anda.');
            return $this->redirect(['site/submissions-member']);
        }
    }

    // Handle approval subtask oleh Team Lead (accept / reject)
    if (Yii::$app->request->isPost && Yii::$app->request->post('review_submission_id')) {
        $reviewId = (int)Yii::$app->request->post('review_submission_id');
        $reviewAction = (string)Yii::$app->request->post('review_action'); // 'accept' atau 'reject'
        $reviewNotes = (string)Yii::$app->request->post('review_notes');
        $submission = \app\models\SubtaskSubmission::findOne($reviewId);
        if ($submission && (int)$submission->reviewer_id === (int)$user->user_id && $submission->status === 'pending') {
            if ($reviewAction === 'accept') {
                $submission->status = 'accepted';
            } else {
                $submission->status = 'rejected';
            }
            $submission->review_notes = $reviewNotes;
            $submission->reviewed_at = new \yii\db\Expression('NOW()');
            if ($submission->save()) {
                // Opsional: update status subtask jika diterima
                if ($reviewAction === 'accept' && $submission->subtask) {
                    $submission->subtask->status = 'done';
                    $submission->subtask->save(false, ['status']);
                }
                Yii::$app->session->setFlash('success', $reviewAction === 'accept' ? 'Subtask diterima.' : 'Subtask ditolak.');
                return $this->redirect(['site/submissions-member']);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menyimpan hasil review subtask.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Pengajuan tidak valid atau bukan milik Anda untuk direview.');
        }
    }

    // Get user's own submissions
    $mySubmissions = \app\models\SubtaskSubmission::find()
        ->where(['submitted_by' => $user->user_id])
        ->with(['subtask', 'subtask.card', 'reviewer'])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    // Get user's own card submissions
    $myCardSubmissions = \app\models\CardSubmission::find()
        ->where(['submitted_by' => $user->user_id])
        ->with(['card', 'card.board', 'reviewer', 'card.board.project'])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    // Group project submission history for member
    $myProjectSubmissionGroups = [];
    foreach ($myCardSubmissions as $cs) {
        $proj = ($cs->card && $cs->card->board && $cs->card->board->project) ? $cs->card->board->project : null;
        if (!$proj) { continue; }
        $pid = (int)$proj->project_id;
        if (!isset($myProjectSubmissionGroups[$pid])) {
            $myProjectSubmissionGroups[$pid] = [
                'project' => $proj,
                'counts' => ['pending' => 0, 'accepted' => 0, 'rejected' => 0],
                'last_submission_at' => $cs->created_at,
                'last_reviewed_at' => $cs->reviewed_at,
                'status' => null,
            ];
        }
        if (isset($myProjectSubmissionGroups[$pid]['counts'][$cs->status])) {
            $myProjectSubmissionGroups[$pid]['counts'][$cs->status] += 1;
        }
        if (strtotime($cs->created_at) > strtotime($myProjectSubmissionGroups[$pid]['last_submission_at'])) {
            $myProjectSubmissionGroups[$pid]['last_submission_at'] = $cs->created_at;
        }
        if ($cs->reviewed_at && (!$myProjectSubmissionGroups[$pid]['last_reviewed_at'] || strtotime($cs->reviewed_at) > strtotime($myProjectSubmissionGroups[$pid]['last_reviewed_at']))) {
            $myProjectSubmissionGroups[$pid]['last_reviewed_at'] = $cs->reviewed_at;
        }
    }
    foreach ($myProjectSubmissionGroups as &$grp) {
        $counts = $grp['counts'];
        if ($counts['pending'] > 0) {
            $grp['status'] = 'pending';
        } else {
            if ($grp['project']->status === 'completed') {
                $grp['status'] = 'accepted';
            } elseif ($counts['accepted'] === 0 && $counts['rejected'] > 0) {
                $grp['status'] = 'rejected';
            } else {
                $grp['status'] = 'accepted';
            }
        }
    }
    unset($grp);

    // Subtask submissions yang harus direview oleh user (jika user adalah team lead di proyek terkait)
    $pendingSubtaskSubmissions = \app\models\SubtaskSubmission::find()
        ->where(['reviewer_id' => $user->user_id, 'status' => 'pending'])
        ->with(['subtask', 'subtask.card', 'submitter'])
        ->orderBy(['created_at' => SORT_ASC])
        ->all();

    // Riwayat peninjauan subtask yang sudah direview oleh user ini (sebagai reviewer)
    $reviewedSubtaskSubmissions = \app\models\SubtaskSubmission::find()
        ->where(['reviewer_id' => $user->user_id])
        ->andWhere(['!=', 'status', 'pending']) // Hanya yang sudah direview (bukan pending)
        ->with(['subtask', 'subtask.card', 'submitter'])
        ->orderBy(['reviewed_at' => SORT_DESC, 'created_at' => SORT_DESC])
        ->all();

    return $this->render('member/submissions', [
        'eligibleSubtasks' => $eligibleSubtasks,
        'eligibleProjects' => $eligibleProjects,
        'mySubmissions' => $mySubmissions,
        'myCardSubmissions' => $myCardSubmissions,
        'myProjectSubmissionGroups' => $myProjectSubmissionGroups,
        'pendingSubtaskSubmissions' => $pendingSubtaskSubmissions,
        'reviewedSubtaskSubmissions' => $reviewedSubtaskSubmissions,
    ]);
}

    /**
     * Halaman review pengumpulan subtask untuk Team Lead.
     */
    public function actionSubmissionsTeamLead()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        $user = Yii::$app->user->identity;

        // Pending submissions untuk direview
        $pending = \app\models\SubtaskSubmission::find()
            ->where(['reviewer_id' => $user->user_id, 'status' => 'pending'])
            ->with(['subtask', 'submitter'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        // Handle accept/reject
        if (Yii::$app->request->isPost) {
            $submissionId = (int)Yii::$app->request->post('submission_id');
            $decision = (string)Yii::$app->request->post('decision'); // 'accept' atau 'reject'
            $reviewNotes = (string)Yii::$app->request->post('review_notes');
            $submission = \app\models\SubtaskSubmission::findOne($submissionId);
            if ($submission && (int)$submission->reviewer_id === (int)$user->user_id) {
                if ($decision === 'accept') {
                    $submission->status = 'accepted';
                    $submission->review_notes = $reviewNotes;
                    $submission->reviewed_at = date('Y-m-d H:i:s');
                    $submission->save(false);
                    // Update status subtask ke done
                    $subtask = $submission->subtask;
                    if ($subtask) {
                        $subtask->status = 'done';
                        $subtask->save(false, ['status']);
                    }
                    Yii::$app->session->setFlash('success', 'Subtask diterima dan ditandai selesai.');
                } elseif ($decision === 'reject') {
                    $submission->status = 'rejected';
                    $submission->review_notes = $reviewNotes;
                    $submission->reviewed_at = date('Y-m-d H:i:s');
                    $submission->save(false);
                    Yii::$app->session->setFlash('warning', 'Subtask ditolak. Pengembang dapat mengajukan kembali.');
                }
                return $this->redirect(['site/submissions-team-lead']);
            } else {
                Yii::$app->session->setFlash('error', 'Submission tidak valid.');
            }
        }

        $history = \app\models\SubtaskSubmission::find()
            ->where(['reviewer_id' => $user->user_id])
            ->andWhere(['!=', 'status', 'pending'])
            ->with(['subtask', 'submitter'])
            ->orderBy(['reviewed_at' => SORT_DESC])
            ->all();

        return $this->render('teamlead/submissions', [
            'pending' => $pending,
            'history' => $history,
        ]);
    }

    /**
 * Halaman pengajuan card ke admin oleh Team Lead, dan review oleh Admin.
 */
public function actionSubmissionsAdmin()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
        return $this->redirect(['site/login']);
    }

    $user = Yii::$app->user->identity;

    // Tampilkan semua card submissions berstatus pending (dapat ditinjau oleh admin mana pun)
    $pendingCardSubmissions = CardSubmission::find()
        ->where(['status' => 'pending'])
        ->with(['card', 'submitter', 'card.board.project'])
        ->orderBy(['created_at' => SORT_ASC])
        ->all();

    // Kelompokkan pengajuan project berdasarkan kumpulan card submissions per project
    $pendingProjectGroups = [];
    foreach ($pendingCardSubmissions as $cs) {
        $proj = ($cs->card && $cs->card->board && $cs->card->board->project) ? $cs->card->board->project : null;
        if (!$proj) { continue; }
        $pid = (int)$proj->project_id;
        if (!isset($pendingProjectGroups[$pid])) {
            $pendingProjectGroups[$pid] = [
                'project' => $proj,
                'submissions' => [],
            ];
        }
        $pendingProjectGroups[$pid]['submissions'][] = $cs;
    }

    // Handle accept/reject project oleh admin
    if (Yii::$app->request->isPost && Yii::$app->request->post('project_id')) {
        $projectId = (int)Yii::$app->request->post('project_id');
        $decision = (string)Yii::$app->request->post('decision');
        $reviewNotes = (string)Yii::$app->request->post('review_notes');
        $project = Project::findOne($projectId);
        if (!$project) {
            Yii::$app->session->setFlash('error', 'Project tidak ditemukan.');
            return $this->redirect(['site/submissions-admin']);
        }
        // Semua submissions pending untuk project
        $projSubs = CardSubmission::find()
            ->joinWith(['card.board.project'])
            ->where(['projects.project_id' => $projectId, 'card_submissions.status' => 'pending'])
            ->all();
        if (empty($projSubs)) {
            Yii::$app->session->setFlash('error', 'Tidak ada pengajuan card untuk project ini.');
            return $this->redirect(['site/submissions-admin']);
        }
        // Validasi semua subtask selesai
        $cards = Card::find()->joinWith('board')->where(['boards.project_id' => $projectId])->all();
        $allDone = true;
        foreach ($cards as $card) {
            $hasSubtasks = Subtask::find()->where(['card_id' => $card->card_id])->exists();
            if (!$hasSubtasks) { $allDone = false; break; }
            $hasUndone = Subtask::find()->where(['card_id' => $card->card_id])->andWhere(['!=','status','done'])->exists();
            if ($hasUndone) { $allDone = false; break; }
        }
        if (!$allDone) {
            Yii::$app->session->setFlash('error', 'Masih ada subtask belum selesai pada project ini.');
            return $this->redirect(['site/submissions-admin']);
        }
        if ($decision === 'accept') {
            $tx = Yii::$app->db->beginTransaction();
            try {
                foreach ($projSubs as $sub) {
                    $sub->reviewer_id = (int)$user->user_id;
                    $sub->status = 'accepted';
                    $sub->review_notes = $reviewNotes;
                    $sub->reviewed_at = new \yii\db\Expression('NOW()');
                    $sub->save(false, ['reviewer_id','status','review_notes','reviewed_at']);
                }
                // Update project status menjadi completed dan lepas anggota
                $project->status = 'completed';
                $project->save(false, ['status']);
                ProjectMember::deleteAll(['project_id' => $projectId]);
                Yii::$app->session->setFlash('success', 'Project diterima. Status project menjadi completed dan anggota dilepas.');
                $tx->commit();
            } catch (\Exception $e) {
                $tx->rollBack();
                Yii::$app->session->setFlash('error', 'Gagal memproses project: ' . $e->getMessage());
            }
        } elseif ($decision === 'reject') {
            foreach ($projSubs as $sub) {
                $sub->reviewer_id = (int)$user->user_id;
                $sub->status = 'rejected';
                $sub->review_notes = $reviewNotes;
                $sub->reviewed_at = new \yii\db\Expression('NOW()');
                $sub->save(false, ['reviewer_id','status','review_notes','reviewed_at']);
            }
            Yii::$app->session->setFlash('warning', 'Project ditolak. Team Lead dapat mengajukan kembali.');
        } else {
            Yii::$app->session->setFlash('error', 'Keputusan tidak valid.');
        }
        return $this->redirect(['site/submissions-admin']);
    }

    // Handle accept/reject card oleh admin (opsi per-card tetap tersedia)
    if (Yii::$app->request->isPost && Yii::$app->request->post('card_submission_id')) {
        $submissionId = (int)Yii::$app->request->post('card_submission_id');
        $decision = (string)Yii::$app->request->post('decision');
        $reviewNotes = (string)Yii::$app->request->post('review_notes');
        
        $submission = CardSubmission::findOne($submissionId);
        
        if (!$submission) {
            Yii::$app->session->setFlash('error', 'Submission tidak ditemukan.');
            return $this->redirect(['site/submissions-admin']);
        }

        // Catat reviewer sebagai admin yang melakukan review
        $submission->reviewer_id = (int)$user->user_id;
        $submission->save(false, ['reviewer_id']);

        // Validasi status
        if ($submission->status !== 'pending') {
            Yii::$app->session->setFlash('error', 'Submission ini sudah direview sebelumnya.');
            return $this->redirect(['site/submissions-admin']);
        }

        // Validasi card masih ada
        if (!$submission->card) {
            Yii::$app->session->setFlash('error', 'Card terkait submission tidak ditemukan.');
            return $this->redirect(['site/submissions-admin']);
        }

        // Validasi card status masih 'done'
        if ($submission->card->status !== 'done') {
            Yii::$app->session->setFlash('error', 'Status card sudah berubah, tidak dapat memproses submission.');
            return $this->redirect(['site/submissions-admin']);
        }

        // Process decision
        if ($decision === 'accept') {
            if ($submission->approve($reviewNotes)) {
                Yii::$app->session->setFlash('success', 
                    'Card <strong>"' . Html::encode($submission->card->card_title) . '"</strong> berhasil diterima dan ditandai sebagai selesai.'
                );
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menerima card. Silakan coba lagi.');
            }
        } elseif ($decision === 'reject') {
            if ($submission->reject($reviewNotes)) {
                Yii::$app->session->setFlash('warning', 
                    'Card <strong>"' . Html::encode($submission->card->card_title) . '"</strong> ditolak. Team Lead dapat mengajukan ulang.'
                );
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menolak card. Silakan coba lagi.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Keputusan tidak valid.');
        }
        
        return $this->redirect(['site/submissions-admin']);
    }

    // Riwayat pengajuan yang sudah direview (non-pending), dikelompokkan per project
    $allCardSubmissions = CardSubmission::find()
        ->andWhere(['!=', 'status', 'pending'])
        ->with(['card', 'submitter', 'reviewer', 'card.board.project'])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    $allProjectSubmissionGroups = [];
    foreach ($allCardSubmissions as $cs) {
        $proj = ($cs->card && $cs->card->board && $cs->card->board->project) ? $cs->card->board->project : null;
        if (!$proj) { continue; }
        $pid = (int)$proj->project_id;
        if (!isset($allProjectSubmissionGroups[$pid])) {
            $allProjectSubmissionGroups[$pid] = [
                'project' => $proj,
                'counts' => ['accepted' => 0, 'rejected' => 0],
                'last_submission_at' => $cs->created_at,
                'last_reviewed_at' => $cs->reviewed_at,
                'status' => null,
            ];
        }
        if ($cs->status === 'accepted') {
            $allProjectSubmissionGroups[$pid]['counts']['accepted'] += 1;
        } else {
            $allProjectSubmissionGroups[$pid]['counts']['rejected'] += 1;
        }
        if (strtotime($cs->created_at) > strtotime($allProjectSubmissionGroups[$pid]['last_submission_at'])) {
            $allProjectSubmissionGroups[$pid]['last_submission_at'] = $cs->created_at;
        }
        if ($cs->reviewed_at && (!$allProjectSubmissionGroups[$pid]['last_reviewed_at'] || strtotime($cs->reviewed_at) > strtotime($allProjectSubmissionGroups[$pid]['last_reviewed_at']))) {
            $allProjectSubmissionGroups[$pid]['last_reviewed_at'] = $cs->reviewed_at;
        }
    }
    foreach ($allProjectSubmissionGroups as &$grp) {
        if ($grp['project']->status === 'completed') {
            $grp['status'] = 'accepted';
        } elseif ($grp['counts']['accepted'] === 0 && $grp['counts']['rejected'] > 0) {
            $grp['status'] = 'rejected';
        } else {
            $grp['status'] = 'accepted';
        }
    }
    unset($grp);

    return $this->render('admin/submissions', [
        'pendingCardSubmissions' => $pendingCardSubmissions,
        'pendingProjectGroups' => $pendingProjectGroups,
        'allProjectSubmissionGroups' => $allProjectSubmissionGroups,
    ]);
}

    // API Actions untuk Subtask Overlay
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
        
        // Check access
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
                'user_name' => $comment->user ? $comment->user->full_name : 'Unknown'
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
        
        // Check access
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
                'resolved_by_name' => $help->resolver ? $help->resolver->full_name : null
            ];
        }
        
        return ['success' => true, 'help_requests' => $helpRequestsData];
    }

    // Helper Methods
    private function checkSubtaskAccess($subtask)
    {
        if (Yii::$app->user->identity->role === 'admin') {
            return true;
        }

        if (Yii::$app->user->identity->role === 'member') {
            $card = $subtask->card;
            if (!$card || !$card->board || !$card->board->project) {
                return false; // Cannot determine access if card/board/project is missing
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

    private function checkCardAccess($card)
    {
        if (Yii::$app->user->identity->role === 'admin') {
            return true;
        }

        if (Yii::$app->user->identity->role === 'member') {
            if (!$card->board || !$card->board->project) {
                return false; // Cannot determine access if board/project is missing
            }
            $project = $card->board->project;
            
            if ($project->team_lead_id == Yii::$app->user->id) {
                return true;
            }
            
            return CardAssignment::find()
                ->where(['card_id' => $card->card_id, 'user_id' => Yii::$app->user->id])
                ->exists();
        }

        return false;
    }

    // New enhanced dashboard action for members
    public function actionDashboardEnhanced()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $userId = Yii::$app->user->id;
        
        // Get projects for user (team lead or created by)
        $projects = Project::find()
            ->where(['team_lead_id' => $userId])
            ->orWhere(['created_by' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        // Get active cards count for the user
        $activeCardsCount = Card::find()
            ->innerJoin('card_assignments ca', 'ca.card_id = cards.card_id')
            ->where(['ca.user_id' => $userId])
            ->andWhere(['!=', 'cards.status', 'done']) // Exclude completed cards
            ->count();

        // Get completed subtasks count for the user
        $completedSubtasksCount = Subtask::find()
            ->where(['created_by' => $userId, 'status' => 'done'])
            ->count();

        // Get total boards count associated with user's projects
        $totalBoardsCount = Board::find()
            ->innerJoin('projects p', 'p.project_id = boards.project_id')
            ->where(['p.team_lead_id' => $userId])
            ->orWhere(['p.created_by' => $userId])
            ->count();

        // Get current tracking session for the user
        $currentTracking = TimeTracking::getActiveSession($userId);

        // Get total minutes tracked today for the user
        $totalMinutesToday = TimeTracking::getTotalMinutesToday($userId);

        return $this->render('member/dashboard-enhanced', [
            'projects' => $projects,
            'activeCardsCount' => $activeCardsCount,
            'completedSubtasksCount' => $completedSubtasksCount,
            'totalBoardsCount' => $totalBoardsCount,
            'currentTracking' => $currentTracking,
            'totalMinutesToday' => $totalMinutesToday,
        ]);
    }

    // Updated Timer Methods (replacing existing ones)
    public function actionStartTimer()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    if (Yii::$app->user->isGuest) {
        return ['success' => false, 'message' => 'Not authenticated'];
    }

    $request = Yii::$app->request;
    $subtaskId = $request->post('subtask_id'); // Expecting subtask_id
    $userId = Yii::$app->user->id;

    if (!$subtaskId) {
        return ['success' => false, 'message' => 'Subtask ID is required.'];
    }

    /** @var \app\models\Subtask|null $subtask */
    $subtask = Subtask::findOne($subtaskId);
    if (!$subtask) {
        return ['success' => false, 'message' => 'Subtask not found.'];
    }

    if (!$this->checkSubtaskAccess($subtask)) {
        return ['success' => false, 'message' => 'Access denied to this subtask.'];
    }

    try {
        // Stop any currently running timer for this user
        $runningTimer = TimeLog::find()
            ->where(['user_id' => $userId, 'end_time' => null])
            ->one();
        /** @var \app\models\TimeLog|null $runningTimer */

        if ($runningTimer) {
            $runningTimer->end_time = date('Y-m-d H:i:s');
            $runningTimer->calculateDuration();
            if (!$runningTimer->save()) {
                Yii::error('Failed to stop previous timer: ' . implode(', ', $runningTimer->getErrors()));
                // Continue to start new timer, but log the error
            }
        }

        // Start new timer
        /** @var \app\models\TimeLog $timeLog */
        $timeLog = new TimeLog();
        $timeLog->subtask_id = $subtaskId;
        $timeLog->card_id = $subtask->card_id; // Associate with card_id as well
        $timeLog->user_id = $userId;
        $timeLog->start_time = date('Y-m-d H:i:s');
        $timeLog->description = $request->post('description', 'Working on subtask');

        if ($timeLog->save()) {
            // Update user's current_task_status if available
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if (isset($user->current_task_status)) {
                $user->current_task_status = 'working';
                $user->save(false, ['current_task_status']);
            }
            // Set subtask status to in_progress when timer starts
            if ($subtask->status !== 'in_progress') {
                $subtask->status = 'in_progress';
                $subtask->save(false, ['status']);
            }
            
            return ['success' => true, 'log_id' => $timeLog->log_id, 'message' => 'Timer started successfully.'];
        } else {
            Yii::error('Failed to start new timer: ' . implode(', ', $timeLog->getErrors()));
            return ['success' => false, 'message' => 'Failed to start timer. Please try again.'];
        }
    } catch (\Exception $e) {
        Yii::error("Exception during startTimer: " . $e->getMessage());
        return ['success' => false, 'message' => 'An internal error occurred.'];
    }
}


    public function actionStopTimer()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest) {
        return ['success' => false, 'message' => 'Not authenticated'];
    }

    $userId = Yii::$app->user->id;
    $logId = Yii::$app->request->post('log_id');

    // Find the running timer either by provided log_id or fallback to any running timer of the user
    $runningTimer = null;
    if ($logId) {
        $runningTimer = TimeLog::find()
            ->where(['log_id' => $logId, 'user_id' => $userId, 'end_time' => null])
            ->one();
        /** @var \app\models\TimeLog|null $runningTimer */
    }

    if (!$runningTimer) {
        $runningTimer = TimeLog::find()
            ->where(['user_id' => $userId, 'end_time' => null])
            ->one();
        /** @var \app\models\TimeLog|null $runningTimer */
    }

    if (!$runningTimer) {
        return ['success' => false, 'message' => 'No active timer found.'];
    }

    try {
        $runningTimer->end_time = date('Y-m-d H:i:s');
        // Calculate and set duration
        $runningTimer->calculateDuration();

        if ($runningTimer->save()) {
            // Optionally update user's status
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if (isset($user->current_task_status)) {
                $user->current_task_status = 'idle';
                $user->save(false, ['current_task_status']);
            }
            return [
                'success' => true,
                'log_id' => $runningTimer->log_id,
                'message' => 'Timer stopped successfully.'
            ];
        }

        Yii::error('Failed to stop timer: ' . json_encode($runningTimer->getErrors()));
        return ['success' => false, 'message' => 'Failed to stop timer. Please try again.'];

    } catch (\Exception $e) {
        Yii::error('Exception during stopTimer: ' . $e->getMessage());
        return ['success' => false, 'message' => 'An internal error occurred.'];
    }
}


    public function actionCheckRunningTimer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        $userId = Yii::$app->user->id;

        // Find any running timer for the user
        $runningTimer = TimeLog::find()
            ->where(['user_id' => $userId, 'end_time' => null])
            ->with(['subtask'])
            ->one();

        if ($runningTimer) {
            return [
                'success' => true,
                'has_running_timer' => true,
                'timer' => [
                    'log_id' => $runningTimer->log_id,
                    'subtask_id' => $runningTimer->subtask_id,
                    'subtask_title' => $runningTimer->subtask ? $runningTimer->subtask->subtask_title : 'Unknown',
                    'start_time' => $runningTimer->start_time,
                    'start_timestamp' => strtotime($runningTimer->start_time) * 1000,
                    'description' => $runningTimer->description
                ]
            ];
        } else {
            return [
                'success' => true,
                'has_running_timer' => false,
                'message' => 'No running timer found'
            ];
        }
    }

    // Time tracking controller actions
    public function actionStartTracking()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }

    $userId = Yii::$app->user->id;
    $tracking = TimeTracking::startTracking($userId);

    if ($tracking) {
        return [
            'success' => true,
            'tracking_id' => $tracking->tracking_id,
            'start_time' => $tracking->start_time,
        ];
    }

    return ['success' => false, 'message' => 'Failed to start tracking'];
}

public function actionStopTracking()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }

    $trackingId = Yii::$app->request->post('tracking_id');
    $durationMinutes = Yii::$app->request->post('duration_minutes');

    if (!$trackingId) {
        return ['success' => false, 'message' => 'Tracking ID is required'];
    }

    $tracking = TimeTracking::findOne($trackingId);
    
    if (!$tracking) {
        return ['success' => false, 'message' => 'Tracking session not found'];
    }

    // Verify that the tracking session belongs to the current user
    if ($tracking->user_id != Yii::$app->user->id) {
        return ['success' => false, 'message' => 'Access denied'];
    }

    if ($tracking->stopTracking($durationMinutes)) {
        return [
            'success' => true, 
            'message' => 'Tracking stopped successfully',
            'duration_minutes' => $tracking->duration_minutes
        ];
    }

    return ['success' => false, 'message' => 'Failed to stop tracking'];
}

public function actionGetTrackingStatus()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }

    $userId = Yii::$app->user->id;
    $tracking = TimeTracking::getActiveSession($userId);

    if (!$tracking) {
        return [
            'success' => true,
            'is_tracking' => false,
            'message' => 'No active session'
        ];
    }

    return [
        'success' => true,
        'is_tracking' => true,
        'tracking_id' => $tracking->tracking_id,
        'start_time' => $tracking->start_time,
        'start_timestamp' => strtotime($tracking->start_time) * 1000,
        'elapsed_seconds' => $tracking->getElapsedSeconds(),
    ];
}

public function actionUpdateTaskStatus()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest) {
        return ['success' => false, 'message' => 'Not authenticated'];
    }

    $status = Yii::$app->request->post('status');
    $userId = Yii::$app->user->id;

    $user = User::findOne($userId);
    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }

    $user->current_task_status = $status;
    if ($user->save(false, ['current_task_status'])) {
        return ['success' => true, 'message' => 'Task status updated'];
    } else {
        return ['success' => false, 'message' => 'Failed to update task status'];
    }
}

    public function actionStartActivity()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $userId = Yii::$app->user->id;
        $user = User::findOne($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        try {
            // Stop any existing active tracking session for the user
            $existingActiveTracking = TimeTracking::find()
                ->where(['user_id' => $userId, 'is_active' => 1])
                ->one();
            
            if ($existingActiveTracking) {
                // If there's an active session, stop it first
                $existingActiveTracking->end_time = date('Y-m-d H:i:s');
                $existingActiveTracking->is_active = 0;
                // Calculate duration based on start_time and end_time
                $start = new \DateTime($existingActiveTracking->start_time);
                $end = new \DateTime($existingActiveTracking->end_time);
                $interval = $start->diff($end);
                $existingActiveTracking->duration_minutes = ($interval->h * 60) + $interval->i;
                $existingActiveTracking->save();
            }

            // Start a new tracking session
            $tracking = new TimeTracking();
            $tracking->user_id = $userId;
            $tracking->start_time = date('Y-m-d H:i:s');
            $tracking->is_active = 1;
            // Set initial last_ping to start_time
            $tracking->last_ping = $tracking->start_time;

            if ($tracking->save()) {
                $user->current_task_status = 'active';
                $user->save(false, ['current_task_status']);

                return [
                    'success' => true,
                    'tracking_id' => $tracking->tracking_id,
                    'start_time' => $tracking->start_time
                ];
            } else {
                Yii::error('Failed to start activity: ' . json_encode($tracking->getErrors()));
                return ['success' => false, 'message' => 'Failed to start activity'];
            }
        } catch (\Exception $e) {
            Yii::error('Exception during startActivity: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error starting activity: ' . $e->getMessage()];
        }
    }

    public function actionPingActivity()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $userId = Yii::$app->user->id;
        // Allow tracking_id to be passed via POST body or query params
        $trackingId = Yii::$app->request->post('tracking_id') ?? Yii::$app->request->get('tracking_id');

        if (!$trackingId) {
            return ['success' => false, 'message' => 'Tracking ID is required'];
        }

        try {
            $tracking = TimeTracking::findOne($trackingId);

            // Check if tracking exists and belongs to the current user
            if (!$tracking || $tracking->user_id !== $userId || !$tracking->is_active) {
                // If tracking is not active, or not found for user, return error
                return ['success' => false, 'message' => 'Active tracking session not found for this user.'];
            }

            // Update last_ping timestamp to keep session alive
            $tracking->last_ping = date('Y-m-d H:i:s');
            if ($tracking->save()) {
                $user = User::findOne($userId);
                if ($user) {
                    $user->current_task_status = 'active';
                    $user->save(false, ['current_task_status']);
                }
                return [
                    'success' => true,
                    'message' => 'Ping successful',
                    'tracking_id' => $tracking->tracking_id
                ];
            } else {
                Yii::error('Failed to update last_ping for tracking ID ' . $trackingId . ': ' . json_encode($tracking->getErrors()));
                return ['success' => false, 'message' => 'Failed to ping activity'];
            }
        } catch (\Exception $e) {
            Yii::error('Exception during pingActivity: ' . $e->getMessage());
            Yii::error($e->getTraceAsString());
            return ['success' => false, 'message' => 'Error pinging activity: ' . $e->getMessage()];
        }
}

}
