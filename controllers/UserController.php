<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\User;

class UserController extends Controller
{
    public function actionAdminUsers()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
            return $this->redirect(['site/login']);
        }

        $users = User::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $userModel = new User();

        return $this->render('@app/views/site/admin/users', [
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
}