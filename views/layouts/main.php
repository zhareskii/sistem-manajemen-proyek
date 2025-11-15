<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<style>
:root {
    --primary: rgb(17, 63, 103);
    --secondary: rgb(52, 105, 154);
    --accent: rgb(88, 160, 200);
    --highlight: rgb(253, 245, 170);
    --white: #fff;
    --light-bg: #f8f9fa;
    --border-gray: #ddd;
    --danger: #dc3545;
}

/* Layout untuk halaman dengan sidebar */
.dashboard-layout {
    display: flex;
    min-height: 100vh;
    background: var(--light-bg);
}

/* Sidebar Styles */
.sidebar {
    background: var(--white);
    color: var(--primary);
    width: 260px;
    min-width: 260px;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 16px rgba(17,63,103,0.08);
    z-index: 1200;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
}

.sidebar-header {
    padding: 30px 25px 20px;
    border-bottom: 1px solid rgba(17,63,103,0.1);
    margin-bottom: 10px;
}

.sidebar .logo {
    font-size: 1.5rem;
    font-weight: bold;
    text-align: center;
    letter-spacing: 1px;
    margin: 0;
    color: var(--primary);
}

.sidebar .menu {
    flex: 1;
    padding: 0 15px;
}

.sidebar .menu a {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    color: var(--primary);
    text-decoration: none;
    font-size: 1rem;
    border-radius: 8px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
    position: relative;
}

.sidebar .menu a i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
}

.sidebar .menu a.active, 
.sidebar .menu a:hover {
    background: rgba(88,160,200,0.12);
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(17,63,103,0.10);
}

.sidebar .logout-section {
    padding: 20px 25px;
    border-top: 1px solid rgba(17,63,103,0.1);
    margin-top: auto;
}

