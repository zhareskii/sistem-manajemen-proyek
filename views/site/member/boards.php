<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $cardsByStatus */
/** @var app\models\Project[] $projects */

$this->title = 'Task Boards';
?>

<style>
:root {
    --primary: rgb(37,49,109);
    --secondary: rgb(95,111,148);
    --accent: rgb(151,210,236);
    --white: #fff;
}

.feature-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30px;
}
.feature-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
}

/* Board Container */
.boards-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 40px;
    align-items: start;
}

/* Board Column */
.board-column {
    background: var(--white);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(37,49,109,0.08);
    display: flex;
    flex-direction: column;
    height: fit-content;
    min-height: 200px;
}

.board-column.todo { border-top: 4px solid #6c757d; }
.board-column.in_progress { border-top: 4px solid #17a2b8; }
.board-column.review { border-top: 4px solid #ffc107; }
.board-column.done { border-top: 4px solid #28a745; }

.board-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(151,210,236,0.3);
}

.board-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.board-count {
    background: rgba(151,210,236,0.2);
    color: var(--primary);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

/* Card Item */
.card-item {
    background: var(--white);
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 12px rgba(37,49,109,0.1);
    border: 1px solid rgba(151,210,236,0.3);
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 16px;
}

.card-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(37,49,109,0.15);
    border-color: var(--accent);
}

.card-title {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 8px;
    font-size: 1rem;
    line-height: 1.4;
}

.card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 0.85rem;
}

.card-project {
    background: rgba(151,210,236,0.2);
    color: var(--primary);
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 500;
}

.card-date {
    color: var(--secondary);
    font-weight: 500;
}

.card-details {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(151,210,236,0.2);
    font-size: 0.8rem;
    color: var(--secondary);
}

/* Priority Indicators */
.priority-high { border-left: 4px solid #dc3545; }
.priority-medium { border-left: 4px solid #ffc107; }
.priority-low { border-left: 4px solid #28a745; }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--secondary);
}

.empty-state .icon {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state .text {
    font-size: 1rem;
    font-weight: 500;
}

/* Overlay */
.overlay { 
    position: fixed; 
    inset: 0; 
    display: none; 
    align-items: center; 
    justify-content: center; 
    background: rgba(0,0,0,.35); 
    z-index: 1050; 
}
.overlay-card { 
    width: 600px; 
    max-width: 90vw; 
    background: #fff; 
    border-radius: 16px; 
    box-shadow: 0 12px 42px rgba(37,49,109,.25); 
    overflow: hidden; 
}
.overlay-head { 
    background: var(--accent); 
    padding: 20px 24px; 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
}
.overlay-title { 
    font-size: 1.4rem; 
    font-weight: 700; 
    color: var(--primary); 
}
.overlay-close { 
    background: transparent; 
    border: none; 
    color: var(--primary); 
    font-size: 24px; 
    font-weight: 700; 
    cursor: pointer; 
    padding: 5px;
}
.overlay-body { 
    padding: 24px; 
    max-height: 70vh;
    overflow-y: auto;
}

.detail-section {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(151,210,236,0.3);
}

.detail-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.detail-value {
    color: var(--secondary);
    line-height: 1.5;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-top: 15px;
}

/* Responsive */
@media (max-width: 1200px) {
    .boards-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .boards-container {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="feature-bar">
    <div class="feature-title">Board Task</div>
</div>

<div class="boards-container">
    <!-- Todo Column -->
    <div class="board-column todo">
        <div class="board-header">
            <div class="board-title">
                📋 Pending
                <span class="board-count"><?= (count($cardsByStatus['todo']) + (isset($subtasksByStatus['todo']) ? count($subtasksByStatus['todo']) : 0)) ?></span>
            </div>
        </div>
        <div class="cards-list">
            <?php if (empty($cardsByStatus['todo']) && empty($subtasksByStatus['todo'])): ?>
                <div class="empty-state">
                    <div class="icon">📋</div>
                    <div class="text">No tasks in this column</div>
                </div>
            <?php else: ?>
                <?php foreach ($cardsByStatus['todo'] as $card): ?>
                    <div class="card-item priority-<?= $card->priority ?? 'medium' ?>" 
                         onclick="openCardDetail(<?= $card->card_id ?>)">
                        <div class="card-title"><?= Html::encode($card->card_title) ?></div>
                        <div class="card-meta">
                            <span class="card-project">
                                <?= $card->board && $card->board->project ? Html::encode($card->board->project->project_name) : 'No project' ?>
                            </span>
                            <span class="card-date">
                                <?= $card->due_date ? date('d M', strtotime($card->due_date)) : 'No due date' ?>
                            </span>
                        </div>
                        <div class="card-details">
                            <span><?= Html::encode($card->assigned_role) ?></span>
                            <span>•</span>
                            <span><?= $card->estimated_hours ? $card->estimated_hours . 'j' : 'No estimate' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($subtasksByStatus['todo'] as $st): ?>
                    <div class="card-item" onclick="openSubtaskDetail(<?= (int)$st->subtask_id ?>)">
                        <div class="card-title">🧩 <?= Html::encode($st->subtask_title) ?></div>
                        <div class="card-meta">
                            <span class="card-project"><?= Html::encode($st->card && $st->card->board && $st->card->board->project ? $st->card->board->project->project_name : 'Tanpa Proyek') ?></span>
                            <span class="card-date"><?= $st->due_date ? date('d M', strtotime($st->due_date)) : 'No due date' ?></span>
                        </div>
                        <div class="card-details">
                            <span><?= Html::encode($st->card ? $st->card->card_title : '-') ?></span>
                            <span>•</span>
                            <span><?= $st->estimated_hours ? $st->estimated_hours . 'j' : 'No estimate' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- In Progress Column -->
    <div class="board-column in_progress">
        <div class="board-header">
            <div class="board-title">
                🔄 In Progress
                <span class="board-count"><?= (count($cardsByStatus['in_progress']) + (isset($subtasksByStatus['in_progress']) ? count($subtasksByStatus['in_progress']) : 0)) ?></span>
            </div>
        </div>
        <div class="cards-list">
            <?php if (empty($cardsByStatus['in_progress']) && empty($subtasksByStatus['in_progress'])): ?>
                <div class="empty-state">
                    <div class="icon">🔄</div>
                    <div class="text">No tasks in progress</div>
                </div>
            <?php else: ?>
                <?php foreach ($cardsByStatus['in_progress'] as $card): ?>
                    <div class="card-item priority-<?= $card->priority ?? 'medium' ?>" 
                         onclick="openCardDetail(<?= $card->card_id ?>)">
                        <div class="card-title"><?= Html::encode($card->card_title) ?></div>
                        <div class="card-meta">
                            <span class="card-project">
                                <?= $card->board && $card->board->project ? Html::encode($card->board->project->project_name) : 'No project' ?>
                            </span>
                            <span class="card-date">
                                <?= $card->due_date ? date('d M', strtotime($card->due_date)) : 'No due date' ?>
                            </span>
                        </div>
                        <div class="card-details">
                            <span><?= Html::encode($card->assigned_role) ?></span>
                            <span>•</span>
                            <span><?= $card->estimated_hours ? $card->estimated_hours . 'j' : 'No estimate' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($subtasksByStatus['in_progress'] as $st): ?>
                    <div class="card-item" onclick="openSubtaskDetail(<?= (int)$st->subtask_id ?>)">
                        <div class="card-title">🧩 <?= Html::encode($st->subtask_title) ?></div>
                        <div class="card-meta">
                            <span class="card-project"><?= Html::encode($st->card && $st->card->board && $st->card->board->project ? $st->card->board->project->project_name : 'Tanpa Proyek') ?></span>
                            <span class="card-date"><?= $st->due_date ? date('d M', strtotime($st->due_date)) : 'No due date' ?></span>
                        </div>
                        <div class="card-details">
                            <span><?= Html::encode($st->card ? $st->card->card_title : '-') ?></span>
                            <span>•</span>
                            <span><?= $st->estimated_hours ? $st->estimated_hours . 'j' : 'No estimate' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Review Column -->
    <div class="board-column review">
        <div class="board-header">
            <div class="board-title">
                👁️ Review
                <span class="board-count"><?= (count($cardsByStatus['review']) + (isset($subtasksByStatus['review']) ? count($subtasksByStatus['review']) : 0)) ?></span>
            </div>
        </div>
        <div class="cards-list">
            <?php if (empty($cardsByStatus['review']) && empty($subtasksByStatus['review'])): ?>
                <div class="empty-state">
                    <div class="icon">👁️</div>
                    <div class="text">No tasks for review</div>
                </div>
            <?php else: ?>
                <?php foreach ($cardsByStatus['review'] as $card): ?>
                    <div class="card-item priority-<?= $card->priority ?? 'medium' ?>" 
                         onclick="openCardDetail(<?= $card->card_id ?>)">
                        <div class="card-title"><?= Html::encode($card->card_title) ?></div>
                        <div class="card-meta">
                            <span class="card-project">
                                <?= $card->board && $card->board->project ? Html::encode($card->board->project->project_name) : 'No project' ?>
                            </span>
                            <span class="card-date">
                                <?= $card->due_date ? date('d M', strtotime($card->due_date)) : 'No due date' ?>
                            </span>
                        </div>
                        <div class="card-details">
                            <span><?= Html::encode($card->assigned_role) ?></span>
                            <span>•</span>
                            <span><?= $card->estimated_hours ? $card->estimated_hours . 'j' : 'No estimate' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($subtasksByStatus['review'] as $st): ?>
                    <div class="card-item" onclick="openSubtaskDetail(<?= (int)$st->subtask_id ?>)">
                        <div class="card-title">🧩 <?= Html::encode($st->subtask_title) ?></div>
                        <div class="card-meta">
                            <span class="card-project"><?= Html::encode($st->card && $st->card->board && $st->card->board->project ? $st->card->board->project->project_name : 'Tanpa Proyek') ?></span>
                            <span class="card-date"><?= $st->due_date ? date('d M', strtotime($st->due_date)) : 'No due date' ?></span>
                        </div>
                        <div class="card-details">
                            <span><?= Html::encode($st->card ? $st->card->card_title : '-') ?></span>
                            <span>•</span>
                            <span><?= $st->estimated_hours ? $st->estimated_hours . 'j' : 'No estimate' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Done Column -->
    <div class="board-column done">
        <div class="board-header">
            <div class="board-title">
                ✅ Done
                <span class="board-count"><?= (count($cardsByStatus['done']) + (isset($subtasksByStatus['done']) ? count($subtasksByStatus['done']) : 0)) ?></span>
            </div>
        </div>
        <div class="cards-list">
            <?php if (empty($cardsByStatus['done']) && empty($subtasksByStatus['done'])): ?>
                <div class="empty-state">
                    <div class="icon">✅</div>
                    <div class="text">No tasks done</div>
                </div>
            <?php else: ?>
                <?php foreach ($cardsByStatus['done'] as $card): ?>
                    <div class="card-item priority-<?= $card->priority ?? 'medium' ?>" 
                         onclick="openCardDetail(<?= $card->card_id ?>)">
                        <div class="card-title"><?= Html::encode($card->card_title) ?></div>
                        <div class="card-meta">
                            <span class="card-project">
                                <?= $card->board && $card->board->project ? Html::encode($card->board->project->project_name) : 'No project' ?>
                            </span>
                            <span class="card-date">
                                <?= $card->due_date ? date('d M', strtotime($card->due_date)) : 'No due date' ?>
                            </span>
                        </div>
                        <div class="card-details">
                            <span><?= Html::encode($card->assigned_role) ?></span>
                            <span>•</span>
                            <span>Aktual: <?= $card->actual_hours ? $card->actual_hours . 'j' : '0j' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($subtasksByStatus['done'] as $st): ?>
                    <div class="card-item" onclick="openSubtaskDetail(<?= (int)$st->subtask_id ?>)">
                        <div class="card-title">🧩 <?= Html::encode($st->subtask_title) ?></div>
                        <div class="card-meta">
                            <span class="card-project"><?= Html::encode($st->card && $st->card->board && $st->card->board->project ? $st->card->board->project->project_name : 'No project') ?></span>
                            <span class="card-date"><?= $st->due_date ? date('d M', strtotime($st->due_date)) : 'No due date' ?></span>
                        </div>
                        <div class="card-details">
                            <span><?= Html::encode($st->card ? $st->card->card_title : '-') ?></span>
                            <span>•</span>
                            <span>Aktual: <?= $st->actual_hours ? $st->actual_hours . 'j' : '0j' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Card Detail Overlay -->
<div id="cardDetailOverlay" class="overlay">
    <div class="overlay-card">
        <div class="overlay-head">
            <div class="overlay-title" id="cardDetailTitle">Card Detail</div>
            <button class="overlay-close" onclick="closeCardDetail()">×</button>
        </div>
        <div class="overlay-body" id="cardDetailBody">
            Loading...
        </div>
    </div>
</div>

<!-- Subtask Detail Overlay -->
<div id="subtaskDetailOverlay" class="overlay">
    <div class="overlay-card">
        <div class="overlay-head">
            <div class="overlay-title" id="subtaskDetailTitle">Subtask Detail</div>
            <button class="overlay-close" onclick="closeSubtaskDetail()">×</button>
        </div>
        <div class="overlay-body" id="subtaskDetailBody">Loading...</div>
    </div>
</div>

<script>
function openCardDetail(cardId) {
    const overlay = document.getElementById('cardDetailOverlay');
    const body = document.getElementById('cardDetailBody');
    const title = document.getElementById('cardDetailTitle');
    
    overlay.style.display = 'flex';
    body.innerHTML = 'Loading...';

    fetch('<?= Url::to(['site/get-card-detail']) ?>?id=' + cardId)
        .then(r => r.json())
        .then(d => {
            if(!d.success){ 
                body.innerHTML = '<span style="color:red">' + (d.message || 'Error loading card detail') + '</span>'; 
                return; 
            }
            
            const card = d.card;
            title.textContent = card.card_title;
            
            const createdDate = card.created_at ? new Date(card.created_at).toLocaleDateString('id-ID') : '-';
            const dueDate = card.due_date ? new Date(card.due_date).toLocaleDateString('id-ID') : '-';
            
            body.innerHTML = `
                <div class="detail-section">
                    <div class="detail-label">Deskripsi</div>
                    <div class="detail-value">${card.description || 'No description'}</div>
                </div>
                
                <div class="detail-grid">
                    <div class="detail-section">
                        <div class="detail-label">Proyek</div>
                        <div class="detail-value">${card.project_name || '-'}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Papan</div>
                        <div class="detail-value">${card.board_name || '-'}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">${card.status || 'todo'}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Prioritas</div>
                        <div class="detail-value">${card.priority || 'medium'}</div>
                    </div>
                </div>
                
                <div class="detail-grid">
                    <div class="detail-section">
                        <div class="detail-label">Role Assigned</div>
                        <div class="detail-value">${card.assigned_role || '-'}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">User Assigned</div>
                        <div class="detail-value">${card.assigned_user || 'Not assigned'}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Estimated Hours</div>
                        <div class="detail-value">${card.estimated_hours || '0'} jam</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Actual Hours</div>
                        <div class="detail-value">${card.actual_hours || '0'} jam</div>
                    </div>
                </div>
                
                <div class="detail-grid">
                    <div class="detail-section">
                        <div class="detail-label">Created On</div>
                        <div class="detail-value">${createdDate}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Due Date</div>
                        <div class="detail-value">${dueDate}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Progress</div>
                        <div class="detail-value">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                                    <div style="height: 100%; background: #28a745; width: ${card.progress_percentage || 0}%;"></div>
                                </div>
                                <span>${card.progress_percentage || 0}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${card.subtasks && card.subtasks.length > 0 ? `
                <div class="detail-section">
                    <div class="detail-label">Daftar Subtask</div>
                    <div class="detail-value">
                        ${card.subtasks.map(subtask => `
                            <div style="padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 6px;">
                                <strong>${subtask.subtask_title}</strong> - ${subtask.status}<br>
                                <small>Est: ${subtask.estimated_hours}h | Actual: ${subtask.actual_hours}h</small>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            `;
        })
        .catch(err => { 
            console.error('Error:', err);
            body.innerHTML = '<span style="color:red">Gagal memuat detail</span>'; 
        });
}

function closeCardDetail() {
    document.getElementById('cardDetailOverlay').style.display = 'none';
}

document.getElementById('cardDetailOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCardDetail();
    }
});

function openSubtaskDetail(subtaskId) {
    const overlay = document.getElementById('subtaskDetailOverlay');
    const body = document.getElementById('subtaskDetailBody');
    const title = document.getElementById('subtaskDetailTitle');
    overlay.style.display = 'flex';
    body.innerHTML = 'Loading...';
    fetch('<?= Url::to(['site/get-subtask-detail']) ?>?id=' + subtaskId)
        .then(r => r.json())
        .then(d => {
            if(!d.success){ body.innerHTML = '<span style="color:red">' + (d.message || 'Error') + '</span>'; return; }
            const st = d.subtask;
            title.textContent = st.subtask_title || 'Subtask';
            const createdDate = st.created_at ? new Date(st.created_at).toLocaleDateString('id-ID') : '-';
            const dueDate = st.due_date ? new Date(st.due_date).toLocaleDateString('id-ID') : '-';
            body.innerHTML = `
                <div class="detail-section">
                    <div class="detail-label">Deskripsi</div>
                    <div class="detail-value">${st.description || '-'}</div>
                </div>
                <div class="detail-grid">
                    <div class="detail-section">
                        <div class="detail-label">Card</div>
                        <div class="detail-value">${st.card && st.card.card_title ? st.card.card_title : '-'}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Proyek</div>
                        <div class="detail-value">${st.card && st.card.board && st.card.board.project ? st.card.board.project.project_name : '-'}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">${st.status || 'todo'}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Estimasi / Aktual</div>
                        <div class="detail-value">${(st.estimated_hours || 0) + 'h'} / ${(st.actual_hours || 0) + 'h'}</div>
                    </div>
                </div>
                <div class="detail-grid">
                    <div class="detail-section">
                        <div class="detail-label">Dibuat Pada</div>
                        <div class="detail-value">${createdDate}</div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Tenggat</div>
                        <div class="detail-value">${dueDate}</div>
                    </div>
                </div>
            `;
        })
        .catch(err => { body.innerHTML = '<span style="color:red">Gagal memuat detail</span>'; });
}

function closeSubtaskDetail() {
    document.getElementById('subtaskDetailOverlay').style.display = 'none';
}

document.getElementById('subtaskDetailOverlay').addEventListener('click', function(e) {
    if (e.target === this) { closeSubtaskDetail(); }
});
</script>