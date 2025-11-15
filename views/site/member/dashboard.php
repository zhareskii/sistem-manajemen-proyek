<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Project[] $projects */
/** @var int $assignedCardsCount */
/** @var int $completedSubtasksCount */
/** @var app\models\Comment[] $recentComments */
/** @var app\models\HelpRequest[] $pendingHelpRequests */
/** @var app\models\TimeTracking $currentTracking */
/** @var int $totalMinutesToday */

$this->title = 'Dashboard Member';

// Force asset publishing
Yii::$app->assetManager->forceCopy = true;
?>

<style>
:root {
    --color-dark-blue: rgb(17, 63, 103);
    --color-medium-blue: rgb(52, 105, 154);
    --color-light-blue: rgb(88, 160, 200);
    --color-yellow: rgb(253, 245, 170);
    --color-white: #ffffff;
    --color-light-gray: #f8f9fa;
    --color-border: #e9ecef;
    --color-text-light: #666666;
}

body {
    background-color: var(--color-white);
    margin: 0;
    padding: 0;
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.header-info h1 {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--color-dark-blue);
    margin: 0;
}

.header-info p {
    color: var(--color-medium-blue);
    font-size: 1rem;
    margin: 5px 0 0 0;
}

/* Main Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 30px;
    margin-bottom: 30px;
    align-items: start;
}

/* Stats Cards - 2 banjar 2 baris */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: repeat(2, auto);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, var(--color-medium-blue), var(--color-light-blue));
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(17, 63, 103, 0.15);
    color: white;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200px;
    height: 200px;
    background: rgba(253, 245, 170, 0.1);
    border-radius: 50%;
    pointer-events: none;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(17, 63, 103, 0.25);
}

.stat-content {
    position: relative;
    z-index: 1;
    width: 100%;
}

.stat-number {
    font-size: 2.8rem;
    font-weight: 800;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-label {
    font-size: 0.95rem;
    font-weight: 600;
    opacity: 0.95;
}

/* Timer Card */
.timer-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(17, 63, 103, 0.15);
    padding: 30px;
    text-align: center;
    border-top: 5px solid var(--color-medium-blue);
    margin-bottom: 30px;
}

.timer-card h3 {
    color: var(--color-dark-blue);
    font-size: 1.3rem;
    margin: 0 0 20px 0;
    font-weight: 700;
}

/* Timer Display Styles */
.timer-display {
    margin-bottom: 25px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 12px;
    border: 2px solid var(--color-light-blue);
}

.timer-display .time {
    font-size: 2.8rem;
    font-weight: 800;
    color: var(--color-dark-blue);
    font-family: 'Courier New', monospace;
    letter-spacing: 3px;
    margin-bottom: 8px;
}

