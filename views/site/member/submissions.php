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
<div class="container" style="max-width: 1000px;">
    <style>
    .panel { background:#fff; border:1px solid #e9ecef; border-radius:10px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
    .panel h3 { display:flex; align-items:center; gap:8px; margin:0; }
    .panel h4 { display:flex; align-items:center; gap:8px; }
    .badge { border-radius:12px; padding:4px 8px; font-size:12px; }
    .label { display:inline-block; padding:4px 8px; border-radius:12px; }
    .label-info { background:#e3f2fd; color:#1565c0; }
    .label-success { background:#e8f5e9; color:#2e7d32; }
    .label-danger { background:#ffebee; color:#c62828; }
    .btn + .btn { margin-left:6px; }
    .muted { color:#6c757d; }
    </style>
    <h2><?= Html::encode($this->title) ?></h2>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
        <div>
            <div class="panel">
                <h3><i>📨</i> Submit Subtask to Team Lead</h3>
                <form method="post" action="<?= Url::to(['site/submissions-member']) ?>" style="margin-top:12px;">
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
                    <div class="form-group" style="margin-top:8px;">
                        <label>Submission Notes (optional)</label>
                        <textarea name="submission_notes" class="form-control" rows="3" placeholder="Add submission notes"></textarea>
                    </div>
                    <div style="margin-top:12px;">
                        <button type="submit" class="btn btn-primary">Send to Team Lead</button>
                    </div>
                </form>
            </div>

            <div class="panel" style="margin-top:16px;">
                <h3>Subtask Submission History</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Subtask</th>
                            <th>Status</th>
                            <th>Reviewer Notes</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mySubmissions as $sb): ?>
                            <tr>
                                <td><?= Html::encode(($sb->subtask && $sb->subtask->card ? $sb->subtask->card->card_title : 'Tanpa Card') . ' — ' . ($sb->subtask ? $sb->subtask->subtask_title : 'Subtask')) ?></td>
                                <td>
                                    <?php if ($sb->status === 'pending'): ?>
                                        <span class="label label-info">Pending</span>
                                    <?php elseif ($sb->status === 'accepted'): ?>
                                        <span class="label label-success">Accepted</span>
                                    <?php else: ?>
                                        <span class="label label-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode($sb->review_notes ?? '-') ?></td>
                                <td><?= Html::encode(Yii::$app->formatter->asDatetime($sb->created_at)) ?></td>
                                <td>
                                    <?php if ($sb->status === 'rejected' && $sb->subtask): ?>
                                        <form method="post" action="<?= Url::to(['site/submissions-member']) ?>" style="display:inline-block;">
                                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                                            <?= Html::hiddenInput('subtask_id', $sb->subtask->subtask_id) ?>
                                            <?= Html::hiddenInput('submission_notes', 'Pengajuan ulang setelah ditolak') ?>
                                            <button type="submit" class="btn btn-sm btn-warning">Resubmit</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="panel">
                <h3><i>📬</i> Submit Project to Admin</h3>
                <form method="post" action="<?= Url::to(['site/submissions-member']) ?>" style="margin-top:12px;">
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
                    <div class="form-group" style="margin-top:8px;">
                        <label>Submission Notes (optional)</label>
                        <textarea name="submission_notes_project" class="form-control" rows="3" placeholder="Add submission notes"></textarea>
                    </div>
                    <div style="margin-top:12px;">
                        <button type="submit" class="btn btn-success">Send Project to Admin</button>
                    </div>
                </form>
                <?php if (empty($eligibleProjects)): ?>
                    <p style="margin-top:8px;" class="muted">No projects available for submission.</p>
                <?php endif; ?>
            </div>

            <div class="panel" style="margin-top:16px;">
                <h3><i>🗂️</i> Review Subtask</h3>
                <?php if (empty($pendingSubtaskSubmissions)): ?>
                    <p class="muted" style="margin-top:8px;">No pending subtask submissions to review.</p>
                <?php else: ?>
                <table class="table table-striped" style="margin-top:8px;">
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
                                <td><?= Html::encode($ps->subtask ? $ps->subtask->subtask_title : '-') ?></td>
                                <td><?= Html::encode(($ps->subtask && $ps->subtask->card) ? $ps->subtask->card->card_title : '-') ?></td>
                                <td><?= Html::encode(($ps->submitter ? ($ps->submitter->full_name ?: $ps->submitter->username) : ('#'.$ps->submitted_by))) ?></td>
                                <td><?= Html::encode($ps->submission_notes ?: '-') ?></td>
                                <td><?= Html::encode(Yii::$app->formatter->asDatetime($ps->created_at)) ?></td>
                                <td>
                                    <form method="post" action="<?= Url::to(['site/submissions-member']) ?>" style="min-width:220px;">
                                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                                        <?= Html::hiddenInput('review_submission_id', $ps->submission_id) ?>
                                        <div class="form-group" style="margin-bottom:6px;">
                                            <select name="review_action" class="form-control" required>
                                                <option value="accept">Accept</option>
                                                <option value="reject">Reject</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-bottom:6px;">
                                            <input type="text" name="review_notes" class="form-control" placeholder="Catatan reviewer (opsional)">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="panel" style="margin-top:16px;">
                <h3><i>🗂️</i> Subtask Review History</h3>
                <?php if (empty($reviewedSubtaskSubmissions)): ?>
                    <p class="muted" style="margin-top:8px;">No subtask reviews have been performed.</p>
                <?php else: ?>
                <table class="table table-striped">
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
                                <td><?= Html::encode(($rv->subtask && $rv->subtask->card ? $rv->subtask->card->card_title : 'Without Card') . ' — ' . ($rv->subtask ? $rv->subtask->subtask_title : 'Subtask')) ?></td>
                                <td>
                                    <?php if ($rv->status === 'accepted'): ?>
                                        <span class="label label-success">Accepted</span>
                                    <?php else: ?>
                                        <span class="label label-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode($rv->submitter ? ($rv->submitter->full_name ?: $rv->submitter->username) : '-') ?></td>
                                <td><?= Html::encode(Yii::$app->formatter->asDatetime($rv->reviewed_at ?: $rv->created_at)) ?></td>
                                <td><?= Html::encode($rv->review_notes ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="panel" style="margin-top:16px;">
                <h3><i>🗂️</i> Project Submission History</h3>
                <?php if (empty($myProjectSubmissionGroups)): ?>
                    <p class="muted" style="margin-top:8px;">No project submissions have been made.</p>
                <?php else: ?>
                <table class="table table-striped">
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
                                <td><?= Html::encode($project ? $project->project_name : '-') ?></td>
                                <td><?= (int)($counts['pending'] + $counts['accepted'] + $counts['rejected']) ?></td>
                                <td>
                                    <?php if ($status === 'pending'): ?>
                                        <span class="label label-info">Pending</span>
                                    <?php elseif ($status === 'accepted'): ?>
                                        <span class="label label-success">Accepted</span>
                                    <?php else: ?>
                                        <span class="label label-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode(Yii::$app->formatter->asDatetime($group['last_submission_at'])) ?></td>
                                <td><?= $group['last_reviewed_at'] ? Html::encode(Yii::$app->formatter->asDatetime($group['last_reviewed_at'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script></script>