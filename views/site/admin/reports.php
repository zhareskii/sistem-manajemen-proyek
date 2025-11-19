<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $projectData */
/** @var array $productivityData */

$this->title = 'Admin Reports';

// Initialize variables with default values to prevent undefined errors
$projectData = $projectData ?? [];
$productivityData = $productivityData ?? [];

// Calculate summary statistics
$projectsCount = count($projectData);
$totalHours = array_sum(array_map(function($p) { return $p['total_hours'] ?? 0; }, $projectData));
$totalCards = array_sum(array_map(function($p) { return $p['card_count'] ?? 0; }, $projectData));
$totalSubtasks = array_sum(array_map(function($p) { return $p['subtask_count'] ?? 0; }, $projectData));
$membersCount = count($productivityData);
?>

<style>
:root {
    --primary: rgb(37,49,109);
    --secondary: rgb(95,111,148);
    --accent: rgb(151,210,236);
    --bg: rgb(254,245,172);
    --white: #fff;
    --success: #28a745;
    --warning: #ffc107;
    --danger: #dc3545;
    --info: #17a2b8;
    --light-gray: #f8f9fa;
    --border-gray: #e9ecef;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--white);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
}

.reports-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: var(--white);
    border-radius: 12px;
    padding: 20px;
    border: 2px solid var(--border-gray);
    text-align: center;
    transition: all 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(37,49,109,0.15);
    border-color: var(--accent);
}

.summary-label {
    color: var(--secondary);
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.summary-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
}

.page-header {
    background: var(--white);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 12px rgba(37,49,109,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header-left h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin: 0 0 8px 0;
}

.header-left p {
    color: var(--secondary);
    font-size: 0.95rem;
}

.print-btn {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(37,49,109,0.2);
}

.print-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37,49,109,0.3);
    color: white;
    text-decoration: none;
}

.section-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--primary);
    margin: 40px 0 25px 0;
    padding-bottom: 15px;
    border-bottom: 3px solid var(--accent);
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.project-card {
    background: var(--white);
    border-radius: 12px;
    padding: 20px;
    border: 2px solid var(--border-gray);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.project-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(180deg, var(--primary), var(--accent));
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(37,49,109,0.15);
    border-color: var(--accent);
}

.project-name {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--primary);
    margin: 0 0 12px 0;
    padding-left: 10px;
}

.project-info {
    padding-left: 10px;
    color: var(--secondary);
    font-size: 0.9rem;
    line-height: 1.6;
}

.project-info strong {
    color: var(--primary);
    font-weight: 600;
}

.member-reports {
    background: var(--white);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 2px 12px rgba(37,49,109,0.1);
    overflow-x: auto;
}

.member-table {
    width: 100%;
    border-collapse: collapse;
}

.member-table th {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 16px;
    text-align: left;
    font-weight: 600;
    border: none;
}

.member-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-gray);
    color: var(--primary);
}

.member-table tbody tr:hover {
    background: rgba(151,210,236,0.1);
}

.member-table tbody tr:last-child td {
    border-bottom: none;
}

.metric-badge {
    display: inline-block;
    background: rgba(151,210,236,0.2);
    color: var(--primary);
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.metric-high {
    background: rgba(40, 167, 69, 0.2);
    color: #2e7d32;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--secondary);
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--white);
    border-radius: 16px;
    max-width: 900px;
    width: 95%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(37,49,109,0.3);
    overflow: hidden;
}

/* Added header overlay with gradient background */
.modal-header {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 40px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1001;
    min-height: 120px;
    background-image: 
        linear-gradient(135deg, var(--primary), var(--secondary)),
        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M0,50 Q300,0 600,50 T1200,50 L1200,120 L0,120 Z" fill="rgba(255,255,255,0.1)"/></svg>');
    background-size: cover, cover;
    background-position: center, center;
}

.modal-header h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    padding: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border-radius: 8px;
}

.close-btn:hover {
    background: rgba(255,255,255,0.2);
}