.timer-display .status {
    font-size: 1rem;
    color: var(--color-text-light);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.status.working {
    color: #4caf50;
    font-weight: 700;
}

.status.idle {
    color: var(--color-text-light);
}

/* Analog Clock */
.analog-clock {
    width: 280px;
    height: 280px;
    margin: 0 auto;
    position: relative;
    background: radial-gradient(circle, var(--color-yellow), #f5f5f5);
    border-radius: 50%;
    box-shadow: 
        inset 0 2px 5px rgba(0,0,0,0.1),
        0 8px 20px rgba(17, 63, 103, 0.2);
    border: 8px solid var(--color-light-blue);
}

.clock-face {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.clock-center {
    width: 20px;
    height: 20px;
    background: var(--color-dark-blue);
    border-radius: 50%;
    position: absolute;
    z-index: 10;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.clock-hand {
    position: absolute;
    bottom: 50%;
    left: 50%;
    transform-origin: bottom center;
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.hour-hand {
    width: 6px;
    height: 80px;
    background: var(--color-dark-blue);
    margin-left: -3px;
}

.minute-hand {
    width: 4px;
    height: 110px;
    background: var(--color-medium-blue);
    margin-left: -2px;
}

.second-hand {
    width: 2px;
    height: 120px;
    background: var(--color-light-blue);
    margin-left: -1px;
}

.clock-number {
    position: absolute;
    width: 100%;
    height: 100%;
    text-align: center;
    font-size: 18px;
    font-weight: 700;
    color: var(--color-dark-blue);
}

.clock-number span {
    display: inline-block;
    position: absolute;
    left: 50%;
    width: 30px;
    transform: translateX(-50%);
}

/* Play/Pause Button at Center */
.timer-controls {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 15;
}

.play-pause-btn {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-medium-blue), var(--color-light-blue));
    border: none;
    color: white;
    font-size: 32px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(17, 63, 103, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 20;
}

.play-pause-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(17, 63, 103, 0.4);
}

.play-pause-btn:active {
    transform: scale(0.95);
}

.play-pause-btn.active {
    background: linear-gradient(135deg, #f44336, #da190b);
}

/* Calendar */
.calendar-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(17, 63, 103, 0.1);
    padding: 20px;
}

.calendar-card h4 {
    color: var(--color-dark-blue);
    font-size: 1.1rem;
    margin: 0 0 15px 0;
    font-weight: 700;
}

.mini-calendar {
    font-size: 0.85rem;
}

.mini-calendar table {
    width: 100%;
    border-collapse: collapse;
}

.mini-calendar th {
    color: var(--color-light-blue);
    font-weight: 700;
    padding: 8px 4px;
    border-bottom: 2px solid var(--color-light-blue);
}

.mini-calendar td {
    text-align: center;
    padding: 8px 4px;
    color: var(--color-text-light);
}

.mini-calendar td.today {
    background: linear-gradient(135deg, var(--color-yellow), #ffeaa7);
    color: var(--color-dark-blue);
    font-weight: 700;
    border-radius: 8px;
}

.mini-calendar td.other-month {
    color: #ccc;
}

/* Projects Section */
.dashboard-section {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(17, 63, 103, 0.1);
    padding: 30px;
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--color-dark-blue);
    margin: 0 0 25px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 2px solid var(--color-light-blue);
    padding-bottom: 15px;
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.project-card {
    background: linear-gradient(135deg, var(--color-light-gray), #ffffff);
    border-radius: 12px;
    padding: 20px;
    border-left: 5px solid var(--color-medium-blue);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.project-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: var(--color-yellow);
    border-radius: 0 0 0 100%;
    opacity: 0.15;
    pointer-events: none;
}

.project-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(17, 63, 103, 0.2);
}

.project-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--color-dark-blue);
    margin-bottom: 12px;
}

.project-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: var(--color-text-light);
    margin-bottom: 15px;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.badge-active {
    background: rgba(76, 175, 80, 0.2);
    color: #2e7d32;
}

.badge-planning {
    background: rgba(253, 245, 170, 0.5);
    color: var(--color-dark-blue);
}

.progress-container {
    margin-top: 15px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: var(--color-text-light);
    margin-bottom: 8px;
    font-weight: 600;
}

.progress-bar {
    height: 8px;
    background: var(--color-border);
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--color-medium-blue), var(--color-light-blue));
    transition: width 0.5s ease;
}

/* Activity Section */
.activity-section {
    margin-bottom: 30px;
}

.activity-item {
    background: var(--color-light-gray);
    padding: 18px;
    border-radius: 12px;
    margin-bottom: 15px;
    border-left: 4px solid var(--color-light-blue);
    transition: all 0.3s ease;
}

.activity-item:hover {
    transform: translateX(5px);
    box-shadow: 0 6px 15px rgba(17, 63, 103, 0.15);
}

.activity-item strong {
    color: var(--color-dark-blue);
    font-weight: 700;
}

.activity-meta {
    font-size: 0.8rem;
    color: var(--color-text-light);
    margin-top: 8px;
}

.issue-preview {
    background: rgba(253, 245, 170, 0.6);
    padding: 12px;
    border-radius: 8px;
    margin-top: 10px;
    font-size: 0.85rem;
    color: var(--color-dark-blue);
    border-left: 3px solid var(--color-yellow);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: var(--color-text-light);
}

