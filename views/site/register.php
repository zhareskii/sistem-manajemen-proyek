<?php
use yii\helpers\Url;
$this->title = 'Register Account';
?>
<style>
body {
    background: linear-gradient(90deg, rgb(254,245,172) 60%, rgb(151,210,236) 100%);
    font-family: 'Poppins', Arial, sans-serif;
    margin: 0;
    color: rgb(37,49,109);
}
.register-container {
    display: flex;
    min-height: 100vh;
    align-items: center;
    justify-content: center;
    padding: 0 5vw;
}
.register-left {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
.register-illustration {
    width: 90%;
    max-width: 400px;
}
.register-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
.register-box {
    background: rgb(254,245,172);
    border-radius: 30px;
    box-shadow: 0 8px 32px rgba(37,49,109,0.15);
    padding: 40px 40px 30px 40px;
    min-width: 340px;
    max-width: 400px;
}
.register-title {
    font-size: 2rem;
    font-weight: bold;
    color: rgb(37,49,109);
    text-align: center;
    margin-bottom: 30px;
}
.register-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.register-input {
    padding: 14px 18px;
    border-radius: 20px;
    border: none;
    background: #fff;
    font-size: 1rem;
    color: rgb(37,49,109);
    box-shadow: 0 2px 8px rgba(95,111,148,0.10);
}
.register-input:focus {
    outline: 2px solid rgb(151,210,236);
}
.register-btn {
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
.register-btn:hover {
    background: rgb(95,111,148);
}
.register-login {
    text-align: center;
    margin-top: 10px;
    color: rgb(95,111,148);
    font-size: 1rem;
}
.register-login a {
    color: rgb(37,49,109);
    font-weight: bold;
    text-decoration: none;
    margin-left: 5px;
}
.register-login a:hover {
    color: rgb(151,210,236);
}
@media (max-width: 900px) {
    .register-container {
        flex-direction: column;
        padding: 30px 2vw;
    }
    .register-left {
        display: none;
    }
    .register-box {
        min-width: 90vw;
        max-width: 98vw;
    }
}
</style>
<div class="register-container">
        <div class="register-box">
            <div class="register-title">Registrasi Akun</div>
            <form class="register-form" method="post" action="<?= Url::to(['site/register']) ?>">
                <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                <input type="text" name="username" class="register-input" placeholder="Username" required />
                <input type="email" name="email" class="register-input" placeholder="Email" required />
                <input type="text" name="full_name" class="register-input" placeholder="Nama Lengkap" required />
                <input type="password" name="password" class="register-input" placeholder="Password" required />
                <button type="submit" class="register-btn">Registrasi</button>
            </form>
            <div class="register-login">
                Already have an account?
                <a href="<?= Url::to(['site/login']) ?>">Login</a>
            </div>
        </div>
</div>
