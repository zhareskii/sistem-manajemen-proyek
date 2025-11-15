<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Project[] $projects */
/** @var app\models\Project $projectModel */
/** @var app\models\User[] $users */

$this->title = 'Projects';
?>

<style>
:root {
    --primary: #25316d;
    --secondary: #5f6f94;
    --accent: #fef5ac;
    --light: #f8f9fa;
    --success: #39b16a;
    --warning: #f06292;
    --info: #57a5ff;
}

.feature-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}
.feature-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--primary);
}
.add-btn {
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 10px 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}
.add-btn:hover {
    background: #1a2550;
    transform: translateY(-2px);
}
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}
.project-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(37,49,109,0.08);
    border: 1px solid rgba(37,49,109,0.08);
    transition: transform .2s ease, box-shadow .2s ease;
    cursor: pointer;
}
.project-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(37,49,109,0.12);
}
.project-header {
    background: var(--primary);
    color: white;
    padding: 16px;
}
.project-name {
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 4px;
}
.project-sub {
    font-size: 0.9rem;
    opacity: 0.9;
}
.project-body {
    padding: 16px;
}
.project-detail {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}
.detail-label {
    font-weight: 600;
    color: var(--secondary);
}
.detail-value {
    color: #333;
}
.progress-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 16px;
}
.progress-track {
    flex: 1;
    height: 8px;
    background: #eceff7;
    border-radius: 99px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    width: 0;
    background: #57a5ff;
    border-radius: 99px;
    transition: width .4s ease;
}
.progress-percent {
    color: #7b8794;
    font-weight: 600;
    font-size: .9rem;
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
    width: 860px;
    max-width: 92vw;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 42px rgba(37,49,109,.25);
    overflow: hidden;
    max-height: 90vh;
    overflow-y: auto;
}
.overlay-head {
    background: var(--primary);
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 10;
}
.overlay-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: white;
}
.overlay-body {
    padding: 22px;
    color: #263238;
}
.overlay-close {
    background: transparent;
    border: none;
    color: white;
    font-size: 22px;
    font-weight: 800;
    cursor: pointer;
}
.detail-row {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}
.detail-label {
    color: #2c3e50;
    font-weight: 700;
}
.detail-value {
    color: #2c3e50;
}
.edit-btn {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}
.edit-btn:hover {
    background: #1a2550;
}

/* Simple modal */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.25);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}
.modal-card {
    width: 640px;
    max-width: 92vw;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 12px 42px rgba(37,49,109,.25);
    overflow: hidden;
}
.modal-head {
    padding: 14px 18px;
    background: var(--primary);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-title {
    font-weight: 800;
    color: white;
}
.modal-close {
    background: none;
    border: none;
    font-size: 22px;
    font-weight: 800;
    color: white;
    cursor: pointer;
}
.modal-body {
    padding: 18px;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.form-grid .full {
    grid-column: 1 / -1;
}
.form-grid input, .form-grid textarea, .form-grid select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d8e1e8;
    border-radius: 10px;
}
.submit-btn {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}
.submit-btn:hover {
    background: #1a2550;
}
.delete-btn {
    background: #dc3545;
}
.delete-btn:hover {
    background: #c82333;
}

/* Styling untuk cards section seperti subtasks */
.cards-section {
    margin-top: 30px;
    border-top: 2px solid #eee;
    padding-top: 20px;
}

.cards-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 20px;
}

.cards-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.card-item {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e0e0e0;
    transition: transform 0.2s ease;
    cursor: default;
}

.card-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-item-title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 6px;
    color: #2c3e50;
}

.card-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 4px;
}

.card-item-details {
    font-size: 0.75rem;
    color: #888;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 4px;
}

.card-progress {
    margin-top: 8px;
}

.progress-mini {
    display: flex;
    align-items: center;
    gap: 8px;
}

.progress-track-mini {
    flex: 1;
    height: 6px;
    background: #eceff7;
    border-radius: 99px;
    overflow: hidden;
}

.progress-fill-mini {
    height: 100%;
    border-radius: 99px;
    transition: width .4s ease;
}

