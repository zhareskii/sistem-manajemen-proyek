<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    const ROLE_ADMIN = 'admin';
    const ROLE_MEMBER = 'member';
    
    const STATUS_IDLE = 'idle';
    const STATUS_WORKING = 'working';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'full_name', 'email', 'role'], 'required'],
            [['created_at'], 'safe'],
            [['is_active'], 'integer'],
            [['username'], 'string', 'max' => 50],
            [['password', 'profile_picture'], 'string', 'max' => 255],
            [['full_name', 'email'], 'string', 'max' => 100],
            [['role', 'current_task_status'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'username' => 'Username',
            'password' => 'Password',
            'full_name' => 'Full Name',
            'email' => 'Email',
            'role' => 'Role',
            'profile_picture' => 'Profile Picture',
            'created_at' => 'Created At',
            'current_task_status' => 'Current Task Status',
            'is_active' => 'Is Active',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['user_id' => $id, 'is_active' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->user_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return false;
    }

    /**
     * Validates password
     */
    public function validatePassword($password)
    {
        if (empty($this->password)) {
            Yii::warning("Empty password hash for user: " . $this->username);
            return false;
        }
        
        // For debugging: check the stored hash format
        Yii::info("Stored hash for user {$this->username}: " . substr($this->password, 0, 10) . '...');
        
        // Check if the password is already hashed
        if (strlen($this->password) < 60) { // A proper bcrypt hash is 60 characters
            // If password in database is not hashed, hash it first
            $this->setPassword($this->password);
            $this->save(false); // Save without validation
            Yii::info("Updated password hash for user: " . $this->username);
        }
        
        try {
            $valid = Yii::$app->security->validatePassword($password, $this->password);
            Yii::info("Password validation result for {$this->username}: " . ($valid ? 'success' : 'failed'));
            return $valid;
        } catch (\Exception $e) {
            Yii::error("Password validation error for {$this->username}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sets the password
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Finds user by username
     */
    public static function findByUsername($username)
    {
        $user = static::findOne(['username' => $username]);
        if ($user === null) {
            Yii::warning("User not found: $username");
            return null;
        }
        Yii::info("User found: $username");
        return $user;
    }

    /**
     * Register new user
     */
    public static function registerUser($username, $email, $full_name, $password)
    {
        if (static::findByUsername($username)) {
            Yii::warning("Registration failed: Username already exists: $username");
            return false;
        }

        $user = new static();
        $user->username = $username;
        $user->email = $email;
        $user->full_name = $full_name;
        $user->role = self::ROLE_MEMBER;
        $user->created_at = date('Y-m-d H:i:s');
        $user->is_active = 1;
        
        // Hash password before saving
        $hashedPassword = Yii::$app->security->generatePasswordHash($password);
        $user->password = $hashedPassword;
        
        Yii::info("Registering new user: $username with hash: " . substr($hashedPassword, 0, 10) . '...');
        
        if ($user->save()) {
            Yii::info("Successfully registered user: $username");
            return true;
        } else {
            Yii::error("Failed to register user: $username. Errors: " . print_r($user->errors, true));
            return false;
        }
    }


    /**
     * Set user status to working
     * @return bool
     */
    public function setWorking()
    {
        $this->current_task_status = self::STATUS_WORKING;
        return $this->save(false, ['current_task_status']);
    }

    /**
     * Set user status to idle
     * @return bool
     */
    public function setIdle()
    {
        $this->current_task_status = self::STATUS_IDLE;
        return $this->save(false, ['current_task_status']);
    }

    /**
     * Check if user is currently working
     * @return bool
     */
    public function isWorking()
    {
        return $this->current_task_status === self::STATUS_WORKING;
    }

    /**
     * Check if user is idle
     * @return bool
     */
    public function isIdle()
    {
        return $this->current_task_status === self::STATUS_IDLE;
    }

    /**
     * Get user's active tracking session
     * @return TimeTracking|null
     */
    public function getActiveTracking()
    {
        return TimeTracking::findOne(['user_id' => $this->user_id, 'is_active' => 1]);
    }

    /**
     * Get today's total tracked minutes
     * @return int
     */
    public function getTodayTrackedMinutes()
    {
        return TimeTracking::getTotalMinutesToday($this->user_id);
    }

    /**
     * Get all tracking sessions for today
     * @return array
     */
    public function getTodayTrackingSessions()
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        return TimeTracking::find()
            ->where(['user_id' => $this->user_id])
            ->andWhere(['>=', 'start_time', "$today 00:00:00"])
            ->andWhere(['<', 'start_time', "$tomorrow 00:00:00"])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }
}
