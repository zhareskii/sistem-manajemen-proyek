<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Project;
use app\models\Board;
use app\models\Card;
use app\models\Subtask;
use app\models\TimeLog;
use app\models\HelpRequest;
use app\models\User;
use app\models\TimeTracking;
use app\models\SubtaskSubmission;
use app\models\CardSubmission;

class ReportsController extends Controller
{
    public function actionMemberReports()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $userId = Yii::$app->user->id;
        $projectIds = Project::find()
            ->select('project_id')
            ->where(['team_lead_id' => $userId])
            ->orWhere(['created_by' => $userId])
            ->column();

        $productivityData = [];
        if (!empty($projectIds)) {
            $projectNames = Project::find()
                ->select(['project_id', 'project_name'])
                ->where(['project_id' => $projectIds])
                ->indexBy('project_id')
                ->asArray()
                ->all();

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

            usort($productivityData, function($a, $b) {
                return $b['total_hours'] <=> $a['total_hours'];
            });
        }

        $cardsCreatedCount = Card::find()->where(['created_by' => $userId])->count();
        $subtasksCreatedCount = Subtask::find()->where(['created_by' => $userId])->count();
        $cardsCompletedCount = Card::find()->where(['created_by' => $userId, 'status' => 'done'])->count();
        $subtasksCompletedCount = Subtask::find()->where(['created_by' => $userId, 'status' => 'done'])->count();
        $subtaskAcceptedCount = SubtaskSubmission::find()->where(['submitted_by' => $userId, 'status' => 'accepted'])->count();
        $subtaskRejectedCount = SubtaskSubmission::find()->where(['submitted_by' => $userId, 'status' => 'rejected'])->count();
        $cardAcceptedCount = CardSubmission::find()->where(['submitted_by' => $userId, 'status' => 'accepted'])->count();
        $cardRejectedCount = CardSubmission::find()->where(['submitted_by' => $userId, 'status' => 'rejected'])->count();

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

        $dailyWorkingAll = [];
        foreach ($rawDailyAll as $row) {
            $dateKey = $row['date'];
            $dailyWorkingAll[$dateKey] = (int)($row['minutes'] ?? 0);
        }

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

        $eligibleSubtasks = Subtask::find()
            ->alias('s')
            ->joinWith(['card c'])
            ->leftJoin('card_assignments ca', 'ca.card_id = s.card_id')
            ->where(['or', ['s.created_by' => $userId], ['ca.user_id' => $userId]])
            ->andWhere(['!=', 's.status', 'done'])
            ->orderBy(['s.created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        $eligibleCards = Card::find()
            ->alias('c')
            ->joinWith(['board b', 'board.project p'])
            ->where(['p.team_lead_id' => $userId, 'c.status' => 'done'])
            ->orderBy(['c.created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        $mySubmissions = SubtaskSubmission::find()
            ->where(['submitted_by' => $userId])
            ->with(['subtask', 'subtask.card', 'reviewer'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();
        $myCardSubmissions = CardSubmission::find()
            ->where(['submitted_by' => $userId])
            ->with(['card', 'card.board', 'reviewer'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();
        $pendingSubtaskSubmissions = SubtaskSubmission::find()
            ->where(['reviewer_id' => $userId, 'status' => 'pending'])
            ->with(['subtask', 'subtask.card', 'submitter'])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit(10)
            ->all();
        $reviewedSubtaskSubmissions = SubtaskSubmission::find()
            ->where(['reviewer_id' => $userId])
            ->andWhere(['!=', 'status', 'pending'])
            ->with(['subtask', 'subtask.card', 'submitter'])
            ->orderBy(['reviewed_at' => SORT_DESC, 'created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('@app/views/site/member/reports', [
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
            $boards = Board::find()->where(['project_id' => $project->project_id])->all();
            $cardData = [];
            $totalHours = 0;
            $cardCount = 0;
            $subtaskCount = 0;
            foreach ($boards as $board) {
                $cards = Card::find()->where(['board_id' => $board->board_id])->all();
                foreach ($cards as $card) {
                    $cardCount++;
                    $subtasks = Subtask::find()->where(['card_id' => $card->card_id])->all();
                    $subtaskCount += count($subtasks);
                    $timeLogs = TimeLog::find()->where(['card_id' => $card->card_id])->all();
                    $cardHours = 0;
                    foreach ($timeLogs as $log) {
                        if ($log->duration_minutes) {
                            $cardHours += $log->duration_minutes / 60;
                        }
                    }
                    $totalHours += $cardHours;
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

        return $this->render('@app/views/site/admin/reports', [
            'projectData' => $projectData,
            'productivityData' => $productivityData
        ]);
    }
}