.empty-state-icon {
    font-size: 3.5rem;
    margin-bottom: 15px;
    opacity: 0.4;
}

.empty-state p {
    margin: 8px 0;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-top: 25px;
}

.action-btn {
    background: linear-gradient(135deg, var(--color-medium-blue), var(--color-light-blue));
    color: white;
    border: none;
    padding: 14px 20px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
    text-align: center;
    box-shadow: 0 4px 15px rgba(17, 63, 103, 0.2);
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(17, 63, 103, 0.3);
    color: white;
    text-decoration: none;
}

@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        text-align: center;
    }
    
    .header-info h1 {
        font-size: 1.8rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        grid-template-rows: auto;
    }
    
    .projects-grid {
        grid-template-columns: 1fr;
    }
    
    .analog-clock {
        width: 220px;
        height: 220px;
    }
    
    .play-pause-btn {
        width: 60px;
        height: 60px;
        font-size: 28px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .stat-card {
        padding: 20px;
        min-height: 100px;
    }
    
    .stat-number {
        font-size: 2.2rem;
    }
    
    .stat-label {
        font-size: 0.85rem;
    }
}
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-info">
            <h1>Welcome, <?= Html::encode(Yii::$app->user->identity->full_name) ?>!</h1>
            <p>Your productivity dashboard</p>
        </div>
    </div>

    <!-- Main Grid: Content + Timer -->
    <div class="dashboard-grid">
        <!-- Left Column - Scrollable Content -->
        <div>
            <!-- Statistics Cards - 2 banjar 2 baris -->
            <div class="stats-grid">
                <!-- Baris 1 -->
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-number"><?= count($projects) ?></div>
                        <div class="stat-label">Active Projects</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-number"><?= $assignedCardsCount ?></div>
                        <div class="stat-label">Cards Assigned</div>
                    </div>
                </div>
                
                <!-- Baris 2 -->
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-number"><?= $completedSubtasksCount ?></div>
                        <div class="stat-label">Completed Subtasks</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-number"><?= $totalMinutesToday ?>m</div>
                        <div class="stat-label">Worked Minutes Today</div>
                    </div>
                </div>
            </div>

            <!-- Projects Section -->
            <div class="dashboard-section">
                <h3 class="section-title">🚀 My Projects</h3>
                
                <?php if (!empty($projects)): ?>
                    <div class="projects-grid">
                        <?php foreach ($projects as $project): ?>
                            <?php $progress = $project->progress_percentage ?? 0; ?>
                            <div class="project-card" onclick="window.location.href='<?= Url::to(['site/member-cards']) ?>'">
                                <div class="project-name"><?= Html::encode($project->project_name) ?></div>
                                <div class="project-meta">
                                    <span class="status-badge badge-<?= $project->status ?? 'planning' ?>">
                                        <?= strtoupper($project->status ?? 'Planning') ?>
                                    </span>
                                    <span><?= $project->difficulty_level ?? 'Medium' ?></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-label">
                                        <span>Progress</span>
                                        <span><?= $progress ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <p>No active projects assigned to you</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity Section -->
            <div class="dashboard-section">
                <h3 class="section-title">📋 Recent Activities</h3>
                
                <?php if (!empty($recentActivities) || !empty($latestComment) || !empty($latestBlocker)): ?>
                    <?php if (!empty($recentActivities)): ?>
                        <div class="activity-section">
                            <?php foreach ($recentActivities as $act): ?>
                                <div class="activity-item">
                                    <?php if ($act['type'] === 'card'): ?>
                                        created card "<?= Html::encode($act['title']) ?>"
                                    <?php else: ?>
                                        created subtask "<?= Html::encode($act['title']) ?>"
                                    <?php endif; ?>
                                    <div class="activity-meta">
                                        <?= date('M j, H:i', strtotime($act['created_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($latestComment)): ?>
                        <div class="activity-section">
                            <div class="activity-item">
                                latest comment on subtask "<?= Html::encode($latestComment->subtask ? $latestComment->subtask->subtask_title : '-') ?>":
                                <div class="issue-preview">
                                    <?= Html::encode(mb_substr($latestComment->comment_text, 0, 120)) ?>
                                </div>
                                <div class="activity-meta">
                                    <?= date('M j, H:i', strtotime($latestComment->created_at)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($latestBlocker)): ?>
                        <div class="activity-section">
                            <div class="activity-item">
                                blocker <?= Html::encode($latestBlocker->status !== 'pending' ? 'fixed' : 'completed') ?> pada subtask "<?= Html::encode($latestBlocker->subtask ? $latestBlocker->subtask->subtask_title : '-') ?>"
                                <div class="issue-preview">
                                    <?= Html::encode(mb_substr($latestBlocker->issue_description, 0, 100)) ?>
                                </div>
                                <div class="activity-meta">
                                    <?= date('M j, H:i', strtotime($latestBlocker->resolved_at ?: $latestBlocker->created_at)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">✨</div>
                        <p>No recent activities</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column - Normal Scroll (Timer + Calendar) -->
        <div>
            <!-- Timer Card -->
            <div class="timer-card">
                <h3>⏱️ Productivity Timer</h3>
                
                <!-- Timer Display -->
                <div class="timer-display">
                    <div class="time" id="timerDisplay">00:00:00</div>
                    <div class="status idle" id="statusDisplay">IDLE</div>
                </div>

                <!-- Analog Clock with Single Control Button -->
                <div class="analog-clock">
                    <div class="clock-face">
                        <!-- Clock Numbers -->
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <div class="clock-number" style="transform: rotate(<?= $i * 30 ?>deg);">
                                <span style="transform: rotate(<?= -$i * 30 ?>deg);"><?= $i ?></span>
                            </div>
                        <?php endfor; ?>
                        
                        <div class="clock-hand hour-hand" id="hourHand"></div>
                        <div class="clock-hand minute-hand" id="minuteHand"></div>
                        <div class="clock-hand second-hand" id="secondHand"></div>
                        
                        <!-- Single Play/Pause Button at Center -->
                        <div class="timer-controls">
                            <button class="play-pause-btn" id="playPauseBtn" type="button">
                                <span id="btnIcon">▶</span>
                            </button>
                        </div>
                        
                        <div class="clock-center"></div>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="calendar-card">
                <h4>📅 Calendar</h4>
                <div class="mini-calendar">
                    <?php
                    $today = date('Y-m-d');
                    $currentMonth = date('n');
                    $currentYear = date('Y');
                    
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
                    $firstDay = date('N', strtotime("$currentYear-$currentMonth-01"));
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Sun</th>
                                <th>Mon</th>
                                <th>Tue</th>
                                <th>Wed</th>
                                <th>Thu</th>
                                <th>Fri</th>
                                <th>Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php for ($i = 1; $i < $firstDay; $i++): ?>
                                    <td class="other-month">-</td>
                                <?php endfor; ?>
                                
                                <?php 
                                $day = 1;
                                $cellCount = $firstDay - 1;
                                while ($day <= $daysInMonth): 
                                    $dateStr = "$currentYear-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                                    $isToday = ($dateStr === $today);
                                ?>
                                    <td class="<?= $isToday ? 'today' : '' ?>">
                                        <?= $day ?>
                                    </td>
                                    <?php 
                                    $cellCount++;
                                    if ($cellCount % 7 === 0 && $day < $daysInMonth): 
                                        echo '</tr><tr>';
                                    endif;
                                    $day++;
                                endwhile; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript tetap sama seperti sebelumnya -->
<script>
// Global timer state variables
let timerInterval = null;
let elapsedSeconds = 0;
let isTimerRunning = false;
let trackingId = null;
let sessionStartTime = null;

// Helper: get CSRF token from meta or hidden input
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.getAttribute('content')) return meta.getAttribute('content');
    const input = document.querySelector('input[name="_csrf"]');
    return input ? input.value : '';
}

// Initialize clock hands based on device time
function updateClockHands() {
    const now = new Date();
    const hours = now.getHours() % 12;
    const minutes = now.getMinutes();
    const seconds = now.getSeconds();
    const milliseconds = now.getMilliseconds();
    
    // Calculate exact angles including milliseconds for smooth movement
    const hourAngle = (hours * 30) + (minutes * 0.5) + (seconds * 0.00833);
    const minuteAngle = (minutes * 6) + (seconds * 0.1) + (milliseconds * 0.0001667);
    const secondAngle = (seconds * 6) + (milliseconds * 0.006);
    
    const hourHand = document.getElementById('hourHand');
    const minuteHand = document.getElementById('minuteHand');
    const secondHand = document.getElementById('secondHand');
    
    if (hourHand) hourHand.style.transform = `rotate(${hourAngle}deg)`;
    if (minuteHand) minuteHand.style.transform = `rotate(${minuteAngle}deg)`;
    if (secondHand) secondHand.style.transform = `rotate(${secondAngle}deg)`;
}

// Update timer display
function updateTimerDisplay() {
    const timerDisplay = document.getElementById('timerDisplay');
    if (!timerDisplay) return;
    
    const hours = Math.floor(elapsedSeconds / 3600);
    const minutes = Math.floor((elapsedSeconds % 3600) / 60);
    const seconds = elapsedSeconds % 60;
    
    timerDisplay.textContent = 
        String(hours).padStart(2, '0') + ':' +
        String(minutes).padStart(2, '0') + ':' +
        String(seconds).padStart(2, '0');
}

// Update status display with message and class
function updateStatusDisplay(message, className = 'idle') {
    const statusDisplay = document.getElementById('statusDisplay');
    if (statusDisplay) {
        statusDisplay.textContent = message;
        statusDisplay.className = 'status ' + className;
    }
}

// Update task status via AJAX
function updateTaskStatus(status) {
    fetch('<?= Url::to(['site/update-task-status']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': getCsrfToken()
        },
        body: 'status=' + encodeURIComponent(status) + '&_csrf=' + encodeURIComponent(getCsrfToken())
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            console.error('Failed to update task status:', data.message);
        }
    })
    .catch(error => console.error('Error updating task status:', error));
}

