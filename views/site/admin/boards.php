<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $projectsByStatus */

$this->title = 'Project Boards';
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

.board-column.planning { border-top: 4px solid #6c757d; }
.board-column.active { border-top: 4px solid #17a2b8; }
.board-column.completed { border-top: 4px solid #28a745; }
.board-column.cancelled { border-top: 4px solid #dc3545; }

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

/* Project Card */
.project-card {
    background: var(--white);
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 12px rgba(37,49,109,0.1);
    border: 1px solid rgba(151,210,236,0.3);
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 16px;
}

.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(37,49,109,0.15);
    border-color: var(--accent);
}

.project-name {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 8px;
    font-size: 1rem;
    line-height: 1.4;
}

.project-meta {
    font-size: 0.85rem;
    color: var(--secondary);
    margin-bottom: 8px;
}

.project-meta div {
    margin-bottom: 4px;
}

.progress-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    margin: 8px 0;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #28a745;
    border-radius: 3px;
    transition: width 0.3s ease;
}

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

/* Drag and Drop */
.project-card[draggable="true"] {
    cursor: grab;
}

.project-card.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.board-column.drag-over {
    background-color: rgba(151,210,236,0.1);
    border: 2px dashed var(--accent);
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
    <div class="feature-title">Project Boards</div>
</div>

<div class="boards-container">
    <!-- Planning Column -->
    <div class="board-column planning" data-status="planning">
        <div class="board-header">
            <div class="board-title">
                📝 Planning
                <span class="board-count"><?= count($projectsByStatus['planning'] ?? []) ?></span>
            </div>
        </div>
        <div class="cards-list">
            <?php if (empty($projectsByStatus['planning'])): ?>
                <div class="empty-state">
                    <div class="icon">📝</div>
                    <div class="text">No projects in planning</div>
                </div>
            <?php else: ?>
                <?php foreach ($projectsByStatus['planning'] as $project): ?>
                    <div class="project-card" data-project-id="<?= $project->project_id ?>"
                         onclick="openProjectDetail(<?= $project->project_id ?>)">
                        <div class="project-name"><?= Html::encode($project->project_name) ?></div>
                        <div class="project-meta">
                            <div>Lead: <?= Html::encode($project->teamLead ? $project->teamLead->full_name : 'Unknown') ?></div>
                            <div>Deadline: <?= $project->deadline ? date('M j, Y', strtotime($project->deadline)) : 'Not set' ?></div>
                            <div>Progress: <?= (int)$project->progress_percentage ?>%</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= (int)$project->progress_percentage ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Column -->
    <div class="board-column active" data-status="active">
        <div class="board-header">
            <div class="board-title">
                🚀 Active
                <span class="board-count"><?= count($projectsByStatus['active'] ?? []) ?></span>
            </div>
        </div>
        <div class="cards-list">
            <?php if (empty($projectsByStatus['active'])): ?>
                <div class="empty-state">
                    <div class="icon">🚀</div>
                    <div class="text">No active projects</div>
                </div>
            <?php else: ?>
                <?php foreach ($projectsByStatus['active'] as $project): ?>
                    <div class="project-card" data-project-id="<?= $project->project_id ?>"
                         onclick="openProjectDetail(<?= $project->project_id ?>)">
                        <div class="project-name"><?= Html::encode($project->project_name) ?></div>
                        <div class="project-meta">
                            <div>Lead: <?= Html::encode($project->teamLead ? $project->teamLead->full_name : 'Unknown') ?></div>
                            <div>Deadline: <?= $project->deadline ? date('M j, Y', strtotime($project->deadline)) : 'Not set' ?></div>
                            <div>Progress: <?= (int)$project->progress_percentage ?>%</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= (int)$project->progress_percentage ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Completed Column -->
    <div class="board-column completed" data-status="completed">
        <div class="board-header">
            <div class="board-title">
                ✅ Completed
                <span class="board-count"><?= count($projectsByStatus['completed'] ?? []) ?></span>
            </div>
        </div>
        <div class="cards-list">
            <?php if (empty($projectsByStatus['completed'])): ?>
                <div class="empty-state">
                    <div class="icon">✅</div>
                    <div class="text">No completed projects</div>
                </div>
            <?php else: ?>
                <?php foreach ($projectsByStatus['completed'] as $project): ?>
                    <div class="project-card" data-project-id="<?= $project->project_id ?>"
                         onclick="openProjectDetail(<?= $project->project_id ?>)">
                        <div class="project-name"><?= Html::encode($project->project_name) ?></div>
                        <div class="project-meta">
                            <div>Lead: <?= Html::encode($project->teamLead ? $project->teamLead->full_name : 'Unknown') ?></div>
                            <div>Deadline: <?= $project->deadline ? date('M j, Y', strtotime($project->deadline)) : 'Not set' ?></div>
                            <div>Progress: <?= (int)$project->progress_percentage ?>%</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= (int)$project->progress_percentage ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cancelled Column -->
    <div class="board-column cancelled" data-status="cancelled">
        <div class="board-header">
            <div class="board-title">
                ❌ Cancelled
                <span class="board-count"><?= count($projectsByStatus['cancelled'] ?? []) ?></span>
            </div>
        </div>
        <div class="cards-list">
            <?php if (empty($projectsByStatus['cancelled'])): ?>
                <div class="empty-state">
                    <div class="icon">❌</div>
                    <div class="text">No cancelled projects</div>
                </div>
            <?php else: ?>
                <?php foreach ($projectsByStatus['cancelled'] as $project): ?>
                    <div class="project-card" data-project-id="<?= $project->project_id ?>"
                         onclick="openProjectDetail(<?= $project->project_id ?>)">
                        <div class="project-name"><?= Html::encode($project->project_name) ?></div>
                        <div class="project-meta">
                            <div>Lead: <?= Html::encode($project->teamLead ? $project->teamLead->full_name : 'Unknown') ?></div>
                            <div>Deadline: <?= $project->deadline ? date('M j, Y', strtotime($project->deadline)) : 'Not set' ?></div>
                            <div>Progress: <?= (int)$project->progress_percentage ?>%</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= (int)$project->progress_percentage ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Drag and drop functionality untuk admin
document.addEventListener('DOMContentLoaded', function() {
    const projectCards = document.querySelectorAll('.project-card');
    const boardColumns = document.querySelectorAll('.board-column');

    projectCards.forEach(card => {
        card.setAttribute('draggable', 'true');

        card.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.getAttribute('data-project-id'));
            this.classList.add('dragging');
        });

        card.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });

    boardColumns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        column.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            const projectId = e.dataTransfer.getData('text/plain');
            const newStatus = this.getAttribute('data-status');

            updateProjectStatus(projectId, newStatus);
        });
    });
});

function updateProjectStatus(projectId, newStatus) {
    const formData = new FormData();
    formData.append('_csrf', '<?= Yii::$app->request->csrfToken ?>');
    formData.append('project_id', projectId);
    formData.append('status', newStatus);

    fetch('<?= Url::to(['site/update-project-status']) ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating project status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred');
    });
}

function openProjectDetail(projectId) {
    // Implementasi detail project overlay
    window.location.href = '<?= Url::to(['site/admin-projects']) ?>';
}
</script>