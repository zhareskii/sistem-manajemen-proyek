<?php
use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Reports';
?>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    background-color: #f5f5f5;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.reports-container {
    max-width: 1400px;
    margin: 0 auto;
}

.reports-header {
    font-size: 24px;
    font-weight: 700;
    color: #000;
    margin-bottom: 20px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #000;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

.chart-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
}

.chart-title {
    font-size: 18px;
    font-weight: 600;
    color: #000;
    margin-bottom: 20px;
}

.chart-canvas {
    width: 100%;
    height: 300px;
}

.tasks-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.task-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    min-height: 400px;
}

.task-section-header {
    font-size: 16px;
    font-weight: 600;
    color: #000;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #eee;
}

.task-list {
    list-style: none;
}

.task-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.task-item:last-child {
    border-bottom: none;
}

.task-title {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 4px;
}

.task-meta {
    font-size: 12px;
    color: #999;
    display: flex;
    gap: 12px;
}

.task-type {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.task-type.subtask {
    background: #e3f2fd;
    color: #1976d2;
}

.task-type.card {
    background: #f3e5f5;
    color: #7b1fa2;
}

.empty-task {
    text-align: center;
    padding: 40px 20px;
    color: #999;
    font-size: 14px;
}

.activity-table {
    width: 100%;
    border-collapse: collapse;
}

.activity-table tbody tr:hover {
    background-color: #f9f9f9;
}

.see-more-btn {
    background: none;
    border: none;
    color: #1976d2;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    padding: 8px 16px;
    transition: color 0.2s;
}

.see-more-btn:hover {
    color: #1565c0;
    text-decoration: underline;
}

.extra-row { 
    display: none; 
}

/* See more button for daily activity table */
.see-more {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 12px auto 0;
    background: none;
    border: none;
    color: #1976d2;
    font-weight: 600;
    cursor: pointer;
}

@media (max-width: 1024px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .tasks-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .chart-canvas {
        height: 250px;
    }
}
@media print {
    .mobile-header, .sidebar, .sidebar-overlay, .reports-toolbar { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    .reports-container { max-width: 100%; }
}
</style>

<div class="reports-container">
    <div class="reports-toolbar" style="display:flex;align-items:center;gap:10px;justify-content:flex-end;margin-bottom:16px;">
        <button id="printReportsBtn" class="print-btn" style="display:inline-flex;align-items:center;gap:8px;background:#115f67;color:#fff;border:none;border-radius:8px;padding:10px 14px;font-weight:600;cursor:pointer;box-shadow:0 2px 8px rgba(17,63,103,0.15);">
            <i class="bi bi-printer"></i>
            <span>Print</span>
        </button>
    </div>
    <h1 class="reports-header">Reports</h1>
    
    <!-- Statistics Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value">
                <?= isset($memberCounts['subtasks_created']) ? (int)$memberCounts['subtasks_created'] + (int)$memberCounts['cards_created'] : 0 ?>
            </div>
            <div class="stat-label">Tasks Created</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">
                <?= isset($memberCounts['subtasks_completed']) ? (int)$memberCounts['subtasks_completed'] + (int)$memberCounts['cards_completed'] : 0 ?>
            </div>
            <div class="stat-label">Tasks Completed</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">
                <?= isset($submissionCounts['subtask_accepted']) ? (int)$submissionCounts['subtask_accepted'] + (int)$submissionCounts['card_accepted'] : 0 ?>
            </div>
            <div class="stat-label">Tasks Accepted</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">
                <?= isset($submissionCounts['subtask_rejected']) ? (int)$submissionCounts['subtask_rejected'] + (int)$submissionCounts['card_rejected'] : 0 ?>
            </div>
            <div class="stat-label">Tasks Rejected</div>
        </div>
    </div>

    <!-- Productivity Table (Time Tracking Harian) -->
    <div class="chart-section">
        <div class="chart-title">Daily Productivity</div>
        <table class="activity-table">
            <thead>
                <tr>
                    <th style="text-align:left; padding: 12px; border-bottom: 1px solid #eee; font-weight: 600;">Date</th>
                    <th style="text-align:right; padding: 12px; border-bottom: 1px solid #eee; font-weight: 600;">Total Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dailyWorking = isset($dailyWorking) ? $dailyWorking : [];
                $dailyWorkingAll = isset($dailyWorkingAll) ? $dailyWorkingAll : [];
                
                if (empty($dailyWorking) && empty($dailyWorkingAll)): ?>
                    <tr>
                        <td colspan="2" style="padding: 40px; text-align:center; color:#999;">No time tracking data available</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    // Show last 7 days (already sorted oldest first from controller)
                    foreach ($dailyWorking as $date => $minutes): 
                        $hours = round($minutes / 60, 2);
                        $formattedDate = date('d M Y', strtotime($date));
                    ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #f0f0f0;"><?= Html::encode($formattedDate) ?></td>
                            <td style="padding: 12px; text-align:right; border-bottom: 1px solid #f0f0f0;"><?= number_format($hours, 2, ',', '.') ?> hours</td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php 
                    // Show remaining data (hidden by default)
                    $remainingData = [];
                    foreach ($dailyWorkingAll as $date => $minutes) {
                        if (!isset($dailyWorking[$date])) {
                            $remainingData[$date] = $minutes;
                        }
                    }
                    // Data already sorted oldest first from controller
                    
                    foreach ($remainingData as $date => $minutes): 
                        $hours = round($minutes / 60, 2);
                        $formattedDate = date('d M Y', strtotime($date));
                    ?>
                        <tr class="extra-row" style="display: none;">
                            <td style="padding: 12px; border-bottom: 1px solid #f0f0f0;"><?= Html::encode($formattedDate) ?></td>
                            <td style="padding: 12px; text-align:right; border-bottom: 1px solid #f0f0f0;"><?= number_format($hours, 2, ',', '.') ?> hours</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (!empty($remainingData)): ?>
            <div style="text-align: center; margin-top: 16px;">
                <button id="seeMoreBtn" type="button" class="see-more-btn" data-expanded="0">Show more →</button>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Task Details Grid -->
    <div class="tasks-grid">
        <!-- Task Dibuat -->
        <div class="task-section">
            <div class="task-section-header">Tasks Created</div>
            <ul class="task-list">
                <?php if (empty($subtasksCreated) && empty($cardsCreated)): ?>
                    <li class="empty-task">No tasks created</li>    
                <?php else: ?>
                    <?php foreach ($subtasksCreated as $subtask): ?>
                        <li class="task-item">
                            <div class="task-title"><?= Html::encode($subtask->subtask_title) ?></div>
                            <div class="task-meta">
                                <span class="task-type subtask">Subtask</span>
                                <?php if ($subtask->card && $subtask->card->board && $subtask->card->board->project): ?>
                                    <span><?= Html::encode($subtask->card->board->project->project_name) ?></span>
                                <?php endif; ?>
                                <span><?= date('d M Y', strtotime($subtask->created_at)) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php foreach ($cardsCreated as $card): ?>
                        <li class="task-item">
                            <div class="task-title"><?= Html::encode($card->card_title) ?></div>
                            <div class="task-meta">
                                <span class="task-type card">Card</span>
                                <?php if ($card->board && $card->board->project): ?>
                                    <span><?= Html::encode($card->board->project->project_name) ?></span>
                                <?php endif; ?>
                                <span><?= date('d M Y', strtotime($card->created_at)) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Task Selesai -->
        <div class="task-section">
            <div class="task-section-header">Tasks Completed</div>
            <ul class="task-list">
                <?php if (empty($subtasksCompleted) && empty($cardsCompleted)): ?>
                    <li class="empty-task">No completed tasks</li>
                <?php else: ?>
                    <?php foreach ($subtasksCompleted as $subtask): ?>
                        <li class="task-item">
                            <div class="task-title"><?= Html::encode($subtask->subtask_title) ?></div>
                            <div class="task-meta">
                                <span class="task-type subtask">Subtask</span>
                                <?php if ($subtask->card && $subtask->card->board && $subtask->card->board->project): ?>
                                    <span><?= Html::encode($subtask->card->board->project->project_name) ?></span>
                                <?php endif; ?>
                                <span><?= date('d M Y', strtotime($subtask->created_at)) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php foreach ($cardsCompleted as $card): ?>
                        <li class="task-item">
                            <div class="task-title"><?= Html::encode($card->card_title) ?></div>
                            <div class="task-meta">
                                <span class="task-type card">Card</span>
                                <?php if ($card->board && $card->board->project): ?>
                                    <span><?= Html::encode($card->board->project->project_name) ?></span>
                                <?php endif; ?>
                                <span><?= date('d M Y', strtotime($card->created_at)) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Task Diterima -->
        <div class="task-section">
            <div class="task-section-header">Tasks Accepted</div>
            <ul class="task-list">
                <?php if (empty($subtasksAccepted) && empty($cardsAccepted)): ?>
                    <li class="empty-task">No accepted tasks</li>
                <?php else: ?>
                    <?php foreach ($subtasksAccepted as $subtask): ?>
                        <li class="task-item">
                            <div class="task-title"><?= Html::encode($subtask->subtask_title) ?></div>
                            <div class="task-meta">
                                <span class="task-type subtask">Subtask</span>
                                <?php if ($subtask->card && $subtask->card->board && $subtask->card->board->project): ?>
                                    <span><?= Html::encode($subtask->card->board->project->project_name) ?></span>
                                <?php endif; ?>
                                <?php
                                // Get submission date
                                $submission = \app\models\SubtaskSubmission::find()
                                    ->where(['subtask_id' => $subtask->subtask_id, 'status' => 'accepted'])
                                    ->orderBy(['reviewed_at' => SORT_DESC])
                                    ->one();
                                $date = $submission && $submission->reviewed_at ? $submission->reviewed_at : $subtask->created_at;
                                ?>
                                <span><?= date('d M Y', strtotime($date)) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php foreach ($cardsAccepted as $card): ?>
                        <li class="task-item">
                            <div class="task-title"><?= Html::encode($card->card_title) ?></div>
                            <div class="task-meta">
                                <span class="task-type card">Card</span>
                                <?php if ($card->board && $card->board->project): ?>
                                    <span><?= Html::encode($card->board->project->project_name) ?></span>
                                <?php endif; ?>
                                <?php
                                // Get submission date
                                $submission = \app\models\CardSubmission::find()
                                    ->where(['card_id' => $card->card_id, 'status' => 'accepted'])
                                    ->orderBy(['reviewed_at' => SORT_DESC])
                                    ->one();
                                $date = $submission && $submission->reviewed_at ? $submission->reviewed_at : $card->created_at;
                                ?>
                                <span><?= date('d M Y', strtotime($date)) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Task Ditolak -->
        <div class="task-section">
            <div class="task-section-header">Tasks Rejected</div>
            <ul class="task-list">
                <?php if (empty($subtasksRejected) && empty($cardsRejected)): ?>
                    <li class="empty-task">No rejected tasks</li>
                <?php else: ?>
                    <?php foreach ($subtasksRejected as $subtask): ?>
                        <li class="task-item">
                            <div class="task-title"><?= Html::encode($subtask->subtask_title) ?></div>
                            <div class="task-meta">
                                <span class="task-type subtask">Subtask</span>
                                <?php if ($subtask->card && $subtask->card->board && $subtask->card->board->project): ?>
                                    <span><?= Html::encode($subtask->card->board->project->project_name) ?></span>
                                <?php endif; ?>
                                <?php
                                // Get submission date
                                $submission = \app\models\SubtaskSubmission::find()
                                    ->where(['subtask_id' => $subtask->subtask_id, 'status' => 'rejected'])
                                    ->orderBy(['reviewed_at' => SORT_DESC])
                                    ->one();
                                $date = $submission && $submission->reviewed_at ? $submission->reviewed_at : $subtask->created_at;
                                ?>
                                <span><?= date('d M Y', strtotime($date)) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php foreach ($cardsRejected as $card): ?>
                        <li class="task-item">
                            <div class="task-title"><?= Html::encode($card->card_title) ?></div>
                            <div class="task-meta">
                                <span class="task-type card">Card</span>
                                <?php if ($card->board && $card->board->project): ?>
                                    <span><?= Html::encode($card->board->project->project_name) ?></span>
                                <?php endif; ?>
                                <?php
                                // Get submission date
                                $submission = \app\models\CardSubmission::find()
                                    ->where(['card_id' => $card->card_id, 'status' => 'rejected'])
                                    ->orderBy(['reviewed_at' => SORT_DESC])
                                    ->one();
                                $date = $submission && $submission->reviewed_at ? $submission->reviewed_at : $card->created_at;
                                ?>
                                <span><?= date('d M Y', strtotime($date)) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script>
// Toggle see more / see less for daily activity rows
document.addEventListener('DOMContentLoaded', function() {
    const seeMoreBtn = document.getElementById('seeMoreBtn');
    if (seeMoreBtn) {
        seeMoreBtn.addEventListener('click', function() {
            const rows = document.querySelectorAll('.extra-row');
            const expanded = this.getAttribute('data-expanded') === '1';
            rows.forEach(r => r.style.display = expanded ? 'none' : 'table-row');
            this.setAttribute('data-expanded', expanded ? '0' : '1');
            this.textContent = expanded ? 'Show more →' : 'Show less ←';
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var printBtn = document.getElementById('printReportsBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }
});
</script>