<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\CardSubmission[] $pendingCardSubmissions */
/** @var app\models\CardSubmission[] $allCardSubmissions */

$this->title = 'Review Pengajuan Project - Admin';
?>
<div class="container" style="max-width: 1200px;">
    <style>
    .panel { 
        background:#fff; 
        border:1px solid #e9ecef; 
        border-radius:10px; 
        padding:20px; 
        box-shadow:0 2px 8px rgba(0,0,0,0.05); 
        margin-bottom:24px; 
    }
    .panel h3 { 
        display:flex; 
        align-items:center; 
        gap:8px; 
        margin:0 0 20px 0; 
        color:#2c3e50;
        font-size:1.5rem;
        font-weight:600;
    }
    .label { 
        display:inline-block; 
        padding:6px 12px; 
        border-radius:12px; 
        font-size:12px; 
        font-weight:500;
    }
    .label-info { background:#e3f2fd; color:#1565c0; }
    .label-success { background:#e8f5e9; color:#2e7d32; }
    .label-danger { background:#ffebee; color:#c62828; }
    .label-warning { background:#fff3e0; color:#ef6c00; }
    .muted { color:#6c757d; }
    .table th { background:#f8f9fa; font-weight:600; }
    .card-info { 
        background:#f8f9fa; 
        padding:20px; 
        border-radius:8px; 
        margin-bottom:20px;
        border-left:4px solid #007bff;
    }
    .submission-meta { 
        font-size:14px; 
        color:#6c757d;
        margin-bottom:12px;
    }
    .submission-meta strong { color:#495057; }
    .notes-section {
        background:#fff;
        padding:12px;
        border-radius:6px;
        border:1px solid #e9ecef;
        margin:12px 0;
    }
    .btn-review {
        min-width:120px;
    }
    .empty-state {
        text-align:center;
        padding:40px 20px;
        color:#6c757d;
    }
    .empty-state i {
        font-size:48px;
        margin-bottom:16px;
        opacity:0.5;
    }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= Html::encode($this->title) ?></h2>
        <div class="text-muted">
            Total: <?= isset($allProjectSubmissionGroups) ? count($allProjectSubmissionGroups) : 0 ?> pengajuan • 
            Pending: <?= isset($pendingProjectGroups) ? count($pendingProjectGroups) : 0 ?> menunggu review
        </div>
    </div>

    <!-- Panel untuk review project yang diajukan team lead -->
    <div class="panel">
        <h3>⏳ Waiting Review (<?= isset($pendingProjectGroups) ? count($pendingProjectGroups) : 0 ?>)</h3>
        
        <?php if (empty($pendingProjectGroups)): ?>
            <div class="empty-state">
                <i>📭</i>
                <h4>No pending project submissions</h4>
                <p>All project submissions from team leads have been reviewed.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pendingProjectGroups as $pid => $group): ?>
                <?php $project = $group['project']; $submissions = $group['submissions']; ?>
                <div class="card-info">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 style="margin:0 0 12px 0; color:#2c3e50;">
                                <?= Html::encode($project->project_name) ?>
                            </h4>
                            <div style="margin-bottom:8px; font-size:15px; color:#6c757d;">
                                <strong>Team Lead:</strong> <?= Html::encode($project->teamLead ? $project->teamLead->full_name : '-') ?>
                            </div>
                            <div class="submission-meta">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <strong>Total Cards Submitted:</strong>
                                        <?= count($submissions) ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Project Status:</strong> <?= Html::encode($project->status) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="notes-section">
                                <strong>🗂️ Card List in Project:</strong>
                                <?php $cards = \app\models\Card::find()->joinWith('board')->where(['boards.project_id' => $project->project_id])->with(['subtasks','assignedUsers'])->all(); ?>
                                <?php if (empty($cards)): ?>
                                    <div class="muted">No cards added to this project.</div>
                                <?php else: ?>
                                    <?php foreach ($cards as $card): ?>
                                        <div style="margin:12px 0; padding:12px; border:1px solid #e9ecef; border-radius:6px;">
                                            <div style="font-weight:600; color:#2c3e50;"><?= Html::encode($card->card_title) ?></div>
                                            <div class="muted"><?= Html::encode($card->description ?: '-') ?></div>
                                            <div style="margin-top:6px; font-size:14px; color:#495057;">
                                                <?php $assignedNames = !empty($card->assignedUsers) ? implode(', ', array_map(function($u){ return $u->full_name ?: $u->username; }, $card->assignedUsers)) : '-'; ?>
                                                <span><strong>Assigned User:</strong> <?= Html::encode($assignedNames) ?></span>
                                                <span style="margin-left:12px;"><strong>Assigned Role:</strong> <?= Html::encode($card->assigned_role ?: '-') ?></span>
                                                <span style="margin-left:12px;"><strong>Deadline:</strong> <?= $card->due_date ? Html::encode(Yii::$app->formatter->asDate($card->due_date)) : '-' ?></span>
                                            </div>
                                            <?php if (!empty($card->subtasks)): ?>
                                                <div style="margin-top:8px;">
                                                    <strong>Subtasks:</strong>
                                                    <ul style="margin:8px 0 0 18px;">
                                                        <?php foreach ($card->subtasks as $st): ?>
                                                            <li>
                                                                <div><strong><?= Html::encode($st->subtask_title) ?></strong></div>
                                                                <div class="muted"><?= Html::encode($st->description ?: '-') ?></div>
                                                                <div style="font-size:13px; color:#495057;">
                                                                    <span>Estimasi: <?= $st->estimated_hours ? Html::encode($st->estimated_hours) . 'j' : '-' ?></span>
                                                                    <span style="margin-left:12px;">Aktual: <?= $st->actual_hours ? Html::encode($st->actual_hours) . 'j' : '0j' ?></span>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <form method="post" action="<?= Url::to(['site/submissions-admin']) ?>">
                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                                <?= Html::hiddenInput('project_id', $project->project_id) ?>
                                
                                <div class="form-group">
                                    <label><strong>Review Decision:</strong></label>
                                    <select name="decision" class="form-control" required style="margin-bottom:12px;">
                                        <option value="">-- Pilih Keputusan --</option>
                                        <option value="accept">Accept</option>
                                        <option value="reject">Reject</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label><strong>Review Notes (optional):</strong></label>
                                    <textarea name="review_notes" class="form-control" rows="3" 
                                              placeholder="Add review notes (optional)"></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-review">
                                        📝 Review Project
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Panel riwayat pengajuan project -->
    <div class="panel">
        <h3>📊 Project Submission History (<?= isset($allProjectSubmissionGroups) ? count($allProjectSubmissionGroups) : 0 ?>)</h3>
        
        <?php if (empty($allProjectSubmissionGroups)): ?>
            <div class="empty-state">
                <i>📋</i>
                <h4>No project submission history available</h4>
                <p>Team lead has not submitted any projects for review.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Team Lead</th>
                        <th>Total Cards to Review</th>
                        <th>Status</th>
                        <th>Last Reviewed At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allProjectSubmissionGroups as $pid => $group): ?>
                        <?php $project = $group['project']; $counts = $group['counts']; $status = $group['status']; ?>
                        <tr>
                            <td><strong><?= Html::encode($project ? $project->project_name : '-') ?></strong></td>
                            <td><?= Html::encode($project && $project->teamLead ? $project->teamLead->full_name : '-') ?></td>
                            <td><?= (int)($counts['accepted'] + $counts['rejected']) ?></td>
                            <td>
                                <?php if ($status === 'accepted'): ?>
                                    <span class="label label-success">Accepted</span>
                                <?php elseif ($status === 'rejected'): ?>
                                    <span class="label label-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="label label-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $group['last_reviewed_at'] 
                                    ? '<small>' . Html::encode(Yii::$app->formatter->asDatetime($group['last_reviewed_at'])) . '</small>'
                                    : '<span class="muted">-</span>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Tidak ada konfirmasi tambahan saat submit review
document.addEventListener('DOMContentLoaded', function() {});
</script>