// Start timer function
function startTimer() {
    if (timerInterval) {
        console.log('Timer already running');
        return;
    }
    
    isTimerRunning = true;
    sessionStartTime = new Date();
    
    timerInterval = setInterval(() => {
        elapsedSeconds++;
        updateTimerDisplay();
    }, 1000);
    
    // Update UI
    const playPauseBtn = document.getElementById('playPauseBtn');
    const btnIcon = document.getElementById('btnIcon');
    
    if (playPauseBtn) playPauseBtn.classList.add('active');
    if (btnIcon) btnIcon.textContent = '⏸';
    updateStatusDisplay('WORKING...', 'working');
    
    // Update task status to working
    updateTaskStatus('working');
    
    // Start time tracking session only if we don't have an active tracking ID
    // This prevents creating duplicate tracking when resuming from existing session
    if (!trackingId) {
        startTimeTracking();
    } else {
        console.log('Resuming existing tracking session:', trackingId);
    }
    
    console.log('Timer started');
}

// Stop timer function
function stopTimer() {
    if (!timerInterval) {
        console.log('Timer not running');
        return;
    }
    
    isTimerRunning = false;
    clearInterval(timerInterval);
    timerInterval = null;
    
    // Calculate total minutes from elapsed seconds
    const totalMinutes = Math.floor(elapsedSeconds / 60);
    
    // Reset timer display
    elapsedSeconds = 0;
    updateTimerDisplay();
    
    // Update UI
    const playPauseBtn = document.getElementById('playPauseBtn');
    const btnIcon = document.getElementById('btnIcon');
    
    if (playPauseBtn) playPauseBtn.classList.remove('active');
    if (btnIcon) btnIcon.textContent = '▶';
    
    // Update status
    updateStatusDisplay('IDLE - Timer stopped', 'idle');
    
    // Update task status to idle
    updateTaskStatus('idle');
    
    // Stop time tracking session and save duration
    stopTimeTracking(totalMinutes);
    
    console.log('Timer stopped');
}

