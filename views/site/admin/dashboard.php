<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $totalProjects */
/** @var int $completedProjects */
/** @var int $inProgressProjects */
/** @var int $totalUsers */
/** @var app\models\Comment[] $recentComments */
/** @var app\models\HelpRequest[] $pendingHelpRequests */

$this->title = 'Admin Dashboard';

// Calculate additional stats
$totalCards = \app\models\Card::find()->count();
$totalSubtasks = \app\models\Subtask::find()->count();
$completedSubtasks = \app\models\Subtask::find()->where(['status' => 'done'])->count();

// Calculate productivity metrics (average completion rate per user)
$allUsers = \app\models\User::find()->all();
$totalProductivity = 0;
$activeUserCount = 0;

foreach ($allUsers as $user) {
    if ($user->role === 'member') {
        $userSubtasks = \app\models\Subtask::find()->where(['created_by' => $user->user_id])->count();
        $userCompletedSubtasks = \app\models\Subtask::find()->where(['created_by' => $user->user_id, 'status' => 'done'])->count();
        
        if ($userSubtasks > 0) {
            $userProductivity = ($userCompletedSubtasks / $userSubtasks) * 100;
            $totalProductivity += $userProductivity;
            $activeUserCount++;
        }
    }
}

$overallProgress = $activeUserCount > 0 ? round($totalProductivity / $activeUserCount) : 0;

// Get total users count (all users including admin)
$totalAllUsers = \app\models\User::find()->count();
$totalMembers = \app\models\User::find()->where(['role' => 'member'])->count();
$totalAdmins = \app\models\User::find()->where(['role' => 'admin'])->count();
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

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
}

.stat-number {
    font-size: 2.8rem;
    font-weight: 800;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 0.95rem;
    font-weight: 600;
    opacity: 0.95;
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

.badge-completed {
    background: rgba(76, 175, 80, 0.2);
    color: #2e7d32;
}

.badge-cancelled {
    background: rgba(244, 67, 54, 0.2);
    color: #c62828;
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

/* System Stats */
.system-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.system-stat-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(17, 63, 103, 0.1);
    padding: 25px;
    text-align: center;
    border-top: 5px solid var(--color-medium-blue);
}

.system-stat-card h4 {
    color: var(--color-dark-blue);
    font-size: 1rem;
    margin: 0 0 15px 0;
    font-weight: 700;
}

.system-stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--color-medium-blue);
    margin-bottom: 8px;
}

.system-stat-label {
    font-size: 0.9rem;
    color: var(--color-text-light);
    font-weight: 600;
}

/* Productivity Indicator */
.productivity-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 10px;
}

.productivity-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.productivity-high { background: #4caf50; }
.productivity-medium { background: #ff9800; }
.productivity-low { background: #f44336; }

.productivity-text {
    font-size: 0.8rem;
    font-weight: 600;
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
        grid-template-columns: repeat(2, 1fr);
    }
    
    .projects-grid {
        grid-template-columns: 1fr;
    }
    
    .system-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-info">
            <h1>Welcome, <?= Html::encode(Yii::$app->user->identity->full_name) ?>!</h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-number"><?= $totalProjects ?></div>
                <div class="stat-label">Total Projects</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-number"><?= $completedProjects ?></div>
                <div class="stat-label">Projects Done</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-number"><?= $inProgressProjects ?></div>
                <div class="stat-label">Projects Active</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-number"><?= $totalAllUsers ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Content + Sidebar -->
    <div class="dashboard-grid">
        <!-- Left Column - Main Content -->
        <div>
            <!-- System Stats -->
            <div class="system-stats">
                <div class="system-stat-card">
                    <h4>📊 Overall Productivity</h4>
                    <div class="system-stat-value"><?= $overallProgress ?>%</div>
                    <div class="system-stat-label">Average User Completion Rate</div>
                    <div class="productivity-indicator">
                        <div class="productivity-dot productivity-<?= $overallProgress >= 70 ? 'high' : ($overallProgress >= 40 ? 'medium' : 'low') ?>"></div>
                        <div class="productivity-text">
                            <?= $overallProgress >= 70 ? 'High' : ($overallProgress >= 40 ? 'Medium' : 'Low') ?> Productivity
                        </div>
                    </div>
                </div>
                <div class="system-stat-card">
                    <h4>📋 Total Cards</h4>
                    <div class="system-stat-value"><?= $totalCards ?></div>
                    <div class="system-stat-label">Tasks Created</div>
                </div>
                <div class="system-stat-card">
                    <h4>👥 User Breakdown</h4>
                    <div class="system-stat-value"><?= $totalMembers ?></div>
                    <div class="system-stat-label">Members</div>
                    <div style="font-size: 0.9rem; color: var(--color-text-light); margin-top: 5px;">
                        + <?= $totalAdmins ?> Admin
                    </div>
                </div>
                <div class="system-stat-card">
                    <h4>✅ Completed Subtasks</h4>
                    <div class="system-stat-value"><?= $completedSubtasks ?></div>
                    <div class="system-stat-label">Finished Work Items</div>
                </div>
            </div>

        <!-- Right Column - Sidebar -->
        <div>
            <!-- Calendar -->
            

            <!-- System Status -->
            <div class="dashboard-section" style="margin-top: 20px;">
                <h3 class="section-title">🔧 System Status</h3>
                <div style="display: grid; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--color-light-gray); border-radius: 8px;">
                        <span style="font-weight: 600; color: var(--color-dark-blue);">Total Users</span>
                        <span style="font-weight: 700; color: var(--color-medium-blue);"><?= $totalAllUsers ?> users</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--color-light-gray); border-radius: 8px;">
                        <span style="font-weight: 600; color: var(--color-dark-blue);">Active Members</span>
                        <span style="font-weight: 700; color: var(--color-medium-blue);"><?= $activeUserCount ?> users</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--color-light-gray); border-radius: 8px;">
                        <span style="font-weight: 600; color: var(--color-dark-blue);">Productivity</span>
                        <span style="font-weight: 700; color: <?= $overallProgress >= 70 ? '#4caf50' : ($overallProgress >= 40 ? '#ff9800' : '#f44336') ?>;">
                            <?= $overallProgress ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="calendar-card">
                <h4>📅 Calender</h4>
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

<script>
// Initialize calendar and basic functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin dashboard loaded successfully');
    
    // Add hover effects to all interactive elements
    const interactiveElements = document.querySelectorAll('.project-card, .activity-item, .system-stat-card');
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        element.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>