.progress-percent-mini {
    color: #7b8794;
    font-weight: 600;
    font-size: 0.7rem;
    min-width: 30px;
}

.no-cards {
    text-align: center;
    padding: 30px;
    color: #666;
    background: #f8f9fa;
    border-radius: 12px;
}

/* Status badges untuk card */
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    color: white;
}
.status-todo { background: #90a4ae; }
.status-in_progress { background: #57a5ff; }
.status-review { background: #ffa557; }
.status-done { background: #39b16a; }
</style>

<div class="feature-bar">
    <div class="feature-title">Projects</div>
    <button class="add-btn" onclick="openCreateModal()">+ Add Project</button>
</div>


<div class="cards-grid">
    <?php foreach ($projects as $p): ?>
        <?php
            $percent = (int)($p->progress_percentage ?? 0);
            $accent = $percent >= 70 ? '#39b16a' : ($percent >= 30 ? '#57a5ff' : '#f06292');
            $teamLeadName = $p->teamLead ? $p->teamLead->full_name : 'Not Assigned';
        ?>
        <div class="project-card" onclick="openDetail(<?= (int)$p->project_id ?>)">
            <div class="project-header">
                <div class="project-name"><?= Html::encode($p->project_name) ?></div>
                <div class="project-sub">Team Lead: <?= Html::encode($teamLeadName) ?></div>
            </div>
            <div class="project-body">
                <div class="project-detail">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><?= Html::encode($p->status) ?></div>
                </div>
                <div class="project-detail">
                    <div class="detail-label">Difficulty:</div>
                    <div class="detail-value"><?= Html::encode($p->difficulty_level) ?></div>
                </div>
                <div class="project-detail">
                    <div class="detail-label">Deadline:</div>
                    <div class="detail-value"><?= $p->deadline ? date('M j, Y', strtotime($p->deadline)) : 'Not set' ?></div>
                </div>
                <div class="progress-row">
                    <div class="progress-track">
                        <div class="progress-fill" style="width: <?= $percent ?>%; background: <?= $accent ?>"></div>
                    </div>
                    <div class="progress-percent"><?= $percent ?>%</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Detail overlay -->
<div id="detailOverlay" class="overlay">
    <div class="overlay-card">
        <div class="overlay-head">
            <div class="overlay-title" id="detailTitle">Detail Project</div>
            <button class="overlay-close" onclick="closeDetail()">×</button>
        </div>
        <div class="overlay-body" id="detailBody"></div>
        <form id="editForm" method="post" action="<?= Url::to(['site/update-project']) ?>" style="display:none; padding:0 22px 22px 22px;">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
            <input type="hidden" name="Project[project_id]" id="edit_project_id">
            <div class="form-grid">
                <div class="full"><input name="Project[project_name]" id="edit_project_name" placeholder="Project name"></div>
                <div class="full"><textarea name="Project[description]" id="edit_description" rows="3" placeholder="Description"></textarea></div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Team Lead</label>
                    <select name="Project[team_lead_id]" id="edit_team_lead_id">
                        <option value="">Select Team Lead</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->user_id ?>"><?= Html::encode($user->full_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Difficulty</label>
                    <select name="Project[difficulty_level]" id="edit_difficulty_level">
                        <option value="easy">easy</option>
                        <option value="medium">medium</option>
                        <option value="hard">hard</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Status</label>
                    <select name="Project[status]" id="edit_status">
                        <option value="planning">planning</option>
                        <option value="active">active</option>
                        <option value="completed">completed</option>
                        <option value="cancelled">cancelled</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Progress %</label>
                    <input type="number" min="0" max="100" name="Project[progress_percentage]" id="edit_progress">
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Deadline</label>
                    <input type="date" name="Project[deadline]" id="edit_deadline">
                </div>
            </div>
            <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="submit-btn" style="background:#90a4ae;color:#fff;" onclick="toggleEdit(false)">Cancel</button>
                <button type="submit" class="submit-btn">Save</button>
            </div>
        </form>
        <!-- Tombol Edit dan Delete dipindahkan ke sini, di luar overlay-body -->
        <div style="position: sticky; bottom: 0; background: white; padding: 15px 22px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px;">
            <button id="editToggleBtn" class="edit-btn" onclick="toggleEdit(true)" style="display:none;">Edit</button>
            <button id="deleteBtn" class="edit-btn delete-btn" onclick="confirmDelete()" style="display:none;">Delete</button>
        </div>
    </div>
</div>

<!-- Create modal -->
<div id="createModal" class="modal-backdrop">
  <div class="modal-card">
    <div class="modal-head">
      <div class="modal-title">Add Project</div>
      <button class="modal-close" onclick="closeCreateModal()">×</button>
    </div>
    <div class="modal-body">
      <form method="post" action="<?= Url::to(['site/create-project']) ?>">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
        <div class="form-grid">
          <div class="full">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Project Name</label>
            <input name="Project[project_name]" placeholder="Project name" required>
          </div>
          <div class="full">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Description</label>
            <textarea name="Project[description]" rows="3" placeholder="Description"></textarea>
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:6px;">Difficulty Level</label>
            <select name="Project[difficulty_level]" required>
              <option value="easy">Easy</option>
              <option value="medium" selected>Medium</option>
              <option value="hard">Hard</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:6px;">Team Lead</label>
            <select name="Project[team_lead_id]" required>
              <option value="">Select Team Lead</option>
              <?php foreach ($users as $user): ?>
                <option value="<?= $user->user_id ?>"><?= Html::encode($user->full_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="full">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Deadline</label>
            <input type="date" name="Project[deadline]" required>
          </div>
        </div>
        <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="submit-btn" style="background:#90a4ae;color:#fff;" onclick="closeCreateModal()">Cancel</button>
          <button type="submit" class="submit-btn">Create</button>
        </div>
      </form>
    </div>
    </div>
</div>

<!-- Delete confirmation modal -->
<div id="deleteModal" class="modal-backdrop">
  <div class="modal-card">
    <div class="modal-head">
      <div class="modal-title">Confirm Delete</div>
      <button class="modal-close" onclick="closeDeleteModal()">×</button>
    </div>
    <div class="modal-body">
      <p style="margin: 0 0 20px 0; font-size: 1.1rem;">Are you sure you want to delete this project?</p>
      <div style="display:flex; justify-content:flex-end; gap:10px;">
        <button type="button" class="submit-btn" style="background:#90a4ae;color:#fff;" onclick="closeDeleteModal()">Cancel</button>
        <button type="button" class="submit-btn delete-btn" onclick="deleteProject()">Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
function openCreateModal(){ document.getElementById('createModal').style.display='flex'; }
function closeCreateModal(){ document.getElementById('createModal').style.display='none'; }

// Fungsi helper untuk format tanggal
function formatDate(dateString) {
  if (!dateString) return null;
  
  try {
    let date = new Date(dateString);
    
    // Jika invalid, coba parse manual format database 'YYYY-MM-DD' atau 'YYYY-MM-DD HH:mm:ss'
    if (isNaN(date.getTime())) {
      const m = (dateString || '').match(/^(\d{4})-(\d{2})-(\d{2})(?:\s+\d{2}:\d{2}:\d{2})?$/);
      if (m) {
        const y = Number(m[1]);
        const mo = Number(m[2]) - 1;
        const d = Number(m[3]);
        date = new Date(y, mo, d);
      }
    }
    
    if (isNaN(date.getTime())) {
      console.log('Invalid date:', dateString);
      return null;
    }
    
    return date.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
  } catch (e) {
    console.error('Error formatting date:', e, 'Date string:', dateString);
    return null;
  }
}

function openDetail(id){
  window.currentProjectId = id;
  const overlay = document.getElementById('detailOverlay');
  const body = document.getElementById('detailBody');
  const title = document.getElementById('detailTitle');
  const editBtn = document.getElementById('editToggleBtn');
  const deleteBtn = document.getElementById('deleteBtn');
  const form = document.getElementById('editForm');
  
  overlay.style.display = 'flex';
  body.innerHTML = 'Loading...';
  editBtn.style.display = 'none';
  deleteBtn.style.display = 'none';
  form.style.display = 'none';
  body.style.display = 'block';

  fetch('<?= Url::to(['site/get-project-detail']) ?>?id=' + id, { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('Network response was not ok');
      return r.json();
    })
  .then(d => {
      if(!d.success){ 
        body.innerHTML = '<span style="color:red">'+(d.message||'Error')+'</span>'; 
        return; 
      }
      const p = d.project;
      window.currentProjectData = p;
      title.textContent = p.project_name;
      const percent = parseInt(p.progress_percentage||0);
      const accent = percent >= 70 ? '#39b16a' : (percent >= 30 ? '#57a5ff' : '#f06292');

      // Render project members table
      let membersHTML = '';
      if (p.project_members && p.project_members.length > 0) {
        membersHTML = `
          <div class="cards-section">
            <div class="cards-title">Project Members (${p.project_members.length})</div>
            <div style="overflow-x:auto;">
              <table style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Name</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Roles</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Joined At</th>
                  </tr>
                </thead>
                <tbody>
                  ${p.project_members.map(m => `
                    <tr>
                      <td style="padding:8px; border-bottom:1px solid #f3f3f3;">${escapeHtml(m.full_name)}</td>
                      <td style="padding:8px; border-bottom:1px solid #f3f3f3;">${(m.roles||[]).length>0 ? escapeHtml(m.roles.join(', ')) : '-'}</td>
                      <td style="padding:8px; border-bottom:1px solid #f3f3f3;">${m.joined_at ? escapeHtml(m.joined_at) : '-'}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
          </div>
        `;
      } else {
        membersHTML = `
          <div class="cards-section">
            <div class="cards-title">Project Members</div>
            <div class="no-cards">No members yet</div>
          </div>
        `;
      }

      // Render team lead cards dengan data yang diminta
      let cardsHTML = '';
      if (p.team_lead_cards && p.team_lead_cards.length > 0) {
        cardsHTML = `
          <div class="cards-section">
            <div class="cards-title">Cards by Team Lead (${p.team_lead_cards.length})</div>
            <div class="cards-list">
              ${p.team_lead_cards.map(card => {
                // Debug logging untuk due_date
                console.log('Card due_date raw:', card.due_date);
                console.log('Card due_date formatted:', formatDate(card.due_date));
                
                const status = card.status || 'todo';
                const cardProgress = parseInt(card.progress_percentage || 0);
                const progressAccent = cardProgress >= 70 ? '#39b16a' : (cardProgress >= 30 ? '#57a5ff' : '#f06292');
                
                const statusColors = {
                  'done': '#39b16a',
                  'in_progress': '#57a5ff', 
                  'review': '#ffa557',
                  'todo': '#90a4ae'
                };
                
                const statusLabels = {
                  'done': 'Done',
                  'in_progress': 'In Progress',
                  'review': 'Review',
                  'todo': 'Todo'
                };

                return `
                  <div class="card-item" onclick="openCardDetail(${card.card_id})" style="border-left: 4px solid ${statusColors[status] || '#90a4ae'}">
                    <div class="card-item-title">${card.card_title || 'Untitled'}</div>
                    <div class="card-item-meta">
                      <span><i class="bi bi-person-badge"></i> Assigned Role: ${card.assigned_role || 'Not assigned'}</span>
                      <span class="status-badge status-${status}">${statusLabels[status] || status}</span>
                    </div>
                    <div class="card-item-details">
                      <span><i class="bi bi-calendar-event"></i> Deadline: ${formatDate(card.due_date) || 'Not set'}</span>
                      <span><i class="bi bi-clock-history"></i> Est: ${card.estimated_hours || 0}h</span>
                      <span><i class="bi bi-stopwatch"></i> Actual: ${card.actual_hours || 0}h</span>
                    </div>
                    <div class="card-progress">
                      <div class="progress-mini">
                        <div class="progress-track-mini">
                          <div class="progress-fill-mini" style="width:${cardProgress}%; background:${progressAccent}"></div>
                        </div>
                        <div class="progress-percent-mini"><i class="bi bi-graph-up"></i> ${cardProgress}%</div>
                      </div>
                    </div>
                  </div>
                `;
              }).join('')}
            </div>
          </div>
        `;
      } else {
        cardsHTML = `
          <div class="cards-section">
            <div class="cards-title">Cards by Team Lead</div>
            <div class="no-cards">No cards created by team lead in this project</div>
          </div>
        `;
      }

      body.innerHTML = `
        <div class="detail-row">
          <div class="detail-label">Description:</div>
          <div class="detail-value">${p.description||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Created By:</div>
          <div class="detail-value">${p.created_by||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Team Lead:</div>
          <div class="detail-value">${p.team_lead||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Difficulty Level:</div>
          <div class="detail-value">${p.difficulty_level||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Status:</div>
          <div class="detail-value">${p.status||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Progress:</div>
          <div class="detail-value">
            <div class="progress-track" style="height:10px;">
              <div class="progress-fill" style="width:${percent}%; background:${accent}"></div>
            </div>
            <div style="font-weight:700; margin-top:6px;">${percent}%</div>
          </div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Created At:</div>
          <div class="detail-value">${p.created_at||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Deadline:</div>
          <div class="detail-value">${p.deadline||'-'}</div>
        </div>
        ${membersHTML}
        ${cardsHTML}
      `;

      // prefill edit form
      document.getElementById('edit_project_id').value = p.project_id;
      document.getElementById('edit_project_name').value = p.project_name||'';
      document.getElementById('edit_description').value = p.description||'';
      document.getElementById('edit_team_lead_id').value = p.team_lead_id || '';
      document.getElementById('edit_difficulty_level').value = p.difficulty_level||'medium';
      document.getElementById('edit_status').value = p.status||'planning';
      document.getElementById('edit_progress').value = percent||0;
      document.getElementById('edit_deadline').value = (p.deadline ? p.deadline.split('/').reverse().join('-') : '');
      editBtn.style.display = 'inline-block';
      deleteBtn.style.display = 'inline-block';
      
      // Store project ID for delete function
      deleteBtn.setAttribute('data-project-id', p.project_id);
    })
    .catch(err => { 
      console.error('Error loading project detail:', err);
      body.innerHTML = '<span style="color:red">Gagal memuat detail: ' + err.message + '</span>'; 
    });
}

function closeDetail(){ document.getElementById('detailOverlay').style.display='none'; }

function toggleEdit(show){
  const f = document.getElementById('editForm');
  const body = document.getElementById('detailBody');
  const editBtn = document.getElementById('editToggleBtn');
  const deleteBtn = document.getElementById('deleteBtn');
  
  if (show) {
    // Show edit form, hide detail and buttons
    f.style.display = 'block';
    body.style.display = 'none';
    editBtn.style.display = 'none';
    deleteBtn.style.display = 'none';
  } else {
    // Show detail, hide edit form, show buttons
    f.style.display = 'none';
    body.style.display = 'block';
    editBtn.style.display = 'inline-block';
    deleteBtn.style.display = 'inline-block';
  }
}

// Render subtasks list
function renderSubtasks(subtasks){
  if (!subtasks || subtasks.length === 0) {
    return `
      <div class="cards-section">
        <div class="cards-title"><i class="bi bi-list-check"></i> Subtasks</div>
        <div class="no-cards"><i class="bi bi-card-checklist"></i> No subtasks added</div>
      </div>
    `;
  }
  return `
    <div class="cards-section">
      <div class="cards-title"><i class="bi bi-list-check"></i> Subtasks (${subtasks.length})</div>
      <div style="display:flex; flex-direction:column; gap:10px;">
        ${subtasks.map(st => `
          <div class="card-item" style="padding:10px 12px;">
            <div class="card-item-title">${escapeHtml(st.subtask_title || 'Untitled')}</div>
            <div class="card-item-meta">
              <span><i class="bi bi-flag"></i> Status: ${escapeHtml(st.status || 'todo')}</span>
              <span><i class="bi bi-calendar2-check"></i> Deadline: ${formatDate(st.due_date) || 'Not set'}</span>
            </div>
          </div>
        `).join('')}
      </div>
    </div>
  `;
}

// Open card detail within project overlay
function openCardDetail(id){
  const overlay = document.getElementById('detailOverlay');
  const body = document.getElementById('detailBody');
  const title = document.getElementById('detailTitle');
  const editBtn = document.getElementById('editToggleBtn');
  const deleteBtn = document.getElementById('deleteBtn');
  const form = document.getElementById('editForm');

  overlay.style.display = 'flex';
  body.innerHTML = 'Loading...';
  editBtn.style.display = 'none';
  deleteBtn.style.display = 'none';
  form.style.display = 'none';
  body.style.display = 'block';

  fetch('<?= Url::to(['site/get-card-detail']) ?>?id=' + id, { credentials: 'same-origin' })
    .then(r => { if (!r.ok) throw new Error('Network response was not ok'); return r.json(); })
    .then(d => {
      if (!d.success) { body.innerHTML = '<span style="color:red">'+(d.message||'Error')+'</span>'; return; }
      const c = d.card || {};
      title.textContent = c.card_title || 'Detail Card';
      const percent = parseInt(c.progress_percentage || 0);
      const accent = percent >= 70 ? '#39b16a' : (percent >= 30 ? '#57a5ff' : '#f06292');

      const detailHTML = `
        <div class="detail-row">
          <div class="detail-label"><i class="bi bi-file-text"></i> Description:</div>
          <div class="detail-value">${escapeHtml(c.description)||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label"><i class="bi bi-diagram-3"></i> Project:</div>
          <div class="detail-value">${escapeHtml(c.project_name)||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label"><i class="bi bi-calendar-event"></i> Deadline:</div>
          <div class="detail-value">${formatDate(c.due_date) || '-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label"><i class="bi bi-person"></i> Assigned User:</div>
          <div class="detail-value">${escapeHtml(c.assigned_user)||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label"><i class="bi bi-award"></i> Assigned Role:</div>
          <div class="detail-value">${escapeHtml(c.assigned_role)||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label"><i class="bi bi-flag"></i> Status:</div>
          <div class="detail-value">${escapeHtml(c.status)||'-'}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label"><i class="bi bi-speedometer2"></i> Progress:</div>
          <div class="detail-value">
            <div class="progress-track" style="height:10px;">
              <div class="progress-fill" style="width:${percent}%; background:${accent}"></div>
            </div>
            <div style="font-weight:700; margin-top:6px;">${percent}%</div>
          </div>
        </div>
        ${renderSubtasks(c.subtasks || [])}
        <div style="margin-top:14px;">
          <button type="button" class="submit-btn" onclick="openDetail(window.currentProjectId)"><i class="bi bi-arrow-left-circle"></i> Kembali ke Detail Project</button>
        </div>
      `;

      body.innerHTML = detailHTML;
    })
    .catch(err => { 
      console.error('Error loading card detail:', err);
      body.innerHTML = '<span style="color:red">Gagal memuat detail card: ' + err.message + '</span>'; 
    });
}

// Helper function untuk escape HTML
function escapeHtml(unsafe) {
  if (unsafe === null || unsafe === undefined) return '';
  return unsafe
    .toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// Delete functions
function confirmDelete(){
  document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal(){
  document.getElementById('deleteModal').style.display = 'none';
}

function deleteProject(){
  const deleteBtn = document.getElementById('deleteBtn');
  const projectId = deleteBtn.getAttribute('data-project-id');
  
  if (!projectId) {
    alert('Project ID tidak ditemukan');
    return;
  }
  
  // Create form to submit delete request
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?= Url::to(['site/delete-project']) ?>?id=' + projectId;
  
  // Add CSRF token
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_csrf';
  csrfInput.value = '<?= Yii::$app->request->csrfToken ?>';
  form.appendChild(csrfInput);
  
  // Submit form
  document.body.appendChild(form);
  form.submit();
}
</script>