.modal-body {
    padding: 30px;
    max-height: calc(90vh - 200px);
    overflow-y: auto;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 20px 30px;
    border-top: 1px solid var(--border-gray);
    background: var(--light-gray);
    border-radius: 0 0 16px 16px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37,49,109,0.2);
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background: var(--light-gray);
    color: var(--primary);
    border: 1px solid var(--border-gray);
}

.btn-secondary:hover {
    background: var(--border-gray);
}

.project-detail {
    margin-bottom: 25px;
}

.detail-label {
    font-size: 0.85rem;
    color: var(--secondary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 1rem;
    color: var(--primary);
    font-weight: 600;
    margin-top: 4px;
    margin-bottom: 12px;
}

.cards-section {
    background: rgba(151,210,236,0.1);
    border-radius: 12px;
    padding: 15px;
    margin-top: 20px;
}

.card-item {
    background: white;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    border-left: 4px solid var(--accent);
}

.card-item:last-child {
    margin-bottom: 0;
}

.card-title {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 6px;
}

.subtask-list {
    margin-left: 15px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--border-gray);
}

.subtask-item {
    font-size: 0.9rem;
    color: var(--secondary);
    padding: 4px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.subtask-status {
    display: inline-block;
    width: 14px;
    height: 14px;
    border-radius: 3px;
    background: var(--warning);
}

.subtask-status.done {
    background: var(--success);
}

/* Print Dialog Modal */
.print-dialog-overlay {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    animation: fadeIn 0.3s ease;
}

.print-dialog-overlay.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.print-dialog {
    background: white;
    border-radius: 16px;
    width: 95%;
    max-width: 500px;
    box-shadow: 0 15px 50px rgba(37,49,109,0.4);
}

.dialog-header {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 20px 30px;
    border-radius: 16px 16px 0 0;
    font-size: 1.3rem;
    font-weight: 700;
}

.dialog-body {
    padding: 30px;
}

.dialog-option {
    display: flex;
    align-items: center;
    padding: 16px;
    margin-bottom: 12px;
    border: 2px solid var(--border-gray);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dialog-option input[type="radio"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    margin-right: 12px;
}

.dialog-option:hover {
    border-color: var(--accent);
    background: rgba(151,210,236,0.05);
}

.dialog-option label {
    flex: 1;
    cursor: pointer;
    margin: 0;
    font-weight: 600;
    color: var(--primary);
}

.dialog-option-desc {
    font-size: 0.85rem;
    color: var(--secondary);
    margin-left: 32px;
    margin-top: 4px;
}

.date-range {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border-gray);
}

.date-input-group {
    display: flex;
    flex-direction: column;
}

.date-input-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--secondary);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.date-input-group input {
    padding: 10px 12px;
    border: 1px solid var(--border-gray);
    border-radius: 8px;
    font-size: 0.95rem;
    color: var(--primary);
}

.dialog-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 20px 30px;
    border-top: 1px solid var(--border-gray);
    background: var(--light-gray);
    border-radius: 0 0 16px 16px;
}

.dialog-cancel {
    background: white;
    color: var(--primary);
    border: 1px solid var(--border-gray);
}

.dialog-cancel:hover {
    background: var(--light-gray);
}

.dialog-submit {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
}

.dialog-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37,49,109,0.2);
    color: white;
    text-decoration: none;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        text-align: center;
    }
    
    .header-left h1 {
        font-size: 1.5rem;
    }
    
    .projects-grid {
        grid-template-columns: 1fr;
    }
    
    .member-table {
        font-size: 0.9rem;
    }
    
    .member-table th,
    .member-table td {
        padding: 10px 12px;
    }
    
    .modal-content {
        width: 100%;
        border-radius: 16px 16px 0 0;
        max-height: 95vh;
    }

    .modal-header h2 {
        font-size: 1.5rem;
    }
}

