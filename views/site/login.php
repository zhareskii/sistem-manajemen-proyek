<?php
use yii\helpers\Url;

$this->title = 'Login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->title ?></title>
    <style>
        body {
            background: linear-gradient(90deg, rgb(254,245,172) 60%, rgb(151,210,236) 100%);
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            color: rgb(37,49,109);
        }

        .login-container {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 0 5vw;
        }

        .login-left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-illustration {
            width: 90%;
            max-width: 400px;
        }

        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: rgb(254,245,172);
            border-radius: 30px;
            box-shadow: 0 8px 32px rgba(37,49,109,0.15);
            padding: 40px 40px 30px 40px;
            min-width: 340px;
            max-width: 400px;
        }

        .login-title {
            font-size: 2rem;
            font-weight: bold;
            color: rgb(37,49,109);
            text-align: center;
            margin-bottom: 30px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .login-input {
            padding: 14px 18px;
            border-radius: 20px;
            border: none;
            background: #fff;
            font-size: 1rem;
            color: rgb(37,49,109);
            box-shadow: 0 2px 8px rgba(95,111,148,0.10);
        }

        .login-input:focus {
            outline: 2px solid rgb(151,210,236);
        }

        .login-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
            color: rgb(95,111,148);
            margin-bottom: 10px;
        }

        .login-btn {
            background: rgb(37,49,109);
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            border: none;
            border-radius: 20px;
            padding: 14px 0;
            width: 100%;
            box-shadow: 0 8px 32px rgba(37,49,109,0.15);
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 10px;
        }

        .login-btn:hover {
            background: rgb(95,111,148);
        }

        .login-register {
            text-align: center;
            margin-top: 10px;
            color: rgb(95,111,148);
            font-size: 1rem;
        }

        .login-register a {
            color: rgb(37,49,109);
            font-weight: bold;
            text-decoration: none;
            margin-left: 5px;
        }

        .login-register a:hover {
            color: rgb(151,210,236);
        }

        @media (max-width: 900px) {
            .login-container {
                flex-direction: column;
                padding: 30px 2vw;
            }

            .login-left {
                display: none;
            }

            .login-box {
                min-width: 90vw;
                max-width: 98vw;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
            <div class="login-box">
                <div class="login-title">Login</div>

                <form class="login-form" method="post" action="<?= Url::to(['site/login']) ?>">
                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">

                    <input type="text" 
                           name="LoginForm[username]" 
                           class="login-input" 
                           placeholder="Username/Email" 
                           required>

                    <input type="password" 
                           name="LoginForm[password]" 
                           class="login-input" 
                           placeholder="Password" 
                           required>

                    <br>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <?php if (!empty($error)): ?>
                    <p style="color:red; text-align:center; margin-top:10px;">
                        <?= htmlspecialchars($error) ?>
                    </p>
                <?php endif; ?>

                <div class="login-register">
                    Don't have an account?
                    <a href="<?= Url::to(['site/register']) ?>">Register</a>
                </div>
            </div>
    </div>
</body>
</html>
