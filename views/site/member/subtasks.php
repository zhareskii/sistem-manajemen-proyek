<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var app\models\Subtask[] $allSubtasks */
/** @var app\models\Card[] $availableCards */
/** @var app\models\Subtask $subtaskModel */
/** @var app\models\Subtask[] $cardSubtasks */

$this->title = 'Subtasks';
?>

<div class="subtasks-page">
    <div class="header">
        <div class="title">Subtasks Assigned to You</div>
        <div class="subtask-count"><?= count($allSubtasks) ?> total subtasks</div>
    </div>

    <!-- Create Subtask Form -->
    <?php if (!empty($availableCards)): ?>
    <div class="box" style="margin-bottom: 20px;">
        <h3>Add New Subtask</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top:8px;">
            <div>
                <div style="font-weight:700; margin-bottom:6px; color:#1e293b;">Project</div>
                <div style="display:grid; gap:6px;">
                    <div><strong>Title:</strong> <span id="proj_title">-</span></div>
                    <div><strong>Created By:</strong> <span id="proj_created_by">-</span></div>
                    <div><strong>Description:</strong> <span id="proj_description">-</span></div>
                </div>
            </div>
            <div>
                <div style="font-weight:700; margin-bottom:6px; color:#1e293b;">Card</div>
                <div style="display:grid; gap:6px;">
                    <div><strong>Title:</strong> <span id="card_title">-</span></div>
                    <div><strong>Created By:</strong> <span id="card_created_by">-</span></div>
                    <div><strong>Description:</strong> <span id="card_description">-</span></div>
                    <div><strong>Assigned Role:</strong> <span id="card_role">-</span></div>
                    <div><strong>Deadline:</strong> <span id="card_deadline">-</span></div>
                </div>
            </div>
        </div>
        <?php $form = ActiveForm::begin([
            'action' => ['site/create-subtask'],
            'options' => ['class' => 'subtask-form']
        ]); ?>
        
        <div class="form-row">
            <div class="field">
                <label>Select Card</label>
                <?php $defaultCardId = $availableCards[0]->card_id ?? null; ?>
                <?= Html::dropDownList('Subtask[card_id]', $defaultCardId, 
                    \yii\helpers\ArrayHelper::map($availableCards, 'card_id', function($card) {
                        return $card->card_title . ' - ' . ($card->board->project->project_name ?? 'Unknown Project');
                    }), 
                    [
                        'required' => true, 
                        'class' => 'form-control',
                        'name' => 'Subtask[card_id]',
                        'id' => 'create_subtask_card_select'
                    ]
                ) ?>
            </div>
            
            <div class="field">
                <label>Subtask Title</label>
                <?= Html::textInput('Subtask[subtask_title]', '', [
                    'maxlength' => true, 
                    'placeholder' => 'Subtask title',
                    'required' => true,
                    'class' => 'form-control',
                    'name' => 'Subtask[subtask_title]'
                ]) ?>
            </div>
        </div>
        
        <div class="form-row">
            <div class="field">
                <label>Description</label>
                <?= Html::textarea('Subtask[description]', '', [
                    'rows' => 3,
                    'placeholder' => 'Subtask description',
                    'class' => 'form-control',
                    'name' => 'Subtask[description]'
                ]) ?>
            </div>
            
            <div class="field">
                <label>Estimated Hours</label>
                <?= Html::input('number', 'Subtask[estimated_hours]', '0', [
                    'step' => '0.5',
                    'min' => '0',
                    'placeholder' => 'Estimated hours',
                    'class' => 'form-control',
                    'name' => 'Subtask[estimated_hours]',
                    'value' => '0'
                ]) ?>
            </div>
        </div>
        
        <div class="form-actions">
            <?= Html::submitButton('Add Subtask', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <script>
    (function(){
        const cardSelect = document.getElementById('create_subtask_card_select');
        function fmtDate(str){
            if(!str) return '-';
            try { return new Date(str).toLocaleDateString(); } catch(e){ return str; }
        }
        function loadCardAndProject(cardId){
            if(!cardId) return;
            fetch('<?= Url::to(['site/get-card-detail']) ?>?id=' + encodeURIComponent(cardId))
            .then(r=>r.json())
            .then(d=>{
                if(!d || !d.success || !d.card) return;
                const c = d.card;
                document.getElementById('card_title').textContent = c.card_title || '-';
                document.getElementById('card_created_by').textContent = c.created_by || '-';
                document.getElementById('card_description').textContent = c.description || '-';
                document.getElementById('card_role').textContent = c.assigned_role || '-';
                document.getElementById('card_deadline').textContent = fmtDate(c.due_date);
                const pid = c.project_id;
                if(pid){
                    fetch('<?= Url::to(['site/get-project-detail']) ?>?id=' + encodeURIComponent(pid))
                    .then(rr=>rr.json())
                    .then(pd=>{
                        if(!pd || !pd.success || !pd.project) return;
                        const p = pd.project;
                        document.getElementById('proj_title').textContent = p.project_name || '-';
                        document.getElementById('proj_created_by').textContent = p.created_by || '-';
                        document.getElementById('proj_description').textContent = p.description || '-';
                    }).catch(()=>{});
                }
            }).catch(()=>{});
        }
        if(cardSelect){
            cardSelect.addEventListener('change', function(){
                loadCardAndProject(this.value);
            });
            if(cardSelect.value){ loadCardAndProject(cardSelect.value); }
        }
    })();
    </script>
    <?php else: ?>
    <div class="box" style="margin-bottom: 20px; background: #f8f9fa;">
        <p style="color: #6c757d; text-align: center; margin: 0;">
            You have no assigned cards. Please contact the Team Lead to get tasks.
        </p>
    </div>
    <?php endif; ?>

    <!-- Daftar Subtask Saya -->
    <div class="subtask-list">
        <?php if (empty($allSubtasks)): ?>
            <div class="empty-state">
                <i>📝</i>
                <p>No subtasks have been assigned to you.</p>
            </div>
        <?php else: ?>
            <?php foreach ($allSubtasks as $subtask): ?>
                <div class="subtask-item" data-id="<?= $subtask->subtask_id ?>">
                    <div class="subtask-header">
                        <div>
                <div class="subtask-title"><?= Html::encode($subtask->subtask_title) ?></div>
                <div class="subtask-card-info">
                                Card: <?= Html::encode($subtask->card ? $subtask->card->card_title : '-') ?> • 
                                Project: <?= Html::encode($subtask->card && $subtask->card->board && $subtask->card->board->project ? $subtask->card->board->project->project_name : '-') ?> • 
                                Role: <?= Html::encode($subtask->card && $subtask->card->assigned_role ? ucfirst($subtask->card->assigned_role) : 'N/A') ?>
                </div>
            </div>
            <div class="subtask-meta">
                            <span class="badge badge-<?= str_replace('_', '-', $subtask->status) ?>">
                                <?= ucfirst(str_replace('_', ' ', $subtask->status)) ?>
                            </span>
                            <?php if (!empty($subtask->helpRequests)) : ?>
                                <span class="badge badge-help">Help Requested</span>
                            <?php endif; ?>
            </div>
        </div>
        
        <?php if ($subtask->description): ?>
            <div class="subtask-description">
                            <?= Html::encode($subtask->description) ?>
            </div>
        <?php endif; ?>
        
        <div class="subtask-stats">
            <div class="stat-item">
                            <span class="stat-label">Estimated:</span>
                            <span class="stat-value"><?= (float)$subtask->estimated_hours ?> hours</span>
            </div>
            <div class="stat-item">
                            <span class="stat-label">Actual:</span>
                            <span class="stat-value"><?= (float)$subtask->actual_hours ?> hours</span>
            </div>
            <div class="stat-item">
                            <span class="stat-label">Card Status:</span>
                            <span class="stat-value"><?= $subtask->card ? ucfirst($subtask->card->status) : '-' ?></span>
            </div>
        </div>
    </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Section untuk Subtask dalam Card Detail -->
<div class="subtasks-section">

    <?php if (empty($cardSubtasks)): ?>
    <?php else: ?>
        <div class="subtasks-list">
            <?php foreach ($cardSubtasks as $subtask): ?>
                <div class="subtask-row" data-id="<?= $subtask->subtask_id ?>">
                    <div class="subtask-info">
                        <div class="subtask-title"><?= Html::encode($subtask->subtask_title) ?></div>
                        <?php if ($subtask->description): ?>
                            <div class="subtask-description"><?= Html::encode($subtask->description) ?></div>
                        <?php endif; ?>
                        <div class="subtask-meta">
                            <span class="meta-item">Estimated: <?= (float)$subtask->estimated_hours ?> hours</span>
                            <span class="meta-item">Actual: <?= (float)$subtask->actual_hours ?> hours</span>
                            <span class="meta-item">Assignee: <?= Html::encode($subtask->assignee ? $subtask->assignee->username : '-') ?> <?= $subtask->card && $subtask->card->assigned_role ? '(' . Html::encode(ucfirst($subtask->card->assigned_role)) . ')' : '' ?></span>
                        </div>
                    </div>
                    
                    <div class="subtask-actions">
                        <span class="status-badge status-<?= str_replace('_', '-', $subtask->status) ?>">
                            <?= ucfirst(str_replace('_', ' ', $subtask->status)) ?>
                        </span>
                        
                        <div class="action-buttons">
                            <button class="btn-icon comments-btn" data-id="<?= $subtask->subtask_id ?>" title="Add Comment">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                </svg>
                                <?php if ($subtask->getComments()->count() > 0): ?>
                                    <span class="badge-count"><?= $subtask->getComments()->count() ?></span>
                                <?php endif; ?>
                            </button>
                            
                            <button class="btn-icon help-btn" data-id="<?= $subtask->subtask_id ?>" title="Help Request">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                                <?php if (!empty($subtask->helpRequests)): ?>
                                    <span class="badge-count alert">!</span>
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Edit Subtask -->
<div class="modal-overlay" id="editModal">
    <div class="modal-panel">
        <div class="modal-header">
            <div class="modal-title">Edit Subtask</div>
            <button class="close-btn" id="editClose">×</button>
        </div>
        <div class="modal-body">
            <?php $form = ActiveForm::begin([
                'id' => 'edit-subtask-form',
                'action' => ['site/update-subtask'],
                'options' => ['class' => 'edit-form']
            ]); ?>
            
            <?= Html::hiddenInput('Subtask[subtask_id]', '', ['id' => 'edit-subtask-id']) ?>
            
            <div class="field">
                <label>Judul Subtask</label>
                <?= Html::textInput('Subtask[subtask_title]', '', [
                    'class' => 'form-control',
                    'id' => 'edit-subtask-title',
                    'required' => true,
                    'name' => 'Subtask[subtask_title]'
                ]) ?>
            </div>
            
            <div class="field">
                <label>Description</label>
                <?= Html::textarea('Subtask[description]', '', [
                    'class' => 'form-control',
                    'id' => 'edit-subtask-description',
                    'rows' => 3,
                    'name' => 'Subtask[description]'
                ]) ?>
            </div>
            
            <div class="form-row">
                <div class="field">
                    <label>Estimated Hours</label>
                    <?= Html::input('number', 'Subtask[estimated_hours]', '', [
                        'class' => 'form-control',
                        'id' => 'edit-subtask-estimated',
                        'step' => '0.5',
                        'min' => '0',
                        'name' => 'Subtask[estimated_hours]'
                    ]) ?>
                </div>
                
                <div class="field">
                    <label>Status</label>
                    <?= Html::dropDownList('Subtask[status]', '', [
                        'todo' => 'Todo',
                        'in_progress' => 'In Progress',
                        'done' => 'Done'
                    ], [
                        'class' => 'form-control',
                        'id' => 'edit-subtask-status',
                        'name' => 'Subtask[status]'
                    ]) ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Subtask</button>
                <button type="button" class="btn btn-outline" id="editCancel">Cancel</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Overlay Detail Subtask -->
<div class="overlay" id="subtaskOverlay">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title" id="ovTitle">Subtask Details</div>
            <div class="tabbar">
                <button class="tab active" data-tab="detail">Detail</button>
                <button class="tab" data-tab="timer" title="Time Tracking" aria-label="Time Tracking">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </button>
                <button class="tab" data-tab="comments" title="Add Comment" aria-label="Add Comment">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                </button>
                <button class="tab" data-tab="help" title="Help Request" aria-label="Help Request">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </button>
            </div>
            <button class="close-btn" id="ovClose">×</button>
        </div>
        <div class="panel-body">
            <!-- Detail Section -->
            <div class="section active" id="sec-detail">
                <div id="detailContent"></div>
                <div class="detail-actions" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                    <button id="ovEditBtn" class="btn btn-warning">Edit</button>
                    <button id="ovDeleteBtn" class="btn btn-danger">Delete</button>
                </div>
            </div>
            <!-- Timer Section -->
            <div class="section" id="sec-timer">
                <div class="timer-meta" id="ovMeta"></div>
                <div class="timer-display" id="timerDisplay">00:00:00</div>
                <div class="timer-controls" id="timerActions"></div>
                <div class="field timer-description">
                    <label>Description</label>
                    <input type="text" id="timerDesc" placeholder="Working on subtask" class="form-control">
                </div>
            </div>
            
            <!-- Comments Section -->
            <div class="section" id="sec-comments">
                <div class="comments-list" id="commentsList"></div>
                <div id="commentForm" style="margin-top: 8px;"></div>
            </div>
            
            <!-- Help Section -->
            <div class="section" id="sec-help">
                <div class="help-list" id="helpList"></div>
                <div id="helpForm" style="margin-top: 8px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Overlay untuk Komentar -->
<div class="overlay" id="commentsOverlay">
    <div class="overlay-panel">
        <div class="overlay-header">
            <h3 id="commentsTitle">Subtask Comments</h3>
            <button class="close-btn" id="commentsClose">×</button>
        </div>
        <div class="overlay-body">
            <div class="comments-list" id="overlayCommentsList"></div>
            <div class="comment-form" id="commentFormContainer">
                <form id="addCommentForm">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                    <input type="hidden" name="Comment[comment_type]" value="subtask">
                    <input type="hidden" name="Comment[subtask_id]" id="commentSubtaskId">
                    <div class="field">
                        <textarea name="Comment[comment_text]" rows="3" placeholder="Tulis komentar Anda..." required class="form-control"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Send Comment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Overlay untuk Help Request -->
<div class="overlay" id="helpOverlay">
    <div class="overlay-panel">
        <div class="overlay-header">
            <h3 id="helpTitle">Help Request</h3>
            <button class="close-btn" id="helpClose">×</button>
        </div>
        <div class="overlay-body">
            <div class="help-list" id="overlayHelpList"></div>
            <div id="overlayHelpFormContainer"></div>
        </div>
    </div>
</div>

<style>
.subtasks-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}