// Toggle timer (single button functionality)
function toggleTimer() {
    if (isTimerRunning) {
        stopTimer();
    } else {
        startTimer();
    }
}

// Start time tracking session
function startTimeTracking() {
    fetch('<?= Url::to(['site/start-tracking']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': getCsrfToken()
        },
        body: '_csrf=' + encodeURIComponent(getCsrfToken())
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.tracking_id) {
            trackingId = data.tracking_id;
            console.log('Time tracking started:', trackingId);
        } else {
            console.error('Failed to start time tracking:', data.message);
            updateStatusDisplay('WORKING - Warning: Tracking failed to start', 'working');
        }
    })
    .catch(error => {
        console.error('Error starting time tracking:', error);
        updateStatusDisplay('WORKING - Error: ' + error.message, 'working');
    });
}

// Stop time tracking session
function stopTimeTracking(totalMinutes) {
    if (!trackingId) {
        console.log('No tracking ID to stop');
        updateStatusDisplay('IDLE - Timer stopped (no active tracking)', 'idle');
        return;
    }
    
    // Show a brief notification that data is being saved
    updateStatusDisplay('IDLE - Saving data...', 'idle');
    
    fetch('<?= Url::to(['site/stop-tracking']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': getCsrfToken()
        },
        body: 'tracking_id=' + encodeURIComponent(trackingId) + 
              '&duration_minutes=' + encodeURIComponent(totalMinutes) + 
              '&_csrf=' + encodeURIComponent(getCsrfToken())
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Time tracking stopped. Total duration:', totalMinutes, 'minutes');
            trackingId = null;
            
            // Update status display
            updateStatusDisplay('IDLE - Data saved! Refreshing...', 'idle');
            
            // Refresh page to update today's progress with a longer delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            console.error('Failed to stop time tracking:', data.message);
            updateStatusDisplay('IDLE - Warning: Failed to save data: ' + (data.message || 'Unknown error'), 'idle');
        }
    })
    .catch(error => {
        console.error('Error stopping time tracking:', error);
        updateStatusDisplay('IDLE - Error: ' + error.message, 'idle');
    });
}

