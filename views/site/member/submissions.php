<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Subtask[] $eligibleSubtasks */
/** @var app\models\SubtaskSubmission[] $mySubmissions */
/** @var app\models\Card[] $eligibleCards */
/** @var app\models\CardSubmission[] $myCardSubmissions */
/** @var app\models\SubtaskSubmission[] $pendingSubtaskSubmissions */

$this->title = 'Submissions';
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= Html::encode($this->title) ?></title>
    <style>
    /* Base Styles */
    .container { 
        max-width: 1200px; 
        margin: 0 auto;
        padding: 15px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .panel { 
        background:#fff; 
        border:1px solid #e9ecef; 
        border-radius:10px; 
        padding:20px; 
        box-shadow:0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .panel h3 { 
        display:flex; 
        align-items:center; 
        gap:8px; 
        margin:0 0 15px 0;
        font-size: 1.3rem;
        color: #333;
    }
    
    .badge { 
        border-radius:12px; 
        padding:6px 12px; 
        font-size:0.85rem;
        font-weight: 500;
    }
    
    .badge.success {
        background: #e8f5e9;
        color: #2e7d32;
    }
    
    .badge.danger {
        background: #ffebee;
        color: #c62828;
    }
    
    .badge.info {
        background: #e3f2fd;
        color: #1565c0;
    }
    
    .btn { 
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    
    .btn-primary { 
        background: #007bff; 
        color: white; 
    }
    
    .btn-success { 
        background: #28a745; 
        color: white; 
    }
    
    .btn-warning { 
        background: #ffc107; 
        color: #212529; 
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
    }
    
    .btn + .btn { 
        margin-left:8px; 
    }
    
    .muted { 
        color:#6c757d;
        font-style: italic;
    }
    
    .submissions-grid { 
        display:grid; 
        grid-template-columns: minmax(0,1fr) minmax(0,1fr); 
        gap:20px; 
        margin-top:20px; 
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        box-sizing: border-box;
    }
    
    textarea.form-control {
        min-height: 80px;
        resize: vertical;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
    }
    
    /* Mobile Table Container */
    .mobile-table-container {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: white;
        margin-top: 10px;
    }
    
    .responsive-table {
        width: 100%;
        min-width: 0;
        border-collapse: collapse;
    }
    
    .responsive-table th,
    .responsive-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
        white-space: normal;
        word-break: keep-all;
        overflow-wrap: normal;
    }
    
    .responsive-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
        position: sticky;
        top: 0;
    }
    
    .responsive-table tr:last-child td {
        border-bottom: none;
    }
    
    .responsive-table tr:hover {
        background: #f8f9fa;
    }
    
    /* Mobile Styles */
    @media (max-width: 768px) {
        .container {
            padding: 10px;
        }
        
        .submissions-grid { 
            grid-template-columns: 1fr; 
            gap: 15px;
        }
        
        .panel {
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .panel h3 {
            font-size: 1.2rem;
            margin-bottom: 12px;
        }
        
        .mobile-table-container {
            margin: 0 -10px;
            border-left: none;
            border-right: none;
            border-radius: 0;
        }
        
        .responsive-table th,
        .responsive-table td {
            padding: 10px 12px;
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 12px 16px;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .btn + .btn {
            margin-left: 0;
        }
        
        .btn-sm {
            padding: 8px 12px;
            width: auto;
        }
        
        .form-control {
            font-size: 16px; /* Prevent zoom on iOS */
        }
        
        /* Stack form elements in review forms */
        .responsive-table .form-group {
            margin-bottom: 8px;
        }
        
        .responsive-table .form-control {
            font-size: 0.9rem;
        }
    }

    /* Stack tables for narrow screens */
    @media (max-width: 600px) {
        .mobile-table-container { overflow-x: visible; border: none; border-radius: 8px; }
        .responsive-table { min-width: 0; display: block; }
        .responsive-table thead { display: none; }
        .responsive-table tbody { display: block; }
        .responsive-table tr { display: block; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 12px; }
        .responsive-table td { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 10px 12px; white-space: normal; }
        .responsive-table td::before { content: attr(data-label); font-weight: 600; color: #495057; }
    }
    
    /* Extra small devices */
    @media (max-width: 480px) {
        .container {
            padding: 8px;
        }
        
        .panel {
            padding: 12px;
        }
        
        .panel h3 {
            font-size: 1.1rem;
        }
        
        .responsive-table th,
        .responsive-table td {
            padding: 8px 10px;
            font-size: 0.85rem;
        }
        
        .badge {
            padding: 4px 8px;
            font-size: 0.8rem;
        }
        
        .form-control {
            padding: 8px 10px;
        }
    }
    
    /* Print Styles */
    @media print {
        .btn {
            display: none;
        }
        
        .panel {
            box-shadow: none;
            border: 1px solid #000;
        }
    }
    </style>
</head>
<body>
<div class="container">
    <h2 style="margin-bottom: 20px; color: #333;"><?= Html::encode($this->title) ?></h2>

    <div class="submissions-grid">
        <!-- Left Column -->
        <div>
            <!-- Submit Subtask Panel -->
            <div class="panel panel-submit-subtask">
                <h3><span style="font-size: 1.2em;">📨</span> Submit Subtask to Team Lead</h3>
                <form method="post" action="<?= Url::to(['site/submissions-member']) ?>">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                    <div class="form-group">
                        <label>Select Subtask</label>   
                        <select name="subtask_id" class="form-control" required>
                            <option value="">-- Select Subtask --</option>
                            <?php foreach ($eligibleSubtasks as $st): ?>
                                <option value="<?= Html::encode($st->subtask_id) ?>">
                                    <?= Html::encode(($st->card ? $st->card->card_title : 'Without Card') . ' — ' . $st->subtask_title) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Submission Notes (optional)</label>
                        <textarea name="submission_notes" class="form-control" rows="3" placeholder="Add submission notes"></textarea>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Send to Team Lead</button>
                    </div>
                </form>
            </div>

            <!-- Subtask Submission History Panel -->
            <div class="panel panel-history-subtask">
                <h3>Subtask Submission History</h3>
                <?php if (empty($mySubmissions)): ?>
                    <p class="muted">No subtask submissions found.</p>
                <?php else: ?>
                <div class="mobile-table-container">
                    <table class="responsive-table">
                        <thead>
                            <tr>
                                <th>Subtask</th>
                                <th>Status</th>
                                <th>Reviewer Notes</th>
                                <th>Submitted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mySubmissions as $sb): ?>
                                <tr>
                                    <td data-label="Subtask"><?= Html::encode(($sb->subtask && $sb->subtask->card ? $sb->subtask->card->card_title : 'Without Card') . ' — ' . ($sb->subtask ? $sb->subtask->subtask_title : 'Subtask')) ?></td>
                                    <td data-label="Status">
                                        <?php if ($sb->status === 'pending'): ?>
                                            <span class="badge info">Pending</span>
                                        <?php elseif ($sb->status === 'accepted'): ?>
                                            <span class="badge success">Accepted</span>
                                        <?php else: ?>
                                            <span class="badge danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Reviewer Notes"><?= Html::encode($sb->review_notes ?? '-') ?></td>
                                    <td data-label="Submitted At"><?= Html::encode(Yii::$app->formatter->asDatetime($sb->created_at)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Submit Project Panel -->
            <div class="panel panel-submit-project">
                <h3><span style="font-size: 1.2em;">📬</span> Submit Project to Admin</h3>
                <form method="post" action="<?= Url::to(['site/submissions-member']) ?>">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                    <div class="form-group">
                        <label>Select Project (all subtasks must be completed)</label>
                        <select name="submit_project_id" class="form-control" required>
                            <option value="">-- Select Project --</option>
                            <?php foreach ($eligibleProjects as $proj): ?>
                                <option value="<?= Html::encode($proj->project_id) ?>">
                                    <?= Html::encode($proj->project_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Submission Notes (optional)</label>
                        <textarea name="submission_notes_project" class="form-control" rows="3" placeholder="Add submission notes"></textarea>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-success">Send Project to Admin</button>
                    </div>
                </form>
                <?php if (empty($eligibleProjects)): ?>
                    <p class="muted" style="margin-top: 10px;">No projects available for submission.</p>
                <?php endif; ?>
            </div>

            <!-- Review Subtask Panel -->
            <div class="panel panel-review-subtask">
                <h3><span style="font-size: 1.2em;">🗂️</span> Review Subtask</h3>
                <?php if (empty($pendingSubtaskSubmissions)): ?>
                    <p class="muted">No pending subtask submissions to review.</p>
                <?php else: ?>
                <div class="mobile-table-container">
                    <table class="responsive-table">
                        <thead>
                            <tr>
                                <th>Subtask</th>
                                <th>Card</th>
                                <th>Submitter</th>
                                <th>Submission Notes</th>
                                <th>Submitted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingSubtaskSubmissions as $ps): ?>
                                <tr>
                                    <td data-label="Subtask"><?= Html::encode($ps->subtask ? $ps->subtask->subtask_title : '-') ?></td>
                                    <td data-label="Card"><?= Html::encode(($ps->subtask && $ps->subtask->card) ? $ps->subtask->card->card_title : '-') ?></td>
                                    <td data-label="Submitter"><?= Html::encode(($ps->submitter ? ($ps->submitter->full_name ?: $ps->submitter->username) : ('#'.$ps->submitted_by))) ?></td>
                                    <td data-label="Notes"><?= Html::encode($ps->submission_notes ?: '-') ?></td>
                                    <td data-label="Submitted At"><?= Html::encode(Yii::$app->formatter->asDatetime($ps->created_at)) ?></td>
                                    <td data-label="Actions">
                                        <form method="post" action="<?= Url::to(['site/submissions-member']) ?>">
                                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                                            <?= Html::hiddenInput('review_submission_id', $ps->submission_id) ?>
                                            <div class="form-group">
                                                <select name="review_action" class="form-control" required>
                                                    <option value="accept">Accept</option>
                                                    <option value="reject">Reject</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="review_notes" class="form-control" placeholder="Reviewer notes (optional)">
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Subtask Review History Panel -->
            <div class="panel panel-subtask-review-history">
                <h3><span style="font-size: 1.2em;">🗂️</span> Subtask Review History</h3>
                <?php if (empty($reviewedSubtaskSubmissions)): ?>
                    <p class="muted">No subtask reviews have been performed.</p>
                <?php else: ?>
                <div class="mobile-table-container">
                    <table class="responsive-table">
                        <thead>
                            <tr>
                                <th>Subtask</th>
                                <th>Status</th>
                                <th>Submitter</th>
                                <th>Reviewed At</th>
                                <th>Review Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviewedSubtaskSubmissions as $rv): ?>
                                <tr>
                                    <td data-label="Subtask"><?= Html::encode(($rv->subtask && $rv->subtask->card ? $rv->subtask->card->card_title : 'Without Card') . ' — ' . ($rv->subtask ? $rv->subtask->subtask_title : 'Subtask')) ?></td>
                                <td data-label="Status">
                                    <?php if ($rv->status === 'accepted'): ?>
                                        <span class="badge success">Accepted</span>
                                    <?php else: ?>
                                        <span class="badge danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Submitter"><?= Html::encode($rv->submitter ? ($rv->submitter->full_name ?: $rv->submitter->username) : '-') ?></td>
                                <td data-label="Reviewed At"><?= Html::encode(Yii::$app->formatter->asDatetime($rv->reviewed_at ?: $rv->created_at)) ?></td>
                                <td data-label="Review Notes"><?= Html::encode($rv->review_notes ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Project Submission History Panel -->
            <div class="panel panel-project-history">
                <h3><span style="font-size: 1.2em;">🗂️</span> Project Submission History</h3>
                <?php if (empty($myProjectSubmissionGroups)): ?>
                    <p class="muted">No project submissions have been made.</p>
                <?php else: ?>
                <div class="mobile-table-container">
                    <table class="responsive-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Total Cards</th>
                                <th>Status</th>
                                <th>Last Submitted At</th>
                                <th>Last Reviewed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myProjectSubmissionGroups as $pid => $group): ?>
                                <?php $project = $group['project']; $counts = $group['counts']; $status = $group['status']; ?>
                                <tr>
                                    <td data-label="Project"><?= Html::encode($project ? $project->project_name : '-') ?></td>
                                <td data-label="Total Cards"><?= (int)($counts['pending'] + $counts['accepted'] + $counts['rejected']) ?></td>
                                <td data-label="Status">
                                    <?php if ($status === 'pending'): ?>
                                        <span class="badge info">Pending</span>
                                    <?php elseif ($status === 'accepted'): ?>
                                        <span class="badge success">Accepted</span>
                                    <?php else: ?>
                                        <span class="badge danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Last Submitted At"><?= Html::encode(Yii::$app->formatter->asDatetime($group['last_submission_at'])) ?></td>
                                <td data-label="Last Reviewed At"><?= $group['last_reviewed_at'] ? Html::encode(Yii::$app->formatter->asDatetime($group['last_reviewed_at'])) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript untuk meningkatkan UX di mobile
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll untuk table container di mobile
    const tableContainers = document.querySelectorAll('.mobile-table-container');
    tableContainers.forEach(container => {
        container.addEventListener('touchstart', function() {
            this.style.cursor = 'grabbing';
        });
        
        container.addEventListener('touchend', function() {
            this.style.cursor = 'grab';
        });
    });
    
    // Prevent zoom pada input fields di iOS
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.fontSize = '16px';
        });
    });
});
</script>
</body>
</html>