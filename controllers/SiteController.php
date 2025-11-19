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
        return Yii::$app->runAction('project/admin-projects');
    }

    public function actionCreateProject()
    {
        return Yii::$app->runAction('project/create-project');
    }

    public function actionUpdateProject()
    {
        return Yii::$app->runAction('project/update-project');
    }

    public function actionDeleteProject($id)
    {
        return Yii::$app->runAction('project/delete-project', ['id' => $id]);
    }

    public function actionGetProjectDetail($id)
    {
        return Yii::$app->runAction('project/get-project-detail', ['id' => $id]);
    }

    // Subtask Methods
    public function actionMemberSubtasks()
    {
        return Yii::$app->runAction('subtask/member-subtasks');
    }

    public function actionSubtasks($card_id)
    {
        return Yii::$app->runAction('subtask/subtasks', ['card_id' => $card_id]);
    }

    public function actionCreateSubtask()
    {
        return Yii::$app->runAction('subtask/create-subtask');
    }

    public function actionUpdateSubtask($id = null)
    {
        return Yii::$app->runAction('subtask/update-subtask', ['id' => $id]);
    }

public function actionUpdateSubtaskStatus()
{
    return Yii::$app->runAction('subtask/update-subtask-status');
}

    public function actionDeleteSubtask($id)
    {
        return Yii::$app->runAction('subtask/delete-subtask', ['id' => $id]);
    }

    // Tambahkan method ini di SiteController.php

    public function actionMemberSubtasksBoard()
    {
        return Yii::$app->runAction('subtask/member-subtasks-board');
    }

    public function actionGetSubtaskDetail($id)
    {
        return Yii::$app->runAction('subtask/get-subtask-detail', ['id' => $id]);
    }

    // Comment Methods
    public function actionAddComment()
    {
        return Yii::$app->runAction('comment/add-comment');
    }

    // Help Request Methods
    public function actionCreateHelpRequest()
    {
        return Yii::$app->runAction('help/create-help-request');
    }

    public function actionUpdateHelpRequest()
    {
        return Yii::$app->runAction('help/update-help-request');
    }
    // Cards Methods (existing - keeping your code)
    public function actionMemberCards()
    {
        return Yii::$app->runAction('card/member-cards');
    }

    public function actionCreateCard()
    {
        return Yii::$app->runAction('card/create-card');
    }

    public function actionUpdateCard()
    {
        return Yii::$app->runAction('card/update-card');
    }

    public function actionGetCardDetail($id)
    {
        return Yii::$app->runAction('card/get-card-detail', ['id' => $id]);
    }

    public function actionDeleteCard($id)
    {
        return Yii::$app->runAction('card/delete-card', ['id' => $id]);
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
    return Yii::$app->runAction('board/member-boards');
}

// Board Methods untuk Admin
public function actionAdminBoards()
{
    return Yii::$app->runAction('board/admin-boards');
}

    public function actionUpdateProjectStatus()
    {
        return Yii::$app->runAction('project/update-project-status');
    }

    // User Management
    public function actionAdminUsers()
    {
        return Yii::$app->runAction('user/admin-users');
    }

    public function actionCreateUser()
    {
        return Yii::$app->runAction('user/create-user');
    }

    public function actionUpdateUser()
    {
        return Yii::$app->runAction('user/update-user');
    }

    public function actionDeleteUser($id)
    {
        return Yii::$app->runAction('user/delete-user', ['id' => $id]);
    }

    public function actionGetUserDetail($id)
    {
        return Yii::$app->runAction('user/get-user-detail', ['id' => $id]);
    }

    public function actionUpdateMyProfile()
    {
        return Yii::$app->runAction('user/update-my-profile');
    }

    // Reports Methods
    public function actionMemberReports()
{
    return Yii::$app->runAction('reports/member-reports');
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
    return Yii::$app->runAction('reports/reports-admin');
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
    return Yii::$app->runAction('submission/submissions-member');
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
        if ($proj->status === 'completed') { continue; }
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
            if ($project->status === 'completed') {
                Yii::$app->session->setFlash('error', 'Project sudah berstatus completed dan tidak dapat diajukan.');
                return $this->redirect(['site/submissions-member']);
            }
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
        return Yii::$app->runAction('submission/submissions-team-lead');
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
    return Yii::$app->runAction('submission/submissions-admin');
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
        return Yii::$app->runAction('subtask/get-subtask-comments', ['id' => $id]);
    }
    
    public function actionGetSubtaskHelpRequests($id)
    {
        return Yii::$app->runAction('subtask/get-subtask-help-requests', ['id' => $id]);
    }

    // Helper Methods
    private function checkSubtaskAccess($subtask)
    //Seperti database function, menerima parameter $subtask dan mengembalikan boolean.
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
    return Yii::$app->runAction('timer/start-timer');
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
    return Yii::$app->runAction('timer/stop-timer');
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
        return Yii::$app->runAction('timer/check-running-timer');
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
        return Yii::$app->runAction('tracking/start-tracking');
    }

public function actionStopTracking()
{
    return Yii::$app->runAction('tracking/stop-tracking');
}

public function actionGetTrackingStatus()
{
    return Yii::$app->runAction('tracking/get-tracking-status');
}

public function actionUpdateTaskStatus()
{
    return Yii::$app->runAction('tracking/update-task-status');
}

    public function actionStartActivity()
    {
        return Yii::$app->runAction('tracking/start-activity');
    }

    public function actionPingActivity()
    {
        return Yii::$app->runAction('tracking/ping-activity');
    }

}