.subtask-count {
    color: #64748b;
    font-size: 14px;
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 20px;
}

.box {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.subtask-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.subtask-item {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    padding: 20px;
    transition: all 0.2s ease;
    border: 1px solid #e2e8f0;
    cursor: pointer;
}

.subtask-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.subtask-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.subtask-title {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}

.subtask-card-info {
    font-size: 14px;
    color: #64748b;
}

.subtask-description {
    color: #475569;
    margin-bottom: 12px;
    font-size: 14px;
}

.subtask-meta {
    display: flex;
    gap: 12px;
    align-items: center;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-todo {
    background: #f1f5f9;
    color: #475569;
}

.badge-in-progress {
    background: #dbeafe;
    color: #1d4ed8;
}

.badge-done {
    background: #dcfce7;
    color: #166534;
}

.badge-help {
    background: #fef3c7;
    color: #92400e;
}

.subtask-stats {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 16px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.stat-label {
    font-weight: 500;
}

.stat-value {
    font-weight: 600;
}

/* Subtasks Section Styles */
.subtasks-section {
    margin: 20px 0;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.subtasks-section h3 {
    margin-bottom: 16px;
    color: #1e293b;
    font-size: 18px;
    font-weight: 600;
}

.subtasks-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.subtask-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.subtask-row:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-color: #cbd5e1;
}

.subtask-info {
    flex: 1;
    min-width: 0;
}

.subtask-title {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
    font-size: 14px;
}

.subtask-description {
    color: #64748b;
    font-size: 13px;
    margin-bottom: 8px;
    line-height: 1.4;
}

.subtask-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #94a3b8;
}

.meta-item {
    display: flex;
    align-items: center;
}

.subtask-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-todo {
    background: #f1f5f9;
    color: #475569;
}

.status-in-progress {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-done {
    background: #dcfce7;
    color: #166534;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.btn-icon:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #374151;
}

.badge-count {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.badge-count.alert {
    background: #ef4444;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
}

.empty-state p {
    font-size: 16px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}

.field label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.field input, .field textarea, .field select {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: border 0.2s ease;
    width: 100%;
}

.field input:focus, .field textarea:focus, .field select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal-overlay.active {
    display: flex;
}

.modal-panel {
    width: 100%;
    max-width: 500px;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
}

.modal-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
}

.modal-body {
    padding: 20px;
}

/* Overlay Styles */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.overlay.active {
    display: flex;
}

.overlay-panel {
    width: 100%;
    max-width: 500px;
    max-height: 80vh;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
}

.overlay-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
}

.overlay-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}

.overlay-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.panel {
    width: 100%;
    max-width: 800px;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
}

.panel-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
}

