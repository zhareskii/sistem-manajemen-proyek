<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\HelpRequest;
use app\models\Subtask;
use app\models\Card;
use app\models\Board;
use app\models\Project;

class HelpController extends Controller
{
    public function actionCreateHelpRequest()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'member') {
            return $this->redirect(['site/login']);
        }

        $model = new HelpRequest();
        
        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            
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
            
            $model = HelpRequest::findOne($requestId);
            
            if (!$model) {
                return ['success' => false, 'message' => 'Permintaan bantuan tidak ditemukan.'];
            }

            $subtask = $model->subtask;
            if (!$subtask) {
                return ['success' => false, 'message' => 'Subtask tidak ditemukan.'];
            }
            $card = $subtask->card;
            if (!$card) {
                return ['success' => false, 'message' => 'Card tidak ditemukan.'];
            }
            $board = $card->board;
            if (!$board) {
                return ['success' => false, 'message' => 'Board tidak ditemukan.'];
            }
            $project = $board->project;
            if (!$project) {
                return ['success' => false, 'message' => 'Project tidak ditemukan.'];
            }

            $isAdmin = Yii::$app->user->identity->role === 'admin';
            $isTeamLead = $project->team_lead_id == Yii::$app->user->id;
            $isCreator = $model->user_id == Yii::$app->user->id;

            if (!$isAdmin && !$isTeamLead && !$isCreator) {
                return ['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengupdate permintaan bantuan ini.'];
            }

            $didUpdate = false;

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
                    return ['success' => false, 'message' => 'Status tidak valid atau tidak diizinkan untuk role Anda.'];
                }

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
                $msg = $newStatus ? ('Status berhasil diupdate menjadi: ' . $newStatus) : 'Help request berhasil diperbarui.';
                return ['success' => true, 'message' => $msg];
            } else {
                $errors = implode(', ', $model->getFirstErrors());
                return ['success' => false, 'message' => 'Gagal mengupdate: ' . $errors];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
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
            
            $isAssigned = \app\models\CardAssignment::find()
                ->where(['card_id' => $card->card_id, 'user_id' => Yii::$app->user->id])
                ->exists();
            
            $isCreator = $subtask->created_by == Yii::$app->user->id;
            
            return $isAssigned || $isCreator;
        }

        return false;
    }
}