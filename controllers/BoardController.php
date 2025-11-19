<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Project;
use app\models\Card;
use app\models\Subtask;

class BoardController extends Controller
{
    public function actionMemberBoards() 
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $userId = Yii::$app->user->id;

        $cardsQuery = Card::find()
            ->joinWith(['board.project'])
            ->where(['cards.created_by' => $userId])
            ->andWhere(['<>', 'projects.status', 'completed'])
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

        $subtasks = Subtask::find()->distinct(true)
            ->joinWith(['card.board.project'])
            ->andWhere(['<>', 'projects.status', 'completed'])
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

        return $this->render('@app/views/site/member/boards', [
            'cardsByStatus' => $cardsByStatus,
            'subtasksByStatus' => $subtasksByStatus,
        ]);
    }

    public function actionAdminBoards()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

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

        return $this->render('@app/views/site/admin/boards', [
            'projectsByStatus' => $projectsByStatus,
        ]);
    }
}