@media print {
    body * {
        visibility: hidden;
    }
    
    .print-content,
    .print-content * {
        visibility: visible;
    }
    
    .print-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    .modal-header,
    .modal-actions,
    .print-btn {
        display: none !important;
    }
}
</style>

<div class="reports-page">
        <div class="header-left">
            <h1>Reports</h1>
            <p>Project and Team Productivity Overview</p>
        </div>
        <div class="header-right">
            <button class="print-btn" onclick="openPrintDialog()">
                🖨️ Print Report
            </button>
        </div>

    <!-- Project Reports Section -->
    <h2 class="section-title">Project Reports</h2>
    
    <?php if (empty($projectData)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📁</div>
            <p style="font-size: 1.1rem; font-weight: 600;">No projects found</p>
        </div>
    <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projectData as $data): ?>
                <div class="project-card" onclick="openProjectModal(<?= htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8') ?>)">
                    <div class="project-name"><?= Html::encode($data['project']->project_name ?? ($data['project']['project_name'] ?? 'Untitled Project')) ?></div>
                    <div class="project-info">
                        <div><strong>Created by:</strong> <?= Html::encode(($data['project']->createdBy->full_name ?? $data['project']->creator->full_name ?? ($data['project']['creator']['full_name'] ?? null)) ?: 'N/A') ?></div>
                        <div><strong>Team lead:</strong> <?= Html::encode(($data['project']->teamLead->full_name ?? ($data['project']['teamLead']['full_name'] ?? null)) ?: 'N/A') ?></div>
                        <div><strong>Actual hours:</strong> <?= (float)($data['total_hours'] ?? 0) ?>h</div>
                        <div><strong>Created at:</strong> <?= isset($data['project']->created_at) ? date('M d, Y', strtotime($data['project']->created_at)) : (isset($data['project']['created_at']) ? date('M d, Y', strtotime($data['project']['created_at'])) : 'N/A') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Member Reports Section -->
    <h2 class="section-title">Member Productivity Reports</h2>
    
    <?php if (empty($productivityData)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <p style="font-size: 1.1rem; font-weight: 600;">No member data available</p>
        </div>
    <?php else: ?>
        <div class="member-reports">
            <table class="member-table">
                <thead>
                    <tr>
                        <th>Member Name</th>
                        <th>Cards Created</th>
                        <th>Subtasks Created</th>
                        <th>Subtasks Completed</th>
                        <th>Actual Hours</th>
                        <th>Working Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productivityData as $member): ?>
                        <tr>
                            <td><strong><?= Html::encode($member['user_name']) ?></strong></td>
                            <td><?= $member['cards_created'] ?></td>
                            <td><?= $member['subtasks_created'] ?></td>
                            <td>
                                <span class="metric-badge metric-high">
                                    <?= $member['subtasks_completed'] ?>
                                </span>
                            </td>
                            <td><?= $member['actual_hours'] ?>h</td>
                            <td><?= $member['working_hours'] ?>h</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Project Detail Modal -->
<div id="projectModal" class="modal">
    <div class="modal-content">
        <!-- Updated modal-header with overlay gradient and larger font for project title -->
        <div class="modal-header">
            <h2 id="modalProjectName">Project Details</h2>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button class="print-btn" onclick="printSingleProject()" style="margin: 0; padding: 8px 16px; font-size: 0.9rem;">
                    🖨️ Print
                </button>
                <button class="close-btn" onclick="closeProjectModal()">×</button>
            </div>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Project details will be inserted here -->
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeProjectModal()">Close</button>
        </div>
    </div>
</div>

