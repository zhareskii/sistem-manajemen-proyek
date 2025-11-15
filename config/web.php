<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'dQe-OPYLiG86V9ZXRjDfGTdj9MyZfe2L',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Authentication
                'login' => 'site/login',
                'logout' => 'site/logout',
                'register' => 'site/register',
                
                // Admin Routes
                'admin/dashboard' => 'site/dashboard-admin',
                'admin/projects' => 'site/admin-projects',
                'admin/boards' => 'site/admin-boards',
                'admin/users' => 'site/admin-users',
                'admin/reports' => 'site/admin-reports',
                'admin/submissions' => 'site/submissions-admin',
                
                // Member Routes
                'member/dashboard' => 'site/dashboard-member',
                'member/cards' => 'site/member-cards',
                'member/boards' => 'site/member-boards',
                'member/subtasks' => 'site/member-subtasks',
                'member/reports' => 'site/reports-member',
                // Unified Submissions Page
                'member/submissions' => 'site/member-submissions',
                'submissions' => 'site/member-submissions',
                
                // Project Management
                'projects' => 'site/admin-projects',
                'project/create' => 'site/create-project',
                'project/update' => 'site/update-project',  
                'project/delete/<id:\d+>' => 'site/delete-project',
                'project/<id:\d+>' => 'site/get-project-detail',
                'project/update-status' => 'site/update-project-status',
                
                // Card Management
                'cards' => 'site/member-cards',
                'card/create' => 'site/create-card',
                'card/update' => 'site/update-card',
                'card/delete/<id:\d+>' => 'site/delete-card',
                'card/<id:\d+>' => 'site/get-card-detail',
                'site/get-card-detail' => 'site/get-card-detail',
                
                // Board Management
                'boards' => 'site/member-boards',
                'board/create' => 'site/create-board',
                'board/update' => 'site/update-board',
                'board/delete/<id:\d+>' => 'site/delete-board',
                'board/<id:\d+>' => 'site/get-board-detail',
                
                // Subtask Management
                'subtasks/<card_id:\d+>' => 'site/subtasks',
                'subtask/create' => 'site/create-subtask',
                'subtask/update/<id:\d+>' => 'site/update-subtask',
                'subtask/delete/<id:\d+>' => 'site/delete-subtask',
                'subtask/<id:\d+>' => 'site/get-subtask-detail',
                
                // Time Tracking
                'timer/start' => 'site/start-timer',
                'timer/stop' => 'site/stop-timer',
                
                // Comments
                'comment/add' => 'site/add-comment',
                
                // Help Requests
                'help/create' => 'site/create-help-request',
                'help/update' => 'site/update-help-request',
                
                // User Management
                'user/create' => 'site/create-user',
                'user/update' => 'site/update-user',
                'user/delete/<id:\d+>' => 'site/delete-user',
                'user/<id:\d+>' => 'site/get-user-detail',
                
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;