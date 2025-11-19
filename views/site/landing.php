<?php
use yii\helpers\Url;
$this->title = 'Selamat datang di Z\'s Manager';
?>
<style>
body {
    background: rgb(254, 245, 172);
    font-family: 'Poppins', Arial, sans-serif;
    margin: 0;
    color: rgb(37, 49, 109);
}
.landing-container {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    justify-content: flex-start;
    min-height: 100vh;
    padding: 60px 5vw;
}
.landing-left {
    flex: 1;
    margin-right: 40px;
}
.landing-title {
    font-size: 4vw;
    font-weight: bold;
    color: rgb(37, 49, 109);
    margin-bottom: 20px;
}
.landing-desc {
    font-size: 1.5vw;
    color: rgb(95, 111, 148);
    margin-bottom: 40px;
}
.landing-btn {
    background: rgb(37, 49, 109);
    color: #fff;
    font-size: 1.3vw;
    font-weight: bold;
    border: none;
    border-radius: 30px;
    padding: 20px 50px;
    box-shadow: 0 8px 32px rgba(37,49,109,0.15);
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 20px;
}
.landing-btn:hover {
    background: rgb(95, 111, 148);
}
.landing-features {
    display: flex;
    flex: 2;
    gap: 40px;
}
.feature-card {
    background: rgb(254, 245, 172);
    border-radius: 30px;
    box-shadow: 0 8px 32px rgba(151,210,236,0.15);
    padding: 40px 30px;
    text-align: center;
    flex: 1;
    min-width: 250px;
}
.feature-icon {
    background: rgb(151, 210, 236);
    border-radius: 50%;
    width: 90px;
    height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px auto;
    box-shadow: 0 4px 16px rgba(95,111,148,0.10);
}
.feature-title {
    font-size: 2vw;
    font-weight: bold;
    color: rgb(37, 49, 109);
    margin-bottom: 15px;
}
.feature-desc {
    font-size: 1.1vw;
    color: rgb(95, 111, 148);
}
@media (max-width: 900px) {
    .landing-container {
        flex-direction: column;
        padding: 30px 2vw;
    }
    .landing-features {
        flex-direction: column;
        gap: 20px;
    }
    .landing-title {
        font-size: 7vw;
    }
    .feature-title {
        font-size: 4vw;
    }
}
</style>
<div class="landing-container">
    <div class="landing-left">
        <div class="landing-title">Welcome to<br>Z's Manager</div>
        <div class="landing-desc">
            A project management application that helps teams collaborate, track progress, and complete projects on time.
        </div>
        <a href="<?= Url::to(['site/login']) ?>">
            <button class="landing-btn">Get Started</button>
        </a>
    </div>
    <div class="landing-features">
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="48" height="48" fill="rgb(37,49,109)"><rect x="10" y="18" width="28" height="18" rx="4"/><rect x="16" y="12" width="16" height="8" rx="2"/></svg>
            </div>
            <div class="feature-title">Project</div>
            <div class="feature-desc">Create, organize, and track your projects in a clean and intuitive workspace designed to boost productivity.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="48" height="48" fill="rgb(37,49,109)"><rect x="12" y="14" width="24" height="4" rx="2"/><rect x="12" y="22" width="24" height="4" rx="2"/><rect x="12" y="30" width="24" height="4" rx="2"/></svg>
            </div>
            <div class="feature-title">Manage Task</div>
            <div class="feature-desc">Easily assign, prioritize, and manage tasks with real-time collaboration and progress tracking.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="48" height="48" fill="rgb(37,49,109)"><circle cx="24" cy="24" r="18"/><rect x="22" y="12" width="4" height="12" rx="2" fill="#fff"/><rect x="22" y="26" width="4" height="10" rx="2" fill="#fff"/></svg>
            </div>
            <div class="feature-title">Time Tracking</div>
            <div class="feature-desc">Keep an accurate log of time spent on tasks to optimize workflows and improve efficiency.</div>
        </div>
    </div>
</div>