<!-- Print Dialog Modal -->
<div id="printDialog" class="print-dialog-overlay">
    <div class="print-dialog">
        <div class="dialog-header">
            Select Print Options
        </div>
        <div class="dialog-body">
            <div class="dialog-option">
                <input type="radio" id="printAll" name="printType" value="all" checked>
                <label for="printAll">
                    <div>Print Entire Report</div>
                    <div class="dialog-option-desc">All projects and member productivity data</div>
                </label>
            </div>
            
            <div class="dialog-option">
                <input type="radio" id="printProjects" name="printType" value="projects">
                <label for="printProjects">
                    <div>Print Projects Only</div>
                    <div class="dialog-option-desc">Project data and statistics</div>
                </label>
            </div>
            
            <div class="dialog-option">
                <input type="radio" id="printMembers" name="printType" value="members">
                <label for="printMembers">
                    <div>Print Member Productivity</div>
                    <div class="dialog-option-desc">Team member performance metrics</div>
                </label>
            </div>
            
            <div class="date-range">
                <div class="date-input-group">
                    <label for="startDate">From Date</label>
                    <input type="date" id="startDate">
                </div>
                <div class="date-input-group">
                    <label for="endDate">To Date</label>
                    <input type="date" id="endDate">
                </div>
            </div>
        </div>
        <div class="dialog-footer">
            <button class="btn dialog-cancel" onclick="closePrintDialog()">Cancel</button>
            <button class="btn dialog-submit" onclick="executePrint()">Print</button>
        </div>
    </div>
</div>

<script>
let currentProjectData = null;
const allProjectsData = <?= json_encode($projectData) ?>;
const allMembersData = <?= json_encode($productivityData) ?>;