.sidebar .logout-btn {
    background: var(--white);
    color: var(--primary);
    border: 1px solid rgba(17,63,103,0.2);
    padding: 12px 20px;
    border-radius: 8px;
    width: 100%;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.sidebar .logout-btn:hover {
    background: rgba(253,245,170,0.35);
    transform: translateY(-2px);
}

/* Main Content Area */
.main-content {
    flex: 1;
    margin-left: 260px;
    padding: 24px 40px;
    background: var(--light-bg);
    min-height: 100vh;
}

.content-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Timer Sidebar */
.timer-sidebar {
    width: 300px;
    min-width: 300px;
    background: var(--white);
    box-shadow: -2px 0 16px rgba(17,63,103,0.08);
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    z-index: 1001;
    position: fixed;
    right: 0;
    height: 100vh;
}

.timer-header {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 20px;
}

.timer-display {
    font-size: 3.5rem;
    font-weight: bold;
    color: var(--primary);
    margin-bottom: 20px;
}

.timer-controls button {
    background: var(--secondary);
    color: var(--white);
    border: none;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 0 10px;
}

.timer-controls button:hover {
    background: var(--accent);
    transform: scale(1.1);
}

.timer-task {
    margin-top: 30px;
    font-size: 1rem;
    color: var(--secondary);
}

/* Adjust main content padding when timer sidebar is present */
.main-content-with-timer {
    margin-right: 300px;
}

/* Layout untuk halaman tanpa sidebar (landing, login, register) */
.standalone-layout {
    min-height: 100vh;
    background: var(--white);
}

.standalone-content {
    padding: 20px;
}

/* Responsive Design */
@media (max-width: 992px) {
    .timer-sidebar {
        display: none; /* Hide timer sidebar on smaller screens for now */
    }
    .main-content-with-timer {
        margin-right: 0;
    }
}

@media (max-width: 768px) {
    .dashboard-layout {
        flex-direction: column;
    }
    
    .sidebar {
        position: fixed;
        width: 260px;
        height: 100vh;
        min-height: 100vh;
        left: 0;
        top: 0;
    }
    
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
    
    .sidebar .menu {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        padding: 15px;
    }
    
    .sidebar .menu a {
        flex: 1;
        min-width: 120px;
        justify-content: center;
        text-align: center;
        padding: 12px 15px;
    }
    
    .sidebar .menu a i {
        margin-right: 0;
        margin-bottom: 5px;
    }
    
    .sidebar .menu a span {
        display: block;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 15px;
    }
    
    .sidebar .menu {
        flex-direction: column;
    }
    
    .sidebar .menu a {
        min-width: auto;
    }
}

/* Footer */
.footer {
    background: var(--white);
    color: var(--primary);
    padding: 20px 0;
    margin-top: auto;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.footer .copyright {
    margin: 0;
}

.footer .powered-by {
    margin: 0;
    opacity: 0.8;
}

/* Breadcrumb styling */
.breadcrumb {
    background: rgba(255,255,255,1);
    border-radius: 10px;
    padding: 12px 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(17,63,103,0.08);
}

/* Alert styling */
.alert {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 8px rgba(17,63,103,0.08);
    margin-bottom: 25px;
}

.sidebar-user{
    padding: 0px 65px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.sidebar-user .avatar{
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}
.sidebar-user .username{
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--primary);
    cursor: pointer;
}

/* Profile Overlay */
.profile-overlay{
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.35);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}
.profile-overlay.active{display:flex}
.profile-modal{
    background: #fff;
    border-radius: 10px;
    width: 420px;
    max-width: 90vw;
    box-shadow: 0 10px 30px rgba(17,63,103,0.15);
    overflow: hidden;
}
.profile-modal-header{
    padding: 14px 18px;
    font-weight: 700;
    color: var(--primary);
    border-bottom: 1px solid rgba(17,63,103,0.1);
}
.profile-modal-body{padding: 16px 18px; display: grid; gap: 10px}
.profile-field{display:grid; gap:6px}
.profile-field label{font-size:0.85rem; color: var(--secondary)}
.profile-field input{padding:9px 10px; border:1px solid var(--border-gray); border-radius:8px; font-size:0.95rem}
.profile-modal-footer{display:flex; gap:10px; justify-content:flex-end; padding: 12px 18px; border-top:1px solid rgba(17,63,103,0.1)}
.btn{padding:8px 12px; border-radius:8px; border:1px solid var(--border-gray); background:#fff; color:var(--primary); cursor:pointer}
.btn-primary{background: var(--accent); color:#fff; border-color: var(--accent)}
.btn-danger{background: var(--danger); color:#fff; border-color: var(--danger)}

/* Logout Overlay */
.logout-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.35);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2001;
}
.logout-overlay.active { display: flex; }
.logout-modal {
    background: #fff;
    border-radius: 10px;
    width: 380px;
    max-width: 90vw;
    box-shadow: 0 10px 30px rgba(17,63,103,0.15);
    overflow: hidden;
}
.logout-modal-header {
    padding: 18px 20px;
    font-weight: 700;
    color: var(--primary);
    border-bottom: 1px solid rgba(17,63,103,0.1);
    text-align: center;
}
.logout-modal-body {
    padding: 25px 20px;
    text-align: center;
    font-size: 1rem;
    color: var(--secondary);
}
.logout-modal-footer {
    display: flex;
    gap: 12px;
    justify-content: center;
    padding: 15px 20px;
    border-top: 1px solid rgba(17,63,103,0.1);
}
.btn-cancel {
    background: var(--white);
    color: var(--primary);
    border: 1px solid rgba(17,63,103,0.2);
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}
.btn-confirm {
    background: rgba(255, 23, 23, 1);
    color: var(--white);
    border: 1px solid rgba(0, 0, 0, 1);
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}
.btn-cancel:hover {
    background: rgba(17,63,103,0.05);
}
.btn-confirm:hover {
    background: rgba(211, 19, 19, 1);
    transform: translateY(-2px);
}
</style>

<?php
// Tentukan layout berdasarkan halaman
$isAuthPage = in_array(Yii::$app->controller->action->id, ['login', 'register', 'index']);
$hasSidebar = !Yii::$app->user->isGuest && !$isAuthPage;
$isMember = $hasSidebar && Yii::$app->user->identity->role === 'member';
$isTeamLead = $hasSidebar && \app\models\Project::find()->where(['team_lead_id' => Yii::$app->user->identity->user_id])->exists();
?>

<?php if ($hasSidebar): ?>
<div class="dashboard-layout">
    <!-- Mobile Header -->
    <div class="mobile-header">
        <button id="hamburger-btn" aria-label="Toggle sidebar" aria-controls="sidebar" aria-expanded="false"><i>&#9776;</i></button>
        <div class="mobile-logo"><?= Yii::$app->user->identity->role === 'admin' ? 'Project Manager' : 'Team Member' ?></div>
        <button id="mobile-filter-btn" aria-label="Filter"><i class="bi bi-funnel"></i></button>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <?= Yii::$app->user->identity->role === 'admin' ? 'Administrator' : 'Member' ?>
            </div>
            <div class="sidebar-user">
                <div class="avatar">
                    <?= substr(Yii::$app->user->identity->full_name,0,1) ?>
                </div>
                <div class="username" id="sidebarUsername" onclick="openProfileOverlay()">
                    <?= Html::encode(Yii::$app->user->identity->username) ?>
                </div>
            </div>
        </div>
        
        <div class="menu">
            <?php if (Yii::$app->user->identity->role === 'admin'): ?>
                <!-- Admin Menu -->
                <a href="<?= \yii\helpers\Url::to(['site/dashboard-admin']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'dashboard-admin' ? 'active' : '' ?>">
                    <i>📊</i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/admin-projects']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'admin-projects' ? 'active' : '' ?>">
                    <i>📁</i>
                    <span>Project</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/admin-boards']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'admin-boards' ? 'active' : '' ?>">
                    <i>📋</i>
                    <span>Board</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/admin-users']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'admin-users' ? 'active' : '' ?>">
                    <i>👥</i>
                    <span>User</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/reports-admin']) ?>" 
                    class="<?= Yii::$app->controller->action->id === 'reports-admin' ? 'active' : '' ?>">
                        <i>📈</i>
                        <span>Report</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/submissions-admin']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'submissions-admin' ? 'active' : '' ?>">
                    <i>📨</i>
                    <span>Submission</span>
                </a>
            <?php else: ?>
                <!-- Member Menu -->
                <a href="<?= \yii\helpers\Url::to(['site/dashboard-member']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'dashboard-member' ? 'active' : '' ?>">
                    <i>📊</i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/member-cards']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'member-cards' ? 'active' : '' ?>">
                    <i>🎴</i>
                    <span>Card</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/member-boards']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'member-boards' ? 'active' : '' ?>">
                    <i>📋</i>
                    <span>Board</span>
                </a>
                <a href="<?= isset($c) ? \yii\helpers\Url::to(['site/subtasks', 'card_id' => $c->card_id]) 
                       : \yii\helpers\Url::to(['site/member-subtasks']) ?>"
                class="<?= in_array(Yii::$app->controller->action->id, ['subtasks', 'member-subtasks']) ? 'active' : '' ?>">
                    <i>✅</i>
                    <span>Subtask</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/member-reports']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'member-reports' ? 'active' : '' ?>">
                    <i>📈</i>
                    <span>Report</span>
                </a>
                <a href="<?= \yii\helpers\Url::to(['site/submissions-member']) ?>" 
                   class="<?= Yii::$app->controller->action->id === 'submissions-member' ? 'active' : '' ?>">
                    <i>📨</i>
                    <span>Submission</span>
                </a>
                <?php // Team Lead tidak perlu menu terpisah; gunakan halaman Submission tunggal ?>
            <?php endif; ?>
        </div>
        
        <div class="logout-section">
            <form action="<?= \yii\helpers\Url::to(['site/logout']) ?>" method="post" id="logoutForm">
                <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                <button type="button" class="logout-btn" onclick="openLogoutOverlay()">
                    <i>🚪</i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-container">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget([
                    'links' => $this->params['breadcrumbs'],
                    'options' => ['class' => 'breadcrumb']
                ]) ?>
            <?php endif ?>
            
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Layout tanpa sidebar untuk landing page, login, register -->
<div class="standalone-layout">
    <div class="standalone-content">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>
