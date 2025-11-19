<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\Comment;
use app\models\Card;
use app\models\Subtask;
use app\models\Project;
use app\models\CardAssignment;

class CommentController extends Controller
{
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
                        $hasAccess = false;
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
                        $hasAccess = false;
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Subtask ID tidak boleh kosong untuk komentar tipe subtask.');
                    $hasAccess = false;
                }
            } else {
                Yii::$app->session->setFlash('error', 'Tipe komentar tidak valid.');
                $hasAccess = false;
            }
            
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
            } elseif (!$hasAccess && !Yii::$app->session->getFlash('error')) {
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

        $referrer = Yii::$app->request->referrer;
        if (!$referrer) {
            if (Yii::$app->user->identity->role === 'admin') {
                $referrer = ['site/dashboard-admin'];
            } else {
                $referrer = ['site/dashboard-member'];
            }
        }
        return $this->redirect($referrer);
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

    private function checkCardAccess($card)
    {
        if (Yii::$app->user->identity->role === 'admin') {
            return true;
        }

        if (Yii::$app->user->identity->role === 'member') {
            if (!$card->board || !$card->board->project) {
                return false;
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
}