function openProjectModal(projectData) {
    currentProjectData = projectData;
    document.getElementById('projectModal').classList.add('active');
    
    // Safe data extraction dengan default values
    const project = projectData.project || {};
    const cards = projectData.cards || [];
    
    // Safe project name
    const projectName = project.project_name || 'Untitled Project';
    document.getElementById('modalProjectName').textContent = projectName;

    // Safe user data extraction dengan multiple fallback
    const creatorName = getSafeValue(project, 'creator.full_name') || 
                       getSafeValue(project, 'createdBy.full_name') || 
                       getSafeValue(project, 'created_by.full_name') ||
                       'N/A';
    
    const teamLeadName = getSafeValue(project, 'teamLead.full_name') || 
                        getSafeValue(project, 'team_lead.full_name') ||
                        'N/A';

    // Safe project basic information
    const difficulty = project.difficulty_level || 'Not specified';
    const progress = typeof project.progress_percentage !== 'undefined' && project.progress_percentage !== null ? 
                    `${project.progress_percentage}%` : '0%';
    const deadline = project.deadline ? new Date(project.deadline).toLocaleDateString() : 'Not set';
    const description = project.description || 'No description provided';
    const status = project.status || 'Not specified';
    const createdAt = project.created_at ? new Date(project.created_at).toLocaleDateString() : 'N/A';
    const updatedAt = project.updated_at ? new Date(project.updated_at).toLocaleDateString() : 'N/A';

    const cardCount = cards.length || 0;

    let modalHTML = `
        <!-- Project Basic Information -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div>
                <div class="project-detail">
                    <div class="detail-label">Project Name</div>
                    <div class="detail-value">${projectName}</div>
                </div>

                <div class="project-detail">
                    <div class="detail-label">Created By</div>
                    <div class="detail-value">${creatorName}</div>
                </div>

                <div class="project-detail">
                    <div class="detail-label">Team Lead</div>
                    <div class="detail-value">${teamLeadName}</div>
                </div>

                <div class="project-detail">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span style="padding: 4px 8px; border-radius: 4px; background: ${
                            status === 'completed' ? 'var(--success)' : 
                            status === 'active' ? 'var(--info)' : 
                            status === 'planning' ? 'var(--warning)' : 
                            'var(--secondary)'
                        }; color: white; font-size: 0.8rem;">
                            ${status.toUpperCase()}
                        </span>
                    </div>
                </div>

                <div class="project-detail">
                    <div class="detail-label">Difficulty Level</div>
                    <div class="detail-value">
                        <span style="color: ${
                            difficulty === 'easy' ? 'var(--success)' : 
                            difficulty === 'medium' ? 'var(--warning)' : 
                            'var(--danger)'
                        }; font-weight: bold;">
                            ${difficulty.toUpperCase()}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <div class="project-detail">
                    <div class="detail-label">Progress</div>
                    <div class="detail-value">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; background: var(--border-gray); height: 8px; border-radius: 4px;">
                                <div style="background: var(--success); height: 100%; width: ${progress}; border-radius: 4px;"></div>
                            </div>
                            <span style="font-weight: bold;">${progress}</span>
                        </div>
                    </div>
                </div>

                <div class="project-detail">
                    <div class="detail-label">Deadline</div>
                    <div class="detail-value">${deadline}</div>
                </div>

                <div class="project-detail">
                    <div class="detail-label">Created At</div>
                    <div class="detail-value">${createdAt}</div>
                </div>

                <div class="project-detail">
                    <div class="detail-label">Last Updated</div>
                    <div class="detail-value">${updatedAt}</div>
                </div>
            </div>
        </div>

        <!-- Project Description -->
        <div class="project-detail">
            <div class="detail-label">Description</div>
            <div class="detail-value" style="background: var(--light-gray); padding: 15px; border-radius: 8px; border-left: 4px solid var(--accent);">
                ${description}
            </div>
        </div>

        

        <!-- Cards & Subtasks Section -->
        <div class="cards-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <strong style="color: var(--primary); font-size: 1.1rem;">Cards & Subtasks (${cards.length})</strong>
            </div>
            <div>
    `;
    
    if (cards.length === 0) {
        modalHTML += `
            <div style="text-align: center; padding: 40px; color: var(--secondary);">
                <div style="font-size: 3rem; margin-bottom: 10px;">📋</div>
                <p style="font-size: 1.1rem; font-weight: 600;">No cards in this project</p>
                <p style="font-size: 0.9rem;">Start by creating cards to organize your work</p>
            </div>
        `;
    } else {
        cards.forEach((cardData, index) => {
            const card = cardData.card || cardData;
            const subtasks = cardData.subtasks || [];
            
            // Safe card data extraction
            const cardTitle = card.card_title || 'Untitled Card';
            const cardStatus = card.status || 'todo';
            const cardPriority = card.priority || 'medium';
            const cardAssignedRole = card.assigned_role || 'Not assigned';
            const cardActualHours = parseFloat(card.actual_hours) || 0;
            const cardEstimatedHours = parseFloat(card.estimated_hours) || 0;
            const cardDescription = card.description || 'No description available';
            const cardDueDate = card.due_date ? new Date(card.due_date).toLocaleDateString() : 'No due date';
            
            // Calculate card progress
            const cardSubtasks = subtasks || [];
            const totalCardSubtasks = cardSubtasks.length;
            const completedCardSubtasks = cardSubtasks.filter(st => (st.status || 'todo') === 'done').length;
            const cardProgress = totalCardSubtasks > 0 ? Math.round((completedCardSubtasks / totalCardSubtasks) * 100) : 0;

            modalHTML += `
                <div class="card-item" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: between; align-items: flex-start; gap: 15px;">
                        <div style="flex: 1;">
                            <div class="card-title">${cardTitle}</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin: 8px 0;">
                                <span style="padding: 2px 8px; background: ${
                                    cardStatus === 'done' ? 'var(--success)' : 
                                    cardStatus === 'in_progress' ? 'var(--info)' : 
                                    'var(--warning)'
                                }; color: white; border-radius: 12px; font-size: 0.7rem;">
                                    ${cardStatus.replace('_', ' ').toUpperCase()}
                                </span>
                                <span style="padding: 2px 8px; background: ${
                                    cardPriority === 'high' ? 'var(--danger)' : 
                                    cardPriority === 'medium' ? 'var(--warning)' : 
                                    'var(--info)'
                                }; color: white; border-radius: 12px; font-size: 0.7rem;">
                                    ${cardPriority.toUpperCase()}
                                </span>
                                <span style="padding: 2px 8px; background: var(--secondary); color: white; border-radius: 12px; font-size: 0.7rem;">
                                    ${cardAssignedRole}
                                </span>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--secondary); margin-bottom: 10px;">
                                ${cardDescription}
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--secondary);">
                                <div>
                                    <strong>Due:</strong> ${cardDueDate}
                                </div>
                            </div>
                        </div>
                    </div>
            `;
            
            if (subtasks.length > 0) {
                modalHTML += `
                    <div class="subtask-list" style="margin-top: 15px;">
                        <div style="font-size: 0.85rem; color: var(--primary); font-weight: 600; margin-bottom: 8px;">
                            Subtasks (${subtasks.length})
                        </div>
                `;
                
                subtasks.forEach(subtask => {
                    // Safe subtask data
                    const subtaskTitle = subtask.subtask_title || 'Untitled Subtask';
                    const subtaskStatus = subtask.status || 'todo';
                    const subtaskActualHours = parseFloat(subtask.actual_hours) || 0;
                    const subtaskEstimatedHours = parseFloat(subtask.estimated_hours) || 0;
                    const statusClass = subtaskStatus === 'done' ? 'done' : '';
                    const hoursText = subtaskActualHours > 0 ? ` (${subtaskActualHours.toFixed(1)}h)` : '';
                    const blockers = Array.isArray(subtask.blockers) ? subtask.blockers : [];
                    const helpRequests = Array.isArray(subtask.help_requests) ? subtask.help_requests : [];
                    
                    modalHTML += `
                        <div class="subtask-item" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                <span class="subtask-status ${statusClass}"></span>
                                <span style="flex: 1;">${subtaskTitle}${hoursText}</span>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--secondary); padding: 2px 6px; background: var(--light-gray); border-radius: 8px;">
                                ${subtaskStatus.replace('_', ' ')}
                            </span>
                        </div>
                    `;
                    if (blockers.length > 0) {
                        modalHTML += `
                            <div style="margin-left: 24px; margin-top: 6px;">
                                <div style="font-size: 0.8rem; color: var(--danger); font-weight: 700;">Blockers (${blockers.length})</div>
                        `;
                        blockers.forEach(b => {
                            const bStatus = (b.status || '').replace('_',' ');
                            const bCreated = b.created_at ? new Date(b.created_at).toLocaleDateString() : '';
                            modalHTML += `
                                <div style="display:flex; gap:8px; align-items:center; font-size:0.8rem; color: var(--secondary);">
                                    <span style="padding: 2px 6px; background: var(--danger); color: #fff; border-radius: 6px; font-size: 0.7rem;">${bStatus}</span>
                                    <span>${b.issue_description || ''}</span>
                                    <span style="margin-left:auto;">${b.creator_name || ''} • ${bCreated}</span>
                                </div>
                            `;
                        });
                        modalHTML += `</div>`;
                    }
                    if (helpRequests.length > 0) {
                        modalHTML += `
                            <div style="margin-left: 24px; margin-top: 6px;">
                                <div style="font-size: 0.8rem; color: var(--primary); font-weight: 700;">Help Requests (${helpRequests.length})</div>
                        `;
                        helpRequests.forEach(h => {
                            const hStatus = (h.status || '').replace('_',' ');
                            const hCreated = h.created_at ? new Date(h.created_at).toLocaleDateString() : '';
                            modalHTML += `
                                <div style="display:flex; gap:8px; align-items:center; font-size:0.8rem; color: var(--secondary);">
                                    <span style="padding: 2px 6px; background: var(--info); color: #fff; border-radius: 6px; font-size: 0.7rem;">${hStatus}</span>
                                    <span>${h.issue_description || ''}</span>
                                    <span style="margin-left:auto;">${h.creator_name || ''} • ${hCreated}</span>
                                </div>
                            `;
                        });
                        modalHTML += `</div>`;
                    }
                });
                modalHTML += '</div>';
            }
            
            modalHTML += '</div>';
        });
    }
    
    modalHTML += `
            </div>
        </div>
    `;
    
    document.getElementById('modalBody').innerHTML = modalHTML;
}