<?php endif; ?>

<!-- Logout Confirmation Overlay -->
<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-modal">
        <div class="logout-modal-header">Confirmation logout</div>
        <div class="logout-modal-body">
            Are you sure you want to logout?
        </div>
        <div class="logout-modal-footer">
            <button class="btn-cancel" onclick="closeLogoutOverlay()">No</button>
            <button class="btn-confirm" onclick="confirmLogout()">Yes</button>
        </div>
    </div>
</div>

<?php if (!Yii::$app->user->isGuest): ?>
<div class="profile-overlay" id="profileOverlay">
  <div class="profile-modal">
    <div class="profile-modal-header">Edit Profil</div>
    <div class="profile-modal-body">
      <div class="profile-field">
        <label>Username</label>
        <input type="text" id="pf_username" value="<?= Html::encode(Yii::$app->user->identity->username) ?>">
      </div>
      <div class="profile-field">
        <label>Full name</label>
        <input type="text" id="pf_fullname" value="<?= Html::encode(Yii::$app->user->identity->full_name) ?>" disabled>
      </div>
      <div class="profile-field">
        <label>Email</label>
        <input type="email" id="pf_email" value="<?= Html::encode(Yii::$app->user->identity->email) ?>">
      </div>
      <div class="profile-field">
        <label>Password</label>
        <input type="password" id="pf_password" placeholder="Isi untuk mengubah kata sandi">
      </div>
    </div>
    <div class="profile-modal-footer">
      <button class="btn" onclick="closeProfileOverlay()">Cancel</button>
      <button class="btn-primary btn" onclick="submitProfileUpdate()">Save</button>
    </div>
  </div>