.tabbar {
    display: flex;
    gap: 8px;
}

.tab {
    padding: 6px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: #f8fafc;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s ease;
}

.tab.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.close-btn {
    border: none;
    background: transparent;
    font-size: 24px;
    cursor: pointer;
    color: #64748b;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.close-btn:hover {
    background: #f1f5f9;
}

.panel-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.section {
    display: none;
}

.section.active {
    display: block;
}

/* Timer Button Styles */
.timer-btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    font-size: 14px;
    min-width: 140px;
}

.timer-btn.start {
    background: #10b981;
    color: white;
}

.timer-btn.start:hover {
    background: #059669;
    transform: translateY(-1px);
}

.timer-btn.stop {
    background: #ef4444;
    color: white;
}

.timer-btn.stop:hover {
    background: #dc2626;
    transform: translateY(-1px);
}

.timer-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.timer-btn:disabled:hover {
    background: #9ca3af;
    transform: none;
}

/* Timer Section */
.timer-meta {
    margin-bottom: 16px;
    font-size: 14px;
    color: #64748b;
}

.timer-display {
    font-size: 32px;
    font-weight: 700;
    text-align: center;
    margin: 20px 0;
    color: #1e293b;
    font-family: 'Courier New', monospace;
}

.timer-controls {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-bottom: 20px;
}