// Check if there's an active tracking session on page load
function checkActiveSession() {
    fetch('<?= Url::to(['site/get-tracking-status']) ?>')
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.is_tracking) {
            // If there's an active session, resume the timer
            trackingId = data.tracking_id;
            
            // Calculate elapsed seconds using epoch timestamp to avoid timezone ambiguity
            if (data.start_timestamp) {
                const startMs = Number(data.start_timestamp);
                const nowMs = Date.now();
                elapsedSeconds = Math.floor((nowMs - startMs) / 1000);
                if (elapsedSeconds < 0) elapsedSeconds = 0;
            } else if (data.start_time) {
                // Fallback if server doesn't send timestamp
                const startTime = new Date(data.start_time);
                elapsedSeconds = Math.floor((Date.now() - startTime.getTime()) / 1000);
                if (elapsedSeconds < 0) elapsedSeconds = 0;
            }
            
            // Start the timer UI
            startTimer();
            
            console.log('Resumed active tracking session:', trackingId, 'Elapsed:', elapsedSeconds, 'seconds');
        } else {
            console.log('No active tracking session found');
            updateStatusDisplay('IDLE', 'idle');
        }
    })
    .catch(error => {
        console.error('Error checking active session:', error);
        updateStatusDisplay('IDLE - Error: Failed to check active session', 'idle');
    });
}