</div>

<script>
// Fungsi untuk logout overlay
function openLogoutOverlay() {
    const el = document.getElementById('logoutOverlay');
    if(el) { 
        el.classList.add('active'); 
    }
}

function closeLogoutOverlay() {
    const el = document.getElementById('logoutOverlay');
    if(el) { 
        el.classList.remove('active'); 
    }
}

function confirmLogout() {
    // Submit form logout
    const logoutForm = document.querySelector('form[action*="logout"]');
    if(logoutForm) {
        logoutForm.submit();
    } else {
        // Fallback: redirect to landing page
        window.location.href = '<?= \yii\helpers\Url::to(['site/index']) ?>';
    }
}

// Event listener untuk tombol logout
document.addEventListener('DOMContentLoaded', function() {
    const logoutButtons = document.querySelectorAll('.logout-btn');
    logoutButtons.forEach(button => {
        // Hentikan event default
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openLogoutOverlay();
        });
    });
    
    // Close overlay ketika klik di luar modal
    const logoutOverlay = document.getElementById('logoutOverlay');
    if(logoutOverlay) {
        logoutOverlay.addEventListener('click', function(e) {
            if(e.target === logoutOverlay) {
                closeLogoutOverlay();
            }
        });
    }
});
</script>

<script>
(function(){
  const startUrl = '<?= \yii\helpers\Url::to(['site/start-activity']) ?>';
  const pingUrl = '<?= \yii\helpers\Url::to(['site/ping-activity']) ?>';
  const endUrl = '<?= \yii\helpers\Url::to(['site/end-activity']) ?>';
  const tokenEl = document.querySelector('meta[name="csrf-token"]');
  const csrf = tokenEl ? tokenEl.getAttribute('content') : null;

  function post(url, body){
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(csrf ? {'X-CSRF-Token': csrf} : {})
      },
      body: JSON.stringify(body||{}),
      credentials: 'same-origin',
      keepalive: true
    }).catch(()=>{});
  }

  document.addEventListener('DOMContentLoaded', function(){
    post(startUrl);
  });

  let pingTimer = setInterval(function(){ post(pingUrl); }, 30000);
  window.addEventListener('beforeunload', function(){
    post(endUrl);
    if (pingTimer) clearInterval(pingTimer);
  });
})();
</script>

