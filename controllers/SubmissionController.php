<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Html;
use app\models\Project;
use app\models\Card;
use app\models\Subtask;
use app\models\CardSubmission;
use app\models\SubtaskSubmission;
use app\models\ProjectMember;

class SubmissionController extends Controller
{
    public function actionSubmissionsMember()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        $user = Yii::$app->user->identity;

        $eligibleSubtasks = Subtask::find()
            ->alias('s')
            ->joinWith(['card c'])
            ->leftJoin('card_assignments ca', 'ca.card_id = s.card_id')
            ->where(['or', ['s.created_by' => $user->user_id], ['ca.user_id' => $user->user_id]])
            ->andWhere(['!=', 's.status', 'done'])
            ->orderBy(['s.created_at' => SORT_DESC])
            ->all();

        $eligibleProjects = [];
        $projectsLead = Project::find()
            ->where(['team_lead_id' => $user->user_id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        foreach ($projectsLead as $proj) {
            if ($proj->status === 'completed') { continue; }
            $cardsInProject = Card::find()->joinWith('board')
                ->where(['boards.project_id' => $proj->project_id])
                ->all();
            if (empty($cardsInProject)) { continue; }
            $allDone = true;
            foreach ($cardsInProject as $card) {
                $hasSubtasks = Subtask::find()->where(['card_id' => $card->card_id])->exists();
                if (!$hasSubtasks) { $allDone = false; break; }
                $hasUndone = Subtask::find()->where(['card_id' => $card->card_id])->andWhere(['!=','status','done'])->exists();
                if ($hasUndone) { $allDone = false; break; }
            }
            if ($allDone) { $eligibleProjects[] = $proj; }
        }

        if (Yii::$app->request->isPost && Yii::$app->request->post('submit_subtask_id')) {
            $subtaskId = (int)Yii::$app->request->post('submit_subtask_id');
            $notes = (string)Yii::$app->request->post('submission_notes');
            $subtask = Subtask::findOne($subtaskId);
            if (!$subtask) {
                Yii::$app->session->setFlash('error', 'Subtask tidak ditemukan.');
                return $this->redirect(['site/submissions-member']);
            }
            $project = ($subtask->card && $subtask->card->board) ? $subtask->card->board->project : null;
            if (!$project || (int)$project->team_lead_id !== (int)$user->user_id) {
                Yii::$app->session->setFlash('error', 'Subtask bukan pada project yang Anda pimpin.');
                return $this->redirect(['site/submissions-member']);
            }
            if ($subtask->status !== 'done') {
                Yii::$app->session->setFlash('error', 'Subtask belum selesai, tidak dapat diajukan.');
                return $this->redirect(['site/submissions-member']);
            }
            $submission = new SubtaskSubmission();
            $submission->subtask_id = $subtask->subtask_id;
            $submission->submitted_by = $subtask->created_by;
            $submission->reviewer_id = $user->user_id;
            $submission->status = 'pending';
            $submission->submission_notes = $notes;
            if ($submission->save()) {
                Yii::$app->session->setFlash('success', 'Subtask berhasil diajukan ke Team Lead.');
            } else {
                Yii::$app->session->setFlash('error', 'Gagal mengajukan subtask.');
            }
            return $this->redirect(['site/submissions-member']);
        }

        if (Yii::$app->request->isPost && Yii::$app->request->post('submit_project_id')) {
            $projectId = (int)Yii::$app->request->post('submit_project_id');
            $notes = (string)Yii::$app->request->post('project_submission_notes');
            $project = Project::findOne($projectId);
            $admin = \app\models\User::find()->where(['role' => 'admin'])->orderBy(['created_at' => SORT_ASC])->one();
            if ($project && $admin && (int)$project->team_lead_id === (int)$user->user_id) {
                $cardsInProject = Card::find()->joinWith('board')->where(['boards.project_id' => $projectId])->all();
                if (empty($cardsInProject)) {
                    Yii::$app->session->setFlash('error', 'Tidak ada card pada project ini.');
                    return $this->redirect(['site/submissions-member']);
                }
                $allDone = true;
                foreach ($cardsInProject as $card) {
                    $hasSubtasks = Subtask::find()->where(['card_id' => $card->card_id])->exists();
                    if (!$hasSubtasks) { $allDone = false; break; }
                    $hasUndone = Subtask::find()->where(['card_id' => $card->card_id])->andWhere(['!=','status','done'])->exists();
                    if ($hasUndone) { $allDone = false; break; }
                }
                if (!$allDone) {
                    Yii::$app->session->setFlash('error', 'Masih ada subtask yang belum selesai.');
                    return $this->redirect(['site/submissions-member']);
                }
                $created = 0;
                foreach ($cardsInProject as $card) {
                    $exists = CardSubmission::find()->where(['card_id' => $card->card_id, 'status' => 'pending'])->exists();
                    if ($exists) { continue; }
                    $submission = new CardSubmission();
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

        if (Yii::$app->request->isPost && Yii::$app->request->post('review_submission_id')) {
            $reviewId = (int)Yii::$app->request->post('review_submission_id');
            $reviewAction = (string)Yii::$app->request->post('review_action');
            $reviewNotes = (string)Yii::$app->request->post('review_notes');
            $submission = SubtaskSubmission::findOne($reviewId);
            if ($submission && (int)$submission->reviewer_id === (int)$user->user_id && $submission->status === 'pending') {
                if ($reviewAction === 'accept') {
                    $submission->status = 'accepted';
                } else {
                    $submission->status = 'rejected';
                }
                $submission->review_notes = $reviewNotes;
                $submission->reviewed_at = new \yii\db\Expression('NOW()');
                if ($submission->save()) {
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

        $mySubmissions = SubtaskSubmission::find()
            ->where(['submitted_by' => $user->user_id])
            ->with(['subtask', 'subtask.card', 'reviewer'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        $myCardSubmissions = CardSubmission::find()
            ->where(['submitted_by' => $user->user_id])
            ->with(['card', 'card.board', 'reviewer', 'card.board.project'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

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

        $pendingSubtaskSubmissions = SubtaskSubmission::find()
            ->where(['reviewer_id' => $user->user_id, 'status' => 'pending'])
            ->with(['subtask', 'subtask.card', 'submitter'])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
        $reviewedSubtaskSubmissions = SubtaskSubmission::find()
            ->where(['reviewer_id' => $user->user_id])
            ->andWhere(['!=', 'status', 'pending'])
            ->with(['subtask', 'subtask.card', 'submitter'])
            ->orderBy(['reviewed_at' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();

        return $this->render('@app/views/site/member/submissions', [
            'eligibleSubtasks' => $eligibleSubtasks,
            'eligibleProjects' => $eligibleProjects,
            'mySubmissions' => $mySubmissions,
            'myCardSubmissions' => $myCardSubmissions,
            'myProjectSubmissionGroups' => $myProjectSubmissionGroups,
            'pendingSubtaskSubmissions' => $pendingSubtaskSubmissions,
            'reviewedSubtaskSubmissions' => $reviewedSubtaskSubmissions,
        ]);
    }

    public function actionSubmissionsTeamLead()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        $user = Yii::$app->user->identity;

        $pending = SubtaskSubmission::find()
            ->where(['reviewer_id' => $user->user_id, 'status' => 'pending'])
            ->with(['subtask', 'submitter'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        if (Yii::$app->request->isPost) {
            $submissionId = (int)Yii::$app->request->post('submission_id');
            $decision = (string)Yii::$app->request->post('decision');
            $reviewNotes = (string)Yii::$app->request->post('review_notes');
            $submission = SubtaskSubmission::findOne($submissionId);
            if ($submission && (int)$submission->reviewer_id === (int)$user->user_id) {
                if ($decision === 'accept') {
                    $submission->status = 'accepted';
                    $submission->review_notes = $reviewNotes;
                    $submission->reviewed_at = date('Y-m-d H:i:s');
                    $submission->save(false);
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

        $history = SubtaskSubmission::find()
            ->where(['reviewer_id' => $user->user_id])
            ->andWhere(['!=', 'status', 'pending'])
            ->with(['subtask', 'submitter'])
            ->orderBy(['reviewed_at' => SORT_DESC])
            ->all();

        return $this->render('@app/views/site/teamlead/submissions', [
            'pending' => $pending,
            'history' => $history,
        ]);
    }

    public function actionSubmissionsAdmin()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $user = Yii::$app->user->identity;

        $pendingCardSubmissions = CardSubmission::find()
            ->where(['status' => 'pending'])
            ->with(['card', 'submitter', 'card.board.project'])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

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

        if (Yii::$app->request->isPost && Yii::$app->request->post('project_id')) {
            $projectId = (int)Yii::$app->request->post('project_id');
            $decision = (string)Yii::$app->request->post('decision');
            $reviewNotes = (string)Yii::$app->request->post('review_notes');
            $project = Project::findOne($projectId);
            if (!$project) {
                Yii::$app->session->setFlash('error', 'Project tidak ditemukan.');
                return $this->redirect(['site/submissions-admin']);
            }
            $projSubs = CardSubmission::find()
                ->joinWith(['card.board.project'])
                ->where(['projects.project_id' => $projectId, 'card_submissions.status' => 'pending'])
                ->all();
            if (empty($projSubs)) {
                Yii::$app->session->setFlash('error', 'Tidak ada pengajuan card untuk project ini.');
                return $this->redirect(['site/submissions-admin']);
            }
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
                $tx = Yii::$app->db->beginTransaction(); //transaksi
                try {
                    foreach ($projSubs as $sub) {
                        $sub->reviewer_id = (int)$user->user_id;
                        $sub->status = 'accepted';
                        $sub->review_notes = $reviewNotes;
                        $sub->reviewed_at = new \yii\db\Expression('NOW()');
                        $sub->save(false, ['reviewer_id','status','review_notes','reviewed_at']);
                    }
                    $project->status = 'completed';
                    $project->save(false, ['status']);
                    ProjectMember::deleteAll(['project_id' => $projectId]);
                    Yii::$app->session->setFlash('success', 'Project diterima. Status project menjadi completed dan anggota dilepas.');
                    $tx->commit(); //commit
                } catch (\Exception $e) {
                    $tx->rollBack(); //rollback
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

        if (Yii::$app->request->isPost && Yii::$app->request->post('card_submission_id')) {
            $submissionId = (int)Yii::$app->request->post('card_submission_id');
            $decision = (string)Yii::$app->request->post('decision');
            $reviewNotes = (string)Yii::$app->request->post('review_notes');
            $submission = CardSubmission::findOne($submissionId);
            if (!$submission) {
                Yii::$app->session->setFlash('error', 'Submission tidak ditemukan.');
                return $this->redirect(['site/submissions-admin']);
            }
            $submission->reviewer_id = (int)$user->user_id;
            $submission->save(false, ['reviewer_id']);
            if ($submission->status !== 'pending') {
                Yii::$app->session->setFlash('error', 'Submission ini sudah direview sebelumnya.');
                return $this->redirect(['site/submissions-admin']);
            }
            if (!$submission->card) {
                Yii::$app->session->setFlash('error', 'Card terkait submission tidak ditemukan.');
                return $this->redirect(['site/submissions-admin']);
            }
            if ($submission->card->status !== 'done') {
                Yii::$app->session->setFlash('error', 'Status card sudah berubah, tidak dapat memproses submission.');
                return $this->redirect(['site/submissions-admin']);
            }
            if ($decision === 'accept') {
                if ($submission->approve($reviewNotes)) {
                    Yii::$app->session->setFlash('success', 'Card <strong>"' . Html::encode($submission->card->card_title) . '"</strong> berhasil diterima dan ditandai sebagai selesai.');
                } else {
                    Yii::$app->session->setFlash('error', 'Gagal menerima card. Silakan coba lagi.');
                }
            } elseif ($decision === 'reject') {
                if ($submission->reject($reviewNotes)) {
                    Yii::$app->session->setFlash('warning', 'Card <strong>"' . Html::encode($submission->card->card_title) . '"</strong> ditolak. Team Lead dapat mengajukan ulang.');
                } else {
                    Yii::$app->session->setFlash('error', 'Gagal menolak card. Silakan coba lagi.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Keputusan tidak valid.');
            }
            return $this->redirect(['site/submissions-admin']);
        }

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

        return $this->render('@app/views/site/admin/submissions', [
            'pendingCardSubmissions' => $pendingCardSubmissions,
            'pendingProjectGroups' => $pendingProjectGroups,
            'allProjectSubmissionGroups' => $allProjectSubmissionGroups,
        ]);
    }
}