// Safe DOM element getter with retry mechanism
function getElementWithRetry(selector, maxAttempts = 10, interval = 100) {
    return new Promise((resolve, reject) => {
        let attempts = 0;
        
        function tryGetElement() {
            attempts++;
            const element = document.querySelector(selector);
            
            if (element) {
                resolve(element);
            } else if (attempts < maxAttempts) {
                setTimeout(tryGetElement, interval);
            } else {
                reject(new Error(`Element ${selector} not found after ${maxAttempts} attempts`));
            }
        }
        
        tryGetElement();
    });
}

// Helper: safely add event listener without throwing on null
function safeAddEventListener(el, type, handler) {
    if (!el) {
        console.warn('safeAddEventListener: target element missing for', type);
        return;
    }
    if (typeof el.addEventListener !== 'function') {
        console.warn('safeAddEventListener: target does not support addEventListener');
        return;
    }
    try {
        el.addEventListener(type, handler);
    } catch (e) {
        console.error('safeAddEventListener: failed to attach', type, e);
    }
}

// Initialize timer with safe DOM access
async function initializeTimer() {
    try {
        console.log('Initializing timer...');
        // Prevent duplicate initialization
        if (window.__timerInit__) {
            console.log('Timer already initialized, skipping re-init');
            return;
        }
        window.__timerInit__ = true;
        
        // Wait for essential DOM elements to be ready
        const playPauseBtn = await getElementWithRetry('#playPauseBtn');
        const timerDisplay = await getElementWithRetry('#timerDisplay');
        const statusDisplay = await getElementWithRetry('#statusDisplay');
        const btnIcon = await getElementWithRetry('#btnIcon');
        
        console.log('All timer elements found:', {
            playPauseBtn: !!playPauseBtn,
            timerDisplay: !!timerDisplay,
            statusDisplay: !!statusDisplay,
            btnIcon: !!btnIcon
        });

        // Event Listeners (guard against null and ensure it's an element)
        if (!playPauseBtn) {
            console.warn('Play/Pause button not found; skipping listener attachment');
        } else {
            safeAddEventListener(playPauseBtn, 'click', toggleTimer);
        }

        // Initialize displays
        updateClockHands();
        updateTimerDisplay();
        
        // Check for active session
        checkActiveSession();

        // Update clock hands every 50ms for smooth movement
        setInterval(updateClockHands, 50);
        
        console.log('Timer initialized successfully');
        
    } catch (error) {
        console.error('Failed to initialize timer:', error);
        // Fallback: try to initialize with basic functionality
        setTimeout(() => {
            const fallbackBtn = document.getElementById('playPauseBtn');
            if (!fallbackBtn) {
                console.warn('Fallback button not found; skipping listener attachment');
            } else {
                safeAddEventListener(fallbackBtn, 'click', toggleTimer);
                console.log('Timer initialized with fallback method');
            }
        }, 1000);
    }
}

// Initialize when DOM is fully ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded, starting timer initialization...');
    initializeTimer();
});

// Fallback initialization if DOMContentLoaded already fired
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    console.log('DOM already ready, initializing timer immediately...');
    initializeTimer();
}

// Handle page visibility change (tab switch) - timer continues running
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        if (isTimerRunning) {
            console.log('Page hidden, timer continues running in background.');
        }
    } else {
        console.log('Page visible again, timer still running.');
        // Re-sync timer display if needed
        if (isTimerRunning && trackingId) {
            checkActiveSession();
        }
    }
});

// Track if user is navigating away (for logout detection)
let isNavigatingAway = false;

// Detect logout link clicks and button clicks
document.addEventListener('click', function(e) {
    const target = e.target.closest('a, button');
    if (target) {
        const href = target.href || target.getAttribute('href') || '';
        const form = target.closest('form');
        
        // Check if it's a logout link or button in logout form
        if (href.includes('/site/logout') || 
            (form && form.action && form.action.includes('logout')) ||
            target.closest('.logout-btn') ||
            target.closest('.logout-section')) {
            isNavigatingAway = true;
            // Stop timer on logout
            if (isTimerRunning && trackingId) {
                const totalMinutes = Math.floor(elapsedSeconds / 60);
                console.log('Logout detected, stopping timer...');
                stopTimeTracking(totalMinutes);
            }
        }
    }
});