<script>
function openProfileOverlay(){
  const el = document.getElementById('profileOverlay');
  if(el){ el.classList.add('active'); }
}
function closeProfileOverlay(){
  const el = document.getElementById('profileOverlay');
  if(el){ el.classList.remove('active'); }
}
function submitProfileUpdate(){
  const u = document.getElementById('pf_username').value;
  const e = document.getElementById('pf_email').value;
  const p = document.getElementById('pf_password').value;
  const tokenEl = document.querySelector('meta[name="csrf-token"]');
  const csrf = tokenEl ? tokenEl.getAttribute('content') : '';
  const form = new URLSearchParams();
  form.append('User[username]', u);
  form.append('User[email]', e);
  if(p && p.length > 0){ form.append('User[password]', p); }
  form.append('_csrf', csrf);
  fetch('<?= \yii\helpers\Url::to(['site/update-my-profile']) ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: form.toString()
  }).then(r=>r.json()).then(d=>{
    if(d && d.success){
      const nameEl = document.getElementById('sidebarUsername');
      if(nameEl && d.user && d.user.username){ nameEl.textContent = d.user.username; }
      const emailEl = document.getElementById('pf_email');
      if(emailEl && d.user && d.user.email){ emailEl.value = d.user.email; }
      document.getElementById('pf_password').value = '';
      closeProfileOverlay();
    } else {
      alert(d && d.message ? d.message : 'Gagal menyimpan profil');
    }
  }).catch(()=>{ alert('Gagal menyimpan profil'); });
}
</script>
<?php endif; ?>

<?php if ($isMember): ?>
<script>
(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const timerDisplay = document.getElementById('timer-display');
    const startBtn = document.getElementById('start-timer-btn');
    const stopBtn = document.getElementById('stop-timer-btn');
    const taskDisplay = document.getElementById('timer-task');

    let timerInterval = null;
    let activeTimer = null;

    async function fetchActiveTimer() {
        try {
            const response = await fetch('<?= \yii\helpers\Url::to(["site/get-active-timer"]) ?>', {
                method: 'GET',
                headers: {
                    'X-CSRF-Token': csrf
                }
            });
            const data = await response.json();
            if (data.success && data.timer) {
                activeTimer = data.timer;
                taskDisplay.textContent = activeTimer.subtask_name;
                startTimer(new Date(activeTimer.start_time));
            } else {
                taskDisplay.textContent = 'No active task';
            }
        } catch (error) {
            console.error('Error fetching active timer:', error);
        }
    }

    function formatTime(seconds) {
        const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
        const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        return `${h}:${m}:${s}`;
    }

    function startTimer(startTime) {
        if (timerInterval) {
            clearInterval(timerInterval);
        }

        timerInterval = setInterval(() => {
            const now = new Date();
            const elapsed = Math.floor((now - startTime) / 1000);
            timerDisplay.textContent = formatTime(elapsed);
        }, 1000);
    }

    startBtn.addEventListener('click', async () => {
        // This is a placeholder. In a real app, you'd likely have a way
        // for the user to select a subtask to time.
        const subtaskId = prompt("Enter Subtask ID to start timer:");
        if (!subtaskId) return;

        try {
            const response = await fetch('<?= \yii\helpers\Url::to(["site/start-time-tracking"]) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify({ subtask_id: subtaskId })
            });
            const data = await response.json();
            if (data.success) {
                fetchActiveTimer();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error starting timer:', error);
        }
    });

    stopBtn.addEventListener('click', async () => {
        if (!activeTimer) {
            alert('No active timer to stop.');
            return;
        }

        try {
            const response = await fetch('<?= \yii\helpers\Url::to(["site/stop-time-tracking"]) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf
                }
            });
            const data = await response.json();
            if (data.success) {
                clearInterval(timerInterval);
                timerDisplay.textContent = '00:00:00';
                taskDisplay.textContent = 'No active task';
                activeTimer = null;
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error stopping timer:', error);
        }
    });

    document.addEventListener('DOMContentLoaded', fetchActiveTimer);
})();
</script>
<?php endif; ?>

<?php if ($hasSidebar): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var hamburgerBtn = document.getElementById('hamburger-btn');
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebar-overlay');
  var dashboardLayout = document.querySelector('.dashboard-layout');

  function openSidebar() {
    if (!sidebar || !dashboardLayout) return;
    sidebar.classList.add('active');
    dashboardLayout.classList.add('sidebar-active');
    if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'true');
  }

  function closeSidebar() {
    if (!sidebar || !dashboardLayout) return;
    sidebar.classList.remove('active');
    dashboardLayout.classList.remove('sidebar-active');
    if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'false');
  }

  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      if (sidebar && sidebar.classList.contains('active')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  }

  if (overlay) {
    overlay.addEventListener('click', function() {
      closeSidebar();
    });
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeSidebar();
    }
  });
});
</script>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>