.timer-description {
    margin-top: 16px;
}

/* Comments Styles */
.comments-list {
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
}

.comment-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    background: #f8fafc;
}

.comment-text {
    margin-bottom: 8px;
    color: #374151;
    font-size: 14px;
}

.comment-meta {
    font-size: 11px;
    color: #94a3b8;
    display: flex;
    justify-content: space-between;
}

.comment-form {
    border-top: 1px solid #e2e8f0;
    padding-top: 16px;
}

/* Help Request Styles */
.help-list {
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
}

.help-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    background: #f8fafc;
}

.help-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 12px;
    color: #64748b;
}

.help-content {
    color: #374151;
    font-size: 14px;
}

.help-form {
    margin-top: 16px;
}

.panel-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 16px;
    border-top: 1px solid #e2e8f0;
    padding-top: 12px;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.btn-danger {
    background: #ef4444;
    color: white;
    border: 1px solid #ef4444;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-outline {
    background: transparent;
    border: 1px solid #cbd5e1;
    color: #475569;
}

.btn-outline:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border: 1px solid #3b82f6;
}

.btn-primary:hover {
    background: #2563eb;
}

/* Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .subtask-header {
        flex-direction: column;
        gap: 12px;
    }
    
    .subtask-stats {
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .subtask-row {
        flex-direction: column;
        gap: 12px;
    }
    
    .subtask-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .subtask-meta {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .overlay-panel {
        margin: 10px;
        max-height: 90vh;
    }
    
    .panel-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
    
    .tabbar {
        width: 100%;
        justify-content: center;
    }
    
    .timer-btn {
        min-width: 120px;
        padding: 10px 16px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('subtaskOverlay');
    const editModal = document.getElementById('editModal');
    const commentsOverlay = document.getElementById('commentsOverlay');
    const helpOverlay = document.getElementById('helpOverlay');
    const ovClose = document.getElementById('ovClose');
    const editClose = document.getElementById('editClose');
    const editCancel = document.getElementById('editCancel');
    const commentsClose = document.getElementById('commentsClose');
    const helpClose = document.getElementById('helpClose');

    let currentSubtaskId = null;
    let timerInterval = null;
    let timerSeconds = 0;
    let currentLogId = null;

    // Check for running timer on page load
    checkRunningTimerOnLoad();

    function checkRunningTimerOnLoad() {
        fetch('<?= Url::to(['site/check-running-timer']) ?>', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.has_running_timer) {
                const timer = data.timer;
                currentSubtaskId = timer.subtask_id;
                currentLogId = timer.log_id;

                // Calculate elapsed time
                const startTs = timer.start_timestamp;
                const nowTs = Date.now();
                timerSeconds = Math.floor((nowTs - startTs) / 1000);

                // Start timer display
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
                timerInterval = setInterval(function() {
                    timerSeconds++;
                    updateTimerDisplay();
                }, 1000);

                // Update UI to show running timer
                updateTimerButtons('stop');
                document.getElementById('timerDesc').value = timer.description || 'Working on subtask';

                // Notification disabled
            }
        })
        .catch(error => {
            console.error('Error checking running timer:', error);
        });
    }

    // Show notification for running timer
    function showRunningTimerNotification(timer) {
        const notification = document.createElement('div');
        notification.id = 'running-timer-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            max-width: 300px;
            font-size: 14px;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <div>
                    <div style="font-weight: 600;">Timer Running</div>
                    <div style="font-size: 12px; opacity: 0.9;">${timer.subtask_title}</div>
                </div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: 8px;">×</button>
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Show/hide functions
    function showOverlay() { overlay.classList.add('active'); }
    function hideOverlay() { 
        overlay.classList.remove('active'); 
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }
    
    function showEditModal() { editModal.classList.add('active'); }
    function hideEditModal() { editModal.classList.remove('active'); }

    // Close event listeners
    ovClose.addEventListener('click', hideOverlay);
    editClose.addEventListener('click', hideEditModal);
    editCancel.addEventListener('click', hideEditModal);
    commentsClose.addEventListener('click', () => commentsOverlay.classList.remove('active'));
    helpClose.addEventListener('click', () => helpOverlay.classList.remove('active'));

    // Close when clicking outside
    overlay.addEventListener('click', (e) => { if (e.target === overlay) hideOverlay(); });
    editModal.addEventListener('click', (e) => { if (e.target === editModal) hideEditModal(); });
    commentsOverlay.addEventListener('click', (e) => { if (e.target === commentsOverlay) commentsOverlay.classList.remove('active'); });
    helpOverlay.addEventListener('click', (e) => { if (e.target === helpOverlay) helpOverlay.classList.remove('active'); });

    // Tab switching
    document.querySelectorAll('.tab').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            const tab = this.getAttribute('data-tab');
            document.getElementById('sec-' + tab).classList.add('active');
        });
    });

    // Open overlay when subtask card is clicked
    document.querySelectorAll('.subtask-item').forEach(item => {
        item.addEventListener('click', function() {
            currentSubtaskId = this.getAttribute('data-id');
            loadSubtaskDetail(currentSubtaskId);
            showOverlay();
        });
    });

    // Comments functionality for card subtasks
    document.querySelectorAll('.comments-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentSubtaskId = this.getAttribute('data-id');
            loadComments(currentSubtaskId);
            commentsOverlay.classList.add('active');
        });
    });

    // Help request functionality for card subtasks
    document.querySelectorAll('.help-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentSubtaskId = this.getAttribute('data-id');
            loadHelpRequest(currentSubtaskId);
            helpOverlay.classList.add('active');
        });
    });

    // Timer functionality
    function startTimer() {
        if (timerInterval) {
            console.log('Timer already running');
            return;
        }
        
        const description = document.getElementById('timerDesc').value || 'Working on subtask';
        
        console.log('Starting timer for subtask:', currentSubtaskId);
        
        fetch('<?= Url::to(['site/start-timer']) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                '_csrf': '<?= Yii::$app->request->csrfToken ?>',
                'subtask_id': currentSubtaskId,
                'description': description
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Timer start response:', data);
            if (data.success) {
                currentLogId = data.log_id;
                timerSeconds = 0;
                
                // Clear any existing interval
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
                
                // Start new interval
                timerInterval = setInterval(function() {
                    timerSeconds++;
                    updateTimerDisplay();
                }, 1000);
                
                // Update UI
                updateTimerButtons('stop');
                console.log('Timer started successfully');
            } else {
                alert(data.message || 'Gagal start timer');
                console.error('Timer start failed:', data);
            }
        })
        .catch(error => {
            console.error('Error starting timer:', error);
            alert('Error start timer: ' + error.message);
        });
    }

    function stopTimer() {
        if (!timerInterval || !currentLogId) {
            console.log('No timer running or no log ID');
            return;
        }
        
        console.log('Stopping timer, log ID:', currentLogId);
        
        fetch('<?= Url::to(['site/stop-timer']) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                '_csrf': '<?= Yii::$app->request->csrfToken ?>',
                'log_id': String(currentLogId)
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Timer stop response:', data);
            if (data.success) {
                // Clear interval
                clearInterval(timerInterval);
                timerInterval = null;
                currentLogId = null;
                
                // Update UI
                updateTimerButtons('start');
                
                // Reload subtask detail to update actual hours
                if (currentSubtaskId) {
                    loadSubtaskDetail(currentSubtaskId);
                }
                
                console.log('Timer stopped successfully');
            } else {
                alert(data.message || 'Gagal stop timer');
                console.error('Timer stop failed:', data);
            }
        })
        .catch(error => {
            console.error('Error stopping timer:', error);
            alert('Error stop timer: ' + error.message);
        });
    }

    function updateTimerButtons(state) {
        const timerActions = document.getElementById('timerActions');
        if (!timerActions) return;
        
        if (state === 'stop') {
            timerActions.innerHTML = '<button class="timer-btn stop"><i class="bi bi-stop-fill"></i> Stop</button>';
            const stopBtn = timerActions.querySelector('.timer-btn.stop');
            if (stopBtn) {
                stopBtn.addEventListener('click', stopTimer);
            }
        } else {
            timerActions.innerHTML = '<button class="timer-btn start"><i class="bi bi-play-fill"></i> Start</button>';
            const startBtn = timerActions.querySelector('.timer-btn.start');
            if (startBtn) {
                startBtn.addEventListener('click', startTimer);
            }
        }
    }

    function updateTimerDisplay() {
        const hours = Math.floor(timerSeconds / 3600);
        const minutes = Math.floor((timerSeconds % 3600) / 60);
        const seconds = timerSeconds % 60;
        
        const timerDisplayElement = document.getElementById('timerDisplay');
        if (timerDisplayElement) {
            timerDisplayElement.textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
    }

    // Load subtask detail for main overlay
    function loadSubtaskDetail(id) {
        fetch('<?= Url::to(['site/get-subtask-detail']) ?>?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Gagal memuat detail subtask');
                    return;
                }
                
                const s = data.subtask;
                window.currentSubtaskData = s;
                document.getElementById('ovTitle').textContent = s.subtask_title;
                document.getElementById('ovMeta').innerHTML = 
                    `Status: <b>${s.status}</b> • Estimasi: ${s.estimated_hours} jam • Aktual: ${s.actual_hours} jam`;
                
                // Render detail info
                const detail = document.getElementById('detailContent');
                detail.innerHTML = `
                    <div class="field">
                        <label>Description</label>
                        <div style="color:#374151;font-size:14px;">${s.description ? s.description : '-'}</div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="field">
                            <label>Card</label>
                            <div>${s.card_title}</div>
                        </div>
                        <div class="field">
                            <label>Project</label>
                            <div>${s.project_name}</div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="field">
                            <label>Estimated Hours</label>
                            <div>${s.estimated_hours} jam</div>
                        </div>
                        <div class="field">
                            <label>Actual Hours</label>
                            <div>${s.actual_hours} jam</div>
                        </div>
                    </div>
                `;
                
                // Set up timer
                if (data.running_timer) {
                    currentLogId = data.running_timer.log_id;
                    const startTs = data.running_timer.start_timestamp 
                        ? Number(data.running_timer.start_timestamp) 
                        : Date.parse(String(data.running_timer.start_time).replace(' ', 'T'));
                    const nowTs = Date.now();
                    timerSeconds = Math.floor((nowTs - startTs) / 1000);
                    updateTimerDisplay();
                    
                    // Clear existing interval
                    if (timerInterval) {
                        clearInterval(timerInterval);
                    }
                    
                    // Start new interval
                    timerInterval = setInterval(function() {
                        timerSeconds++;
                        updateTimerDisplay();
                    }, 1000);
                    
                    updateTimerButtons('stop');
                } else {
                    timerSeconds = 0;
                    updateTimerDisplay();
                    updateTimerButtons('start');
                    
                    // Clear interval if exists
                    if (timerInterval) {
                        clearInterval(timerInterval);
                        timerInterval = null;
                    }
                    currentLogId = null;
                }
                
                document.getElementById('timerDesc').value = s.subtask_title;
                
                // Load comments
                renderComments(data);
                
                // Load help requests
                renderHelp(data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error load detail');
            });
    }

    function renderComments(data) {
        const commentsList = document.getElementById('commentsList');
        commentsList.innerHTML = '';
        
        (data.comments || []).forEach(comment => {
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.innerHTML = `
                <div class="comment-text">${comment.comment_text}</div>
                <div class="comment-meta">
                    <span>oleh ${comment.user_name}</span>
                    <span>${comment.created_at}</span>
                </div>
            `;
            commentsList.appendChild(div);
        });
        
        document.getElementById('commentForm').innerHTML = `
            <form method="post" action="<?= Url::to(['site/add-comment']) ?>">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                <input type="hidden" name="Comment[comment_type]" value="subtask">
                <input type="hidden" name="Comment[subtask_id]" value="${currentSubtaskId}">
                <div class="field">
                    <label>Komentar</label>
                    <textarea name="Comment[comment_text]" rows="3" required class="form-control" placeholder="Tulis komentar Anda..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Kirim Komentar</button>
                </div>
            </form>`;
    }

    function renderHelp(data) {
        const helpList = document.getElementById('helpList');
        const helpForm = document.getElementById('helpForm');

        helpList.innerHTML = '';
        helpForm.innerHTML = '';

        const helpRequests = Array.isArray(data.help_requests) ? data.help_requests : [];

        if (helpRequests.length > 0) {
            helpRequests.forEach(hr => {
                const div = document.createElement('div');
                div.className = 'help-item';
                const statusClassMap = {
                    'pending': 'status-todo',
                    'in_progress': 'status-in-progress',
                    'fixed': 'status-done',
                    'completed': 'status-done'
                };
                div.innerHTML = `
                    <div class="help-header">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="status-badge ${statusClassMap[hr.status] || 'status-todo'}">${hr.status.replace('_', ' ').toUpperCase()}</span>
                            <span>oleh ${hr.creator_name}</span>
                        </div>
                        <span>${hr.created_at}</span>
                    </div>
                    <div class="help-content">
                        <div style="margin-bottom: 8px;"><strong>Deskripsi:</strong> ${hr.issue_description}</div>
                        ${hr.resolution_notes ? `<div><strong>Catatan:</strong> ${hr.resolution_notes}</div>` : ''}
                    </div>
                `;
                helpList.appendChild(div);
            });
        } else {
            helpList.innerHTML = '<div class="empty-state" style="padding: 20px;"><p>No help requests found</p></div>';
        }

        helpForm.innerHTML = `
            <div class="help-form">
                <div style="background: #f0f9ff; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                    <p style="margin: 0; color: #0369a1; font-size: 14px;">
                        Request help if you encounter any issues while working on this subtask.
                    </p>
                </div>
                <form id="createHelpForm">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                    <input type="hidden" name="HelpRequest[subtask_id]" value="${currentSubtaskId}">
                    <div class="field">
                        <label>Deskripsi Masalah *</label>
                        <textarea name="HelpRequest[issue_description]" rows="4" required 
                                  placeholder="Describe the issue you're facing in detail..." 
                                  class="form-control"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Send Help Request</button>
                    </div>
                </form>
            </div>`;

        document.getElementById('createHelpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            createHelpRequest(this);
        });
    }

    // Load comments for overlay
    function loadComments(subtaskId) {
        fetch('<?= Url::to(['site/get-subtask-comments']) ?>?id=' + subtaskId)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal memuat komentar');
                    return;
                }

                const subtask = data.subtask;
                document.getElementById('commentsTitle').textContent = `Komentar: ${subtask.subtask_title}`;
                document.getElementById('commentSubtaskId').value = subtaskId;

                const commentsList = document.getElementById('overlayCommentsList');
                commentsList.innerHTML = '';

                if (data.comments && data.comments.length > 0) {
                    data.comments.forEach(comment => {
                        const commentItem = document.createElement('div');
                        commentItem.className = 'comment-item';
                        commentItem.innerHTML = `
                            <div class="comment-text">${comment.comment_text}</div>
                            <div class="comment-meta">
                                <span>oleh ${comment.user_name}</span>
                                <span>${comment.created_at}</span>
                            </div>
                        `;
                        commentsList.appendChild(commentItem);
                    });
                } else {
                    commentsList.innerHTML = '<div class="empty-state" style="padding: 20px;"><p>No comments found</p></div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading comments');
            });
    }

    // Load help request for overlay
    function loadHelpRequest(subtaskId) {
        fetch('<?= Url::to(['site/get-subtask-detail']) ?>?id=' + subtaskId)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to load help request');
                    return;
                }

                const subtask = data.subtask;
                document.getElementById('helpTitle').textContent = `Help Request: ${subtask.subtask_title}`;

                const list = document.getElementById('overlayHelpList');
                const helpFormContainer = document.getElementById('overlayHelpFormContainer');

                list.innerHTML = '';
                helpFormContainer.innerHTML = '';

                const helpRequests = Array.isArray(data.help_requests) ? data.help_requests : [];

                if (helpRequests.length > 0) {
                    helpRequests.forEach(hr => {
                        const div = document.createElement('div');
                        div.className = 'help-item';
                        const statusClassMap = {
                            'pending': 'status-todo',
                            'in_progress': 'status-in-progress',
                            'fixed': 'status-done',
                            'completed': 'status-done'
                        };
                        div.innerHTML = `
                            <div class="help-header">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="status-badge ${statusClassMap[hr.status] || 'status-todo'}">${hr.status.replace('_', ' ').toUpperCase()}</span>
                                    <span>oleh ${hr.creator_name}</span>
                                </div>
                                <span>${hr.created_at}</span>
                            </div>
                            <div class="help-content">
                                <div style="margin-bottom: 8px;"><strong>Deskripsi:</strong> ${hr.issue_description}</div>
                                ${hr.resolution_notes ? `<div><strong>Catatan:</strong> ${hr.resolution_notes}</div>` : ''}
                            </div>
                        `;
                        list.appendChild(div);
                    });
                } else {
                    list.innerHTML = '<div class="empty-state" style="padding: 20px;"><p>Belum ada help request</p></div>';
                }

                helpFormContainer.innerHTML = `
                    <div class="help-form">
                        <div style="background: #f0f9ff; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                            <p style="margin: 0; color: #0369a1; font-size: 14px;">
                                Request help if you're facing any issues with this subtask.
                            </p>
                        </div>
                        <form id="createHelpForm">
                            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                            <input type="hidden" name="HelpRequest[subtask_id]" value="${subtaskId}">
                            <div class="field">
                                <label>Issue Description *</label>
                                <textarea name="HelpRequest[issue_description]" rows="4" required 
                                          placeholder="Describe the issue you're facing in detail..." 
                                          class="form-control"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Kirim Help Request</button>
                            </div>
                        </form>
                    </div>
                `;

                document.getElementById('createHelpForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    createHelpRequest(this);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error memuat help request');
            });
    }

    // Submit comment form for overlay
    document.getElementById('addCommentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('<?= Url::to(['site/add-comment']) ?>', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        })
        .then(response => {
            const ct = response.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
                return response.json();
            }
            throw new Error('Response is not JSON. Maybe session expired or access denied.');
        })
        .then(data => {
            if (data.success) {
                this.reset();
                loadComments(currentSubtaskId);
            } else {
                alert(data.message || 'Failed to add comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding comment');
        });
    });

    // Create help request
    function createHelpRequest(form) {
        const formData = new FormData(form);
        const subtaskId = formData.get('HelpRequest[subtask_id]');
        
        fetch('<?= Url::to(['site/create-help-request']) ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Help request created successfully');
                // Refresh overlay content without full reload
                if (subtaskId) {
                    loadHelpRequest(String(subtaskId));
                } else if (currentSubtaskId) {
                    loadHelpRequest(String(currentSubtaskId));
                }
            } else {
                alert(data.message || 'Failed to create help request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating help request');
        });
    }

    // Update help request
    function updateHelpRequest(form) {
        const formData = new FormData(form);
        const subtaskId = formData.get('HelpRequest[subtask_id]') || currentSubtaskId;
        
        fetch('<?= Url::to(['site/update-help-request']) ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Help request updated successfully');
                // Refresh overlay content without full reload
                if (subtaskId) {
                    loadHelpRequest(String(subtaskId));
                } else if (currentSubtaskId) {
                    loadHelpRequest(String(currentSubtaskId));
                }
            } else {
                alert(data.message || 'Failed to update help request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating help request');
        });
    }

    // Initialize
    updateTimerDisplay();

    // Overlay Edit/Delete handlers
    const ovEditBtn = document.getElementById('ovEditBtn');
    const ovDeleteBtn = document.getElementById('ovDeleteBtn');
    if (ovEditBtn) {
      ovEditBtn.addEventListener('click', function() {
        const s = window.currentSubtaskData || {};
        if (!s || !s.subtask_id) return;
        document.getElementById('edit-subtask-id').value = s.subtask_id;
        document.getElementById('edit-subtask-title').value = s.subtask_title || '';
        document.getElementById('edit-subtask-description').value = s.description || '';
        document.getElementById('edit-subtask-estimated').value = s.estimated_hours || 0;
        document.getElementById('edit-subtask-status').value = s.status || 'todo';
        showEditModal();
      });
    }
    if (ovDeleteBtn) {
      ovDeleteBtn.addEventListener('click', function() {
        const s = window.currentSubtaskData || {};
        if (!s || !s.subtask_id) return;
        if (!confirm(`Are you sure you want to delete subtask "${s.subtask_title || ''}"?`)) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= Url::to(['site/delete-subtask']) ?>?id=' + s.subtask_id;
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_csrf';
        csrfToken.value = '<?= Yii::$app->request->csrfToken ?>';
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
      });
    }
});
</script>