// Handle form logout (more reliable for POST forms)
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form && (form.action.includes('logout') || form.action.includes('/site/logout'))) {
        isNavigatingAway = true;
        // Stop timer on logout
        if (isTimerRunning && trackingId) {
            const totalMinutes = Math.floor(elapsedSeconds / 60);
            console.log('Logout form submitted, stopping timer...');
            stopTimeTracking(totalMinutes);
        }
    }
});

// Track navigation to detect internal navigation vs close
let isInternalNavigation = false;

// Detect internal navigation (links within the site)
document.addEventListener('click', function(e) {
    const target = e.target.closest('a');
    if (target && target.href) {
        try {
            const url = new URL(target.href, window.location.origin);
            // If it's same origin, it's internal navigation
            if (url.origin === window.location.origin && !target.href.includes('logout')) {
                isInternalNavigation = true;
                // Reset flag after a short delay
                setTimeout(() => {
                    isInternalNavigation = false;
                }, 1000);
            }
        } catch (err) {
            // Invalid URL, ignore
        }
    }
});

// Handle page unload (only for actual page close, not navigation)
// Use pagehide for better reliability
window.addEventListener('pagehide', function(e) {
    // If page is being cached (back/forward navigation), don't stop timer
    if (e.persisted) {
        console.log('Page cached, timer continues running.');
        return;
    }
    
    // If user is navigating away (logout), timer already stopped
    if (isNavigatingAway) {
        console.log('User logging out, timer already stopped.');
        return;
    }
    
    // If it's internal navigation, don't stop timer
    if (isInternalNavigation) {
        console.log('Internal navigation detected, timer continues running.');
        return;
    }
    
    // For actual page close (browser/tab close), stop the timer
    if (isTimerRunning && trackingId) {
        console.log('Page closing (browser/tab close), stopping timer...');
        const totalMinutes = Math.floor(elapsedSeconds / 60);
        
        // Use sendBeacon for reliable data sending on page close
        const formData = new FormData();
        formData.append('tracking_id', trackingId);
        formData.append('duration_minutes', totalMinutes);
        formData.append('_csrf', getCsrfToken());
        
        navigator.sendBeacon(
            '<?= Url::to(['site/stop-tracking']) ?>', 
            formData
        );
    }
});

// Handle beforeunload for browser/tab close (backup for browsers that don't support pagehide well)
window.addEventListener('beforeunload', function(e) {
    // Skip if it's logout or internal navigation
    if (isNavigatingAway || isInternalNavigation) {
        return;
    }
    
    // For actual browser/tab close, stop the timer
    if (isTimerRunning && trackingId) {
        const totalMinutes = Math.floor(elapsedSeconds / 60);
        
        // Use sendBeacon for reliable data sending
        const formData = new FormData();
        formData.append('tracking_id', trackingId);
        formData.append('duration_minutes', totalMinutes);
        formData.append('_csrf', getCsrfToken());
        
        navigator.sendBeacon(
            '<?= Url::to(['site/stop-tracking']) ?>', 
            formData
        );
        
        console.log('Browser/tab closing, timer stopped via sendBeacon.');
    }
});

// Timer should only stop on:
// 1. Explicit pause/stop button click (handled by stopTimer())
// 2. Logout (handled by logout detection above)
// 3. Website/tab closed (handled by pagehide/beforeunload events)

// Expose functions globally for debugging
window.timerDebug = {
    getState: function() {
        return {
            isRunning: isTimerRunning,
            elapsedSeconds: elapsedSeconds,
            trackingId: trackingId
        };
    },
    forceStop: function() {
        stopTimer();
    },
    forceStart: function() {
        startTimer();
    }
};

console.log('Timer script loaded. Debug with window.timerDebug');
</script>