// Helper function untuk safely access nested properties
function getSafeValue(obj, path, defaultValue = 'N/A') {
    if (!obj || typeof obj !== 'object') return defaultValue;
    
    const keys = path.split('.');
    let result = obj;
    
    for (const key of keys) {
        if (result === null || result === undefined || typeof result !== 'object') {
            return defaultValue;
        }
        result = result[key];
        if (result === undefined) return defaultValue;
    }
    
    return result !== undefined && result !== null ? result : defaultValue;
}

// Helper function untuk safely access nested properties
function getSafeValue(obj, path, defaultValue = 'N/A') {
    if (!obj || typeof obj !== 'object') return defaultValue;
    
    const keys = path.split('.');
    let result = obj;
    
    for (const key of keys) {
        if (result === null || result === undefined || typeof result !== 'object') {
            return defaultValue;
        }
        result = result[key];
        if (result === undefined) return defaultValue;
    }
    
    return result !== undefined && result !== null ? result : defaultValue;
}

function closeProjectModal() {
    document.getElementById('projectModal').classList.remove('active');
    currentProjectData = null;
}

function openPrintDialog() {
    document.getElementById('printDialog').classList.add('active');
}

function closePrintDialog() {
    document.getElementById('printDialog').classList.remove('active');
}

function printSingleProject() {
    if (!currentProjectData) return;
    
    const printWindow = window.open('', '_blank');
    const project = currentProjectData.project;
    const cards = currentProjectData.cards;
    
    let printHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Project Report - ${project.project_name}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, rgb(37,49,109), rgb(95,111,148)); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                .header h1 { margin: 0; font-size: 2rem; }
                .section { margin-bottom: 25px; page-break-inside: avoid; }
                .section-title { background: rgb(37,49,109); color: white; padding: 10px 15px; border-radius: 5px; font-size: 1.2rem; font-weight: bold; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                th { background: rgb(95,111,148); color: white; font-weight: bold; }
                .detail-row { display: grid; grid-template-columns: 200px 1fr; gap: 20px; margin-bottom: 15px; }
                .detail-label { font-weight: bold; color: rgb(37,49,109); }
                .card-item { background: #f5f5f5; padding: 12px; margin-bottom: 10px; border-left: 4px solid rgb(151,210,236); border-radius: 4px; }
                .subtask-item { margin-left: 20px; padding: 6px 0; }
                .done { color: green; }
                .pending { color: orange; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>${project.project_name}</h1>
                <p>Generated on ${new Date().toLocaleDateString()}</p>
            </div>
            
            <div class="section">
                <div class="section-title">Project Information</div>
                <div class="detail-row">
                    <div class="detail-label">Created By:</div>
                    <div>${project.createdBy?.full_name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Description:</div>
                    <div>${project.description || 'No description provided'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div>${project.status}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Created At:</div>
                    <div>${new Date(project.created_at).toLocaleDateString()}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Total Hours:</div>
                    <div>${currentProjectData.total_hours}h</div>
                </div>
            </div>
    `;
    
    if (cards.length > 0) {
        printHTML += '<div class="section"><div class="section-title">Cards & Subtasks</div>';
        
        cards.forEach((cardData, index) => {
            const card = cardData.card;
            const subtasks = cardData.subtasks;
            
            printHTML += `<div class="card-item"><strong>${card.card_title}</strong> (${card.status})</div>`;
            
            if (subtasks.length > 0) {
                subtasks.forEach(subtask => {
                    const statusClass = subtask.status === 'done' ? 'done' : 'pending';
                    printHTML += `<div class="subtask-item"><span class="${statusClass}">• ${subtask.subtask_title} (${subtask.status})</span></div>`;
                });
            }
        });
        
        printHTML += '</div>';
    }
    
    printHTML += `
        </body>
        </html>
    `;
    
    printWindow.document.write(printHTML);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 250);
}

function executePrint() {
    const printType = document.querySelector('input[name="printType"]:checked').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    closePrintDialog();
    
    const printWindow = window.open('', '_blank');
    let printHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Project Management Report</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px; }
                .page { page-break-after: always; }
                .header { background: linear-gradient(135deg, rgb(37,49,109), rgb(95,111,148)); color: white; padding: 25px; border-radius: 8px; margin-bottom: 30px; text-align: center; }
                .header h1 { font-size: 2.5rem; margin-bottom: 10px; }
                .header p { font-size: 1.1rem; opacity: 0.9; }
                .section { margin-bottom: 30px; page-break-inside: avoid; }
                .section-title { background: rgb(37,49,109); color: white; padding: 12px 15px; border-radius: 5px; font-size: 1.3rem; font-weight: bold; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                th { background: rgb(95,111,148); color: white; font-weight: bold; }
                .project-item { background: #f9f9f9; padding: 15px; margin-bottom: 15px; border-left: 4px solid rgb(151,210,236); border-radius: 4px; }
                .project-item h3 { color: rgb(37,49,109); margin-bottom: 8px; }
                .detail-row { display: grid; grid-template-columns: 200px 1fr; gap: 15px; margin-bottom: 10px; font-size: 0.9rem; }
                .detail-label { font-weight: bold; color: rgb(37,49,109); }
                @media print { body { margin: 0; padding: 10px; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>📊 Project Management Report</h1>
                <p>Generated on ${new Date().toLocaleDateString()}</p>
                ${startDate && endDate ? `<p>Period: ${new Date(startDate).toLocaleDateString()} to ${new Date(endDate).toLocaleDateString()}</p>` : ''}
            </div>
    `;
    
    // Print Projects
    if (printType === 'all' || printType === 'projects') {
        printHTML += '<div class="section"><div class="section-title">📁 Project Reports</div>';
        
        allProjectsData.forEach(data => {
            const project = data.project;
            const createdBy = project.createdBy?.full_name || 'N/A';
            printHTML += `
                <div class="project-item">
                    <h3>${project.project_name}</h3>
                    <div class="detail-row">
                        <div class="detail-label">Created By:</div>
                        <div>${createdBy}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div>${project.status}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Cards / Subtasks:</div>
                        <div>${data.card_count} cards / ${data.subtask_count} subtasks</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Total Hours:</div>
                        <div>${data.total_hours}h</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Created:</div>
                        <div>${new Date(project.created_at).toLocaleDateString()}</div>
                    </div>
                </div>
            `;
        });
        
        printHTML += '</div>';
    }
    
    // Print Member Productivity
    if (printType === 'all' || printType === 'members') {
        printHTML += '<div class="section"><div class="section-title">👥 Member Productivity Report</div>';
        printHTML += `
            <table>
                <thead>
                    <tr>
                        <th>Member Name</th>
                        <th>Cards Created</th>
                        <th>Subtasks Created</th>
                        <th>Completed</th>
                        <th>Actual Hours</th>
                        <th>Working Hours</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        allMembersData.forEach(member => {
            printHTML += `
                <tr>
                    <td><strong>${member.user_name}</strong></td>
                    <td>${member.cards_created}</td>
                    <td>${member.subtasks_created}</td>
                    <td>${member.subtasks_completed}</td>
                    <td>${member.actual_hours}h</td>
                    <td>${member.working_hours}h</td>
                </tr>
            `;
        });
        
        printHTML += `
                </tbody>
            </table>
        </div>
        `;
    }
    
    printHTML += `
        </body>
        </html>
    `;
    
    printWindow.document.write(printHTML);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 250);
}

// Close modal when clicking outside
document.getElementById('projectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeProjectModal();
    }
});

document.getElementById('printDialog')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePrintDialog();
    }
});

// Set date inputs to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const lastMonth = new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0];
    document.getElementById('startDate').value = lastMonth;
    document.getElementById('endDate').value = today;
});
</script>
