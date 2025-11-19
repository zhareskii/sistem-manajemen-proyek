<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Card[] $cards */
/** @var app\models\Card $cardModel */
/** @var app\models\User[] $users */

$this->title = 'Cards';
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
.card-item {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(37,49,109,0.08);
    border: 1px solid rgba(37,49,109,0.08);
    transition: transform .2s ease, box-shadow .2s ease;
    cursor: pointer;
}
.card-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(37,49,109,0.12);
}
.card-header {
    background: var(--primary);
    color: white;
    padding: 16px;
}
.card-name {
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 4px;
}
.card-sub {
    font-size: 0.9rem;
    opacity: 0.9;
}
.card-body {
    padding: 16px;
}
.card-detail {
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
    width: 900px;
    max-width: 95vw;
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

/* Subtasks Section */
.subtasks-section {
    margin-top: 30px;
    border-top: 2px solid #eee;
    padding-top: 20px;
}
.subtasks-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 20px;
}
.subtasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
}
.subtask-column {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    border-left: 4px solid #90a4ae;
}
.subtask-column.done { border-left-color: #39b16a; }
.subtask-column.in_progress { border-left-color: #57a5ff; }
.subtask-column.review { border-left-color: #ffa557; }
.subtask-column.todo { border-left-color: #90a4ae; }
.subtask-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.subtask-title {
    font-weight: 700;
    font-size: 0.9rem;
}
.subtask-count {
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}
.subtask-item {
    background: white;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    border: 1px solid #e0e0e0;
    transition: transform 0.2s ease;
}
.subtask-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.subtask-item-title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 6px;
    color: #2c3e50;
}
.subtask-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 4px;
}
.subtask-item-details {
    font-size: 0.75rem;
    color: #888;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.no-subtasks {
    text-align: center;
    padding: 30px;
    color: #666;
    background: #f8f9fa;
    border-radius: 12px;
}

/* Comments & Help Requests */
.comment-item {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 10px;
}
.help-request-item {
    background: #fff8e1;
    border-left: 4px solid #ff9800;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 10px;
}
.help-request-item.fixed {
    background: #e8f5e9;
    border-left-color: #4caf50;
}
.help-request-item.in_progress {
    background: #e3f2fd;
    border-left-color: #2196f3;
}
.help-request-item.completed {
    background: #f1f8e9;
    border-left-color: #8bc34a;
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
/* Two column positioning helpers */
.form-grid.two-col .col-left { grid-column: 1; }
.form-grid.two-col .col-right { grid-column: 2; }
/* Lebarkan form inline agar memanjang horizontal */
.form-grid.two-col { grid-template-columns: 1.2fr 0.8fr; }
.inline-create-card .modal-card { width: 100%; max-width: none; }
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

/* Status badges */
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}
.status-pending { background: #ffc107; }
.status-in_progress { background: #2196f3; }
.status-fixed { background: #4caf50; }
.status-completed { background: #8bc34a; }

/* Tab Styles */
.tabs-container {
    margin-top: 20px;
}

.tabs-header {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 20px;
}

.tab-button {
    padding: 12px 24px;
    background: none;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.tab-button:hover {
    color: #25316d;
    background: #f8f9fa;
}

.tab-button.active {
    color: #25316d;
    border-bottom-color: #25316d;
    background: #f8f9fa;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Subtask Comments & Help Requests */
.subtask-comments-section,
.subtask-help-requests-section {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #2196f3;
}

.subtask-help-requests-section {
    border-left-color: #ff9800;
}

.subtask-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.subtask-comments-list,
.subtask-help-requests-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.subtask-comment-item,
.subtask-help-request-item {
    background: white;
    padding: 12px;
    border-radius: 6px;
    border-left: 3px solid #2196f3;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.subtask-help-request-item {
    border-left-color: #ff9800;
}

.subtask-help-request-item.fixed {
    border-left-color: #4caf50;
    background: #f1f8e9;
}

.subtask-help-request-item.in_progress {
    border-left-color: #2196f3;
    background: #e3f2fd;
}

.subtask-help-request-item.completed {
    border-left-color: #8bc34a;
    background: #f1f8e9;
}

.no-data-message {
    text-align: center;
    padding: 20px;
    color: #666;
    font-style: italic;
}
</style>

<div class="feature-bar">
    <div class="feature-title">Cards</div>
</div>


<?php
// Show inline add card form only if user is Team Lead for at least one non-completed project
$userProjects = \app\models\Project::find()->where(['team_lead_id' => Yii::$app->user->id])->andWhere(['<>','status','completed'])->all();
// Preselect project pertama (asumsi: diprioritaskan project pertama yang menjadikan user sebagai TL)
$defaultProjectId = !empty($userProjects) ? $userProjects[0]->project_id : null;
if (!empty($userProjects)):
?>
<div class="inline-create-card" style="margin-bottom: 22px;">
  <div class="modal-card" style="box-shadow:none;border:1px solid #e5e7eb;">
    <div class="modal-head" style="background:#f8fafc;color:#25316d;">
      <div class="modal-title" style="color:#25316d;">Add New Card</div>
    </div>
    <div class="modal-body">
      <!-- Informasi Project di atas form -->
      <?php
        $projectsMapInline = [];
        foreach ($userProjects as $p) {
          $projectsMapInline[$p->project_id] = [
            'name' => $p->project_name,
            'description' => $p->description,
            'deadline' => $p->deadline,
          ];
        }
        $currentProjectInline = null;
        if ($defaultProjectId) {
          foreach ($userProjects as $p) { if ($p->project_id == $defaultProjectId) { $currentProjectInline = $p; break; } }
        }
      ?>
      <div class="project-info-panel" style="border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin-bottom:12px;background:#f8fafc;">
        <div style="font-weight:700;color:#25316d;margin-bottom:6px;">Project Information</div>
        <div style="color:#25316d;"><strong>Title:</strong> <?= Html::encode($currentProjectInline ? $currentProjectInline->project_name : '-') ?></div>
        <div style="color:#415a77;margin-top:4px;"><strong>Description:</strong> <?= Html::encode($currentProjectInline && $currentProjectInline->description ? $currentProjectInline->description : '-') ?></div>
        <div style="color:#415a77;margin-top:4px;"><strong>Deadline:</strong> <?= $currentProjectInline && $currentProjectInline->deadline ? date('M j, Y', strtotime($currentProjectInline->deadline)) : '-' ?></div>
      </div>

      <form method="post" action="<?= Url::to(['site/create-card']) ?>">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
        <input type="hidden" name="Card[status]" value="todo">
        <div class="form-grid two-col">
          <div class="col-left">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Card Title</label>
            <input name="Card[card_title]" placeholder="Card title" required>
          </div>
          <div class="col-right">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Priority</label>
            <select name="Card[priority]" required>
              <option value="low">Low</option>
              <option value="medium" selected>Medium</option>
              <option value="high">High</option>
            </select>
          </div>
          <div class="col-left" style="grid-row: span 2;">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Description</label>
            <textarea name="Card[description]" rows="3" placeholder="Description"></textarea>
          </div>
          <div class="col-right">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Estimated Hours</label>
            <input type="number" step="0.01" name="Card[estimated_hours]" placeholder="Estimated hours">
          </div>
          <div class="col-left">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Project</label>
            <select name="Card[project_id]" id="projectSelectInline" required>
              <option value="">Select Project</option>
              <?php foreach ($userProjects as $project): ?>
                <option value="<?= $project->project_id ?>" <?= ($defaultProjectId === $project->project_id) ? 'selected' : '' ?>><?= Html::encode($project->project_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-right">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Assigned Role</label>
            <select name="Card[assigned_role]" id="assignedRole" required onchange="filterUsersByRole()">
              <option value="developer">Developer</option>
              <option value="designer">Designer</option>
            </select>
          </div>
          <div class="col-left">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Deadline</label>
            <input type="date" name="Card[due_date]" required min="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-right">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Assigned User</label>
            <select name="assigned_user_id" id="assignedUser" required style="width: 100%; padding: 10px 12px; border: 1px solid #d8e1e8; border-radius: 10px;">
              <option value="">Select User</option>
              <?php foreach ($users as $user): ?>
                <option value="<?= (int)$user->user_id ?>" data-role="<?= $user->role ?>"><?= Html::encode($user->full_name) ?></option>
              <?php endforeach; ?>
            </select>
            <div style="font-size:.85rem;color:#7b8794;margin-top:6px;">Select one user as developer/designer</div>
          </div>
        </div>
        <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:10px;">
          <button type="submit" class="submit-btn">Create</button>
        </div>
      </form>
    </div>
  </div>
  <div style="font-size:.9rem;color:#7b8794;margin-top:8px;">This form appears because you are the Project Team Lead.</div>
</div>
<script>
  (function(){
    const map = <?= json_encode($projectsMapInline, JSON_UNESCAPED_UNICODE) ?>;
    function formatDateISO(d){
      try { return new Date(d).toLocaleDateString(); } catch(e){ return d || '-'; }
    }
    function updatePanel(pid){
      const info = map[pid];
      const panel = document.querySelector('.inline-create-card .project-info-panel');
      if (!panel || !info) return;
      const rows = panel.querySelectorAll('div');
      // rows[1] -> Title, rows[2] -> Description, rows[3] -> Deadline
      if (rows[1]) rows[1].innerHTML = '<strong>Title:</strong> ' + (info.name || '-');
      if (rows[2]) rows[2].innerHTML = '<strong>Description:</strong> ' + (info.description || '-');
      if (rows[3]) rows[3].innerHTML = '<strong>Deadline:</strong> ' + (info.deadline ? formatDateISO(info.deadline) : '-');
    }
    document.addEventListener('DOMContentLoaded', function(){
      const sel = document.getElementById('projectSelectInline');
      if (sel){ sel.addEventListener('change', function(){ updatePanel(this.value); }); }
    });
  })();
</script>
<?php else: ?>
  <div style="background:#fff3cd;color:#856404;padding:12px;border-radius:8px;margin-bottom:20px;border:1px solid #ffeeba;">
    You are not a Team Lead on any active project, so you cannot add cards here.
  </div>
<?php endif; ?>

<div class="cards-grid">
    <?php foreach ($cards as $c): ?>
        <?php
            // Calculate progress based on subtasks
            $progress = $c->calculateProgress();
            $accent = $progress >= 70 ? '#39b16a' : ($progress >= 30 ? '#57a5ff' : '#f06292');
            $assignedUsers = $c->assignedUsers;
            $assignedUserNames = [];
            foreach ($assignedUsers as $user) {
                $assignedUserNames[] = $user->full_name;
            }
        ?>
        <div class="card-item" onclick="openDetail(<?= (int)$c->card_id ?>)">
            <div class="card-header">
                <div class="card-name"><?= Html::encode($c->card_title) ?></div>
                <div class="card-sub">
                    <div>Assigned User: <?= Html::encode(implode(', ', $assignedUserNames)) ?></div>
                    <div>Assigned Role: <?= Html::encode($c->assigned_role ?: 'N/A') ?></div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-detail">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><?= Html::encode($c->status) ?></div>
                </div>
                <div class="card-detail">
                    <div class="detail-label">Deadline:</div>
                    <div class="detail-value"><?= $c->due_date ? date('M j, Y', strtotime($c->due_date)) : 'Not set' ?></div>
                </div>
                <div class="progress-row">
                    <div class="progress-track">
                        <div class="progress-fill" style="width: <?= $progress ?>%; background: <?= $accent ?>"></div>
                    </div>
                    <div class="progress-percent"><?= $progress ?>%</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Detail overlay -->
<div id="detailOverlay" class="overlay">
    <div class="overlay-card">
        <div class="overlay-head">
            <div class="overlay-title" id="detailTitle">Detail Card</div>
            <button class="overlay-close" onclick="closeDetail()">×</button>
        </div>
        <div class="overlay-body" id="detailBody"></div>
        <form id="editForm" method="post" action="<?= Url::to(['site/update-card']) ?>" style="display:none; padding:0 22px 22px 22px;">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
            <input type="hidden" name="Card[card_id]" id="edit_card_id">
            <input type="hidden" name="Card[status]" id="edit_status" value="todo">
            <div class="form-grid">
                <div class="full"><input name="Card[card_title]" id="edit_card_title" placeholder="Card title"></div>
                <div class="full"><textarea name="Card[description]" id="edit_description" rows="3" placeholder="Description"></textarea></div>
                <div class="full">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Project</label>
                    <select name="Card[project_id]" id="edit_project_id" required>
                        <option value="">Select Project</option>
                        <?php
                        $userProjects = \app\models\Project::find()->where(['team_lead_id' => Yii::$app->user->id])->all();
                        foreach ($userProjects as $project):
                        ?>
                            <option value="<?= $project->project_id ?>"><?= Html::encode($project->project_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Assigned Role</label>
                    <select name="Card[assigned_role]" id="edit_assigned_role" onchange="filterEditUsersByRole()">
                        <option value="developer">Developer</option>
                        <option value="designer">Designer</option>
                    </select>
                </div>
                <div class="full">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Assigned User</label>
                    <select name="assigned_user_id" id="edit_assigned_user" required style="width: 100%; padding: 10px 12px; border: 1px solid #d8e1e8; border-radius: 10px;">
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= (int)$user->user_id ?>" data-role="<?= $user->role ?>"><?= Html::encode($user->full_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Priority</label>
                    <select name="Card[priority]" id="edit_priority">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Estimated Hours</label>
                    <input type="number" step="0.01" name="Card[estimated_hours]" id="edit_estimated_hours">
                </div>
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Due Date</label>
                    <input type="date" name="Card[due_date]" id="edit_due_date" min="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="submit-btn" style="background:#90a4ae;color:#fff;" onclick="toggleEdit(false)">Cancel</button>
                <button type="submit" class="submit-btn">Save</button>
            </div>
        </form>
        <div style="position: sticky; bottom: 0; background: white; padding: 15px 22px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px;">
            <button id="editToggleBtn" class="edit-btn" onclick="toggleEdit(true)" style="display:none;">Edit</button>
            <button id="deleteBtn" class="edit-btn delete-btn" onclick="confirmDelete()" style="display:none;">Delete</button>
            <!-- Add back button that shows when viewing comments/help requests -->
            <button id="backToDetailBtn" class="edit-btn" onclick="hideSubtaskSections()" style="display:none; background:#6c757d;">Back to Detail Card</button>
        </div>
    </div>
</div>

<!-- Create modal -->
<div id="createModal" class="modal-backdrop">
  <div class="modal-card">
    <div class="modal-head">
      <div class="modal-title">Add Card</div>
      <button class="modal-close" onclick="closeCreateModal()">×</button>
    </div>
    <div class="modal-body">
      <!-- Informasi Project di atas form (Modal) -->
      <?php
        // Kumpulkan project yang bisa dipilih di modal
        $projectsModal = \app\models\Project::find()->where(['team_lead_id' => Yii::$app->user->id])->all();
        $projectsMapModal = [];
        foreach ($projectsModal as $pm) {
          $projectsMapModal[$pm->project_id] = [
            'name' => $pm->project_name,
            'description' => $pm->description,
            'deadline' => $pm->deadline,
          ];
        }
      ?>
      <div class="project-info-panel" style="border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin-bottom:12px;background:#f8fafc;">
        <div style="font-weight:700;color:#25316d;margin-bottom:6px;">Project Information</div>
        <div id="modalProjectName" style="color:#25316d;"><strong>Project Name:</strong> -</div>
        <div id="modalProjectDesc" style="color:#415a77;margin-top:4px;"><strong>Description:</strong> -</div>
        <div id="modalProjectDeadline" style="color:#415a77;margin-top:4px;"><strong>Deadline:</strong> -</div>
      </div>

      <form method="post" action="<?= Url::to(['site/create-card']) ?>">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
        <input type="hidden" name="Card[status]" value="todo">
        <div class="form-grid">
          <div class="full">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Card Title</label>
            <input name="Card[card_title]" placeholder="Card title" required>
          </div>
          <div class="full">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Description</label>
            <textarea name="Card[description]" rows="3" placeholder="Description"></textarea>
          </div>
          <div class="full">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Project</label>
            <select name="Card[project_id]" id="projectSelectModal" required>
              <option value="">Select Project</option>
              <?php
              $userProjects = \app\models\Project::find()->where(['team_lead_id' => Yii::$app->user->id])->all();
              foreach ($userProjects as $project):
              ?>
                <option value="<?= $project->project_id ?>"><?= Html::encode($project->project_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:6px;">Assigned Role</label>
            <select name="Card[assigned_role]" id="assignedRole" required onchange="filterUsersByRole()">
              <option value="developer">Developer</option>
              <option value="designer">Designer</option>
            </select>
          </div>
          <div class="full">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Assigned User</label>
            <select name="assigned_user_id" id="assignedUser" required style="width: 100%; padding: 10px 12px; border: 1px solid #d8e1e8; border-radius: 10px;">
              <option value="">Select User</option>
              <?php foreach ($users as $user): ?>
                <option value="<?= (int)$user->user_id ?>" data-role="<?= $user->role ?>"><?= Html::encode($user->full_name) ?></option>
              <?php endforeach; ?>
            </select>
            <div style="font-size:.85rem;color:#7b8794;margin-top:6px;">Select one user as developer/designer</div>
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:6px;">Priority</label>
            <select name="Card[priority]" required>
              <option value="low">Low</option>
              <option value="medium" selected>Medium</option>
              <option value="high">High</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:6px;">Estimated Hours</label>
            <input type="number" step="0.01" name="Card[estimated_hours]" placeholder="Estimated hours">
          </div>
          <div class="full">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Due Date</label>
            <input type="date" name="Card[due_date]" required min="<?= date('Y-m-d') ?>">
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
<script>
  (function(){
    const map = <?= json_encode($projectsMapModal, JSON_UNESCAPED_UNICODE) ?>;
    function formatDateISO(d){
      try { return new Date(d).toLocaleDateString('id-ID'); } catch(e){ return d || '-'; }
    }
    function updateModalPanel(pid){
      const info = map[pid];
      if (!info) return;
      const nameEl = document.getElementById('modalProjectName');
      const descEl = document.getElementById('modalProjectDesc');
      const dlEl = document.getElementById('modalProjectDeadline');
      if (nameEl) nameEl.innerHTML = '<strong>Judul:</strong> ' + (info.name || '-');
      if (descEl) descEl.innerHTML = '<strong>Deskripsi:</strong> ' + (info.description || '-');
      if (dlEl) dlEl.innerHTML = '<strong>Deadline:</strong> ' + (info.deadline ? formatDateISO(info.deadline) : '-');
    }
    document.addEventListener('DOMContentLoaded', function(){
      const sel = document.getElementById('projectSelectModal');
      if (sel){ sel.addEventListener('change', function(){ updateModalPanel(this.value); }); }
    });
  })();
</script>

<!-- Delete confirmation modal -->
<div id="deleteModal" class="modal-backdrop">
  <div class="modal-card">
    <div class="modal-head">
      <div class="modal-title">Konfirmasi Hapus</div>
      <button class="modal-close" onclick="closeDeleteModal()">×</button>
    </div>
    <div class="modal-body">
      <p style="margin: 0 0 20px 0; font-size: 1.1rem;">Are you sure you want to delete this card?</p>
      <div style="display:flex; justify-content:flex-end; gap:10px;">
        <button type="button" class="submit-btn" style="background:#90a4ae;color:#fff;" onclick="closeDeleteModal()">Cancel</button>
        <button type="button" class="submit-btn delete-btn" onclick="deleteCard()">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Help Request Modal -->
<div id="editHelpRequestModal" class="modal-backdrop">
  <div class="modal-card">
    <div class="modal-head">
      <div class="modal-title">Edit Help Request</div>
      <button class="modal-close" onclick="closeEditHelpRequestModal()">×</button>
    </div>
    <div class="modal-body">
      <form id="editHelpRequestForm" method="post" action="<?= Url::to(['site/update-help-request']) ?>">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
        <input type="hidden" name="HelpRequest[request_id]" id="edit_hr_id">
        <div class="form-grid full">
          <label style="display:block;font-weight:600;margin-bottom:6px;">Issue Description</label>
          <textarea name="HelpRequest[issue_description]" id="edit_hr_issue" rows="3" placeholder="Describe the issue..." required></textarea>
        </div>
        <div class="form-grid full">
          <label style="display:block;font-weight:600;margin-bottom:6px;">Status</label>
          <select name="HelpRequest[status]" id="edit_hr_status" required>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="fixed">Fixed</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div class="form-grid full">
          <label style="display:block;font-weight:600;margin-bottom:6px;">Resolution Notes</label>
          <textarea name="HelpRequest[resolution_notes]" id="edit_hr_resolution" rows="2" placeholder="Add resolution notes..."></textarea>
        </div>
        <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="submit-btn" style="background:#90a4ae;color:#fff;" onclick="closeEditHelpRequestModal()">Cancel</button>
          <button type="submit" class="submit-btn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Function to filter users by role in create form
function filterUsersByRole() {
    const roleSelect = document.getElementById('assignedRole');
    const userSelect = document.getElementById('assignedUser');
    const selectedRole = roleSelect.value;

    // Reset user selection
    userSelect.innerHTML = '<option value="">Select User</option>';

    // Get all original user options
    const allUsers = <?= json_encode(array_map(function($user) {
        return [
            'user_id' => $user->user_id,
            'full_name' => $user->full_name,
            'role' => $user->role
        ];
    }, $users)) ?>;

    // Add filtered users (show all active member users)
    allUsers.forEach(user => {
        if (user.role === 'member') {
            const option = document.createElement('option');
            option.value = user.user_id;
            option.textContent = user.full_name;
            option.setAttribute('data-role', user.role);
            userSelect.appendChild(option);
        }
    });
}

// Function to filter users by role in edit form
function filterEditUsersByRole() {
    const userSelect = document.getElementById('edit_assigned_user');
    const currentUserId = userSelect.value;

    // Simply reload all member users
    userSelect.innerHTML = '<option value="">Select User</option>';

    <?php foreach ($users as $user): ?>
        <?php if ($user->role === 'member'): ?>
            userSelect.innerHTML += '<option value="<?= (int)$user->user_id ?>"><?= Html::encode($user->full_name) ?></option>';
        <?php endif; ?>
    <?php endforeach; ?>

    // Restore selection
    if (currentUserId) {
        userSelect.value = currentUserId;
    }
}

function updateHelpRequestStatus(requestId, newStatus) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?= Url::to(['site/update-help-request']) ?>';

  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_csrf';
  csrfInput.value = '<?= Yii::$app->request->csrfToken ?>';
  form.appendChild(csrfInput);

  const requestIdInput = document.createElement('input');
  requestIdInput.type = 'hidden';
  requestIdInput.name = 'HelpRequest[request_id]';
  requestIdInput.value = requestId;
  form.appendChild(requestIdInput);

  const statusInput = document.createElement('input');
  statusInput.type = 'hidden';
  statusInput.name = 'HelpRequest[status]';
  statusInput.value = newStatus;
  form.appendChild(statusInput);

  document.body.appendChild(form);
  form.submit();
}

function openCreateModal(){
    document.getElementById('createModal').style.display='flex';
    filterUsersByRole();
}

function closeCreateModal(){
    document.getElementById('createModal').style.display='none';
}

let currentSubtaskId = null;

function openDetail(id){
  const overlay = document.getElementById('detailOverlay');
  const body = document.getElementById('detailBody');
  const title = document.getElementById('detailTitle');
  const editBtn = document.getElementById('editToggleBtn');
  const deleteBtn = document.getElementById('deleteBtn');
  const form = document.getElementById('editForm');
  const backBtn = document.getElementById('backToDetailBtn');

  overlay.style.display = 'flex';
  body.innerHTML = '<div style="text-align: center; padding: 40px;">Loading card details...</div>';
  editBtn.style.display = 'none';
  deleteBtn.style.display = 'none';
  backBtn.style.display = 'none'; // Hide back button initially
  form.style.display = 'none';
  body.style.display = 'block';

  // Show loading state
  body.innerHTML = `
    <div style="text-align: center; padding: 40px;">
      <div style="font-size: 1.2rem; color: #25316d; margin-bottom: 15px;">Loading Card Details</div>
      <div style="color: #666;">Please wait...</div>
    </div>
  `;

  fetch('<?= Url::to(['site/get-card-detail']) ?>?id=' + id, {
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
    }
  })
  .then(r => {
    if (!r.ok) {
      throw new Error(`HTTP error! status: ${r.status}`);
    }
    return r.json();
  })
  .then(d => {
    if(!d.success){
      body.innerHTML = `
        <div style="text-align: center; padding: 40px; color: #dc3545;">
          <div style="font-size: 1.5rem; margin-bottom: 10px;">❌</div>
          <div style="font-size: 1.1rem; margin-bottom: 10px;">Failed to load card details</div>
          <div style="color: #666;">${d.message || 'Unknown error occurred'}</div>
          <button onclick="closeDetail()" style="margin-top: 15px; padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer;">
            Close
          </button>
        </div>
      `;
      return;
    }

    const c = d.card;
    renderCardDetail(c, body);

    // prefill edit form
    document.getElementById('edit_card_id').value = c.card_id;
    document.getElementById('edit_card_title').value = c.card_title||'';
    document.getElementById('edit_description').value = c.description||'';
    document.getElementById('edit_project_id').value = c.project_id || '';
    document.getElementById('edit_assigned_role').value = c.assigned_role||'developer';
    document.getElementById('edit_assigned_user').value = c.assigned_user_id || '';
    document.getElementById('edit_priority').value = c.priority||'medium';
    document.getElementById('edit_estimated_hours').value = c.estimated_hours||0;
    document.getElementById('edit_due_date').value = (c.due_date||'').substring(0,10);

    // Initialize user filter for edit form
    filterEditUsersByRole();

    editBtn.style.display = 'inline-block';
    deleteBtn.style.display = 'inline-block';

    // Store card ID for delete function
    deleteBtn.setAttribute('data-card-id', c.card_id);

    // Hide edit/delete if card is completed
    if ((c.status || '').toLowerCase() === 'done') {
      editBtn.style.display = 'none';
      deleteBtn.style.display = 'none';
    }
  })
  .catch(err => {
    console.error('Error loading card detail:', err);
    body.innerHTML = `
      <div style="text-align: center; padding: 40px; color: #dc3545;">
        <div style="font-size: 1.5rem; margin-bottom: 10px;">❌</div>
        <div style="font-size: 1.1rem; margin-bottom: 10px;">Network Error</div>
        <div style="color: #666; margin-bottom: 15px;">Failed to load card details. Please check your connection and try again.</div>
        <div style="font-size: 0.9rem; color: #999; margin-bottom: 20px;">Error: ${err.message}</div>
        <button onclick="openDetail(${id})" style="margin: 5px; padding: 8px 16px; background: #25316d; color: white; border: none; border-radius: 6px; cursor: pointer;">
          Retry
        </button>
        <button onclick="closeDetail()" style="margin: 5px; padding: 8px 16px; background: #6c757d; border: none; border-radius: 6px; cursor: pointer;">
          Close
        </button>
      </div>
    `;
  });
}

function renderCardDetail(c, bodyElement) {
  const percent = parseInt(c.progress_percentage || 0);
  const accent = percent >= 70 ? '#39b16a' : (percent >= 30 ? '#57a5ff' : '#f06292');

  // Format dates
  const createdDate = c.created_at ? new Date(c.created_at).toLocaleDateString(undefined, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }) : '-';

  const dueDate = c.due_date ? new Date(c.due_date).toLocaleDateString(undefined, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }) : '-';

  // Render subtasks grouped by status (comments and help requests only show on button click)
  let subtasksHTML = renderSubtasks(c.subtasks);

  // Render project members list
  let membersHTML = '';
  if (c.project_members && c.project_members.length > 0) {
    membersHTML = `
      <div class="subtasks-section">
        <div class="subtasks-title">Project Members (${c.project_members.length})</div>
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
              ${c.project_members.map(m => `
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
      <div class="subtasks-section">
        <div class="subtasks-title">Project Members</div>
        <div class="no-subtasks">No members yet</div>
      </div>
    `;
  }

  // Control edit/delete visibility based on status
  const isDone = (c.status || '').toLowerCase() === 'done';

  bodyElement.innerHTML = `
    <div class="detail-row">
      <div class="detail-label">Description:</div>
      <div class="detail-value">${escapeHtml(c.description)||'-'}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Project:</div>
      <div class="detail-value">${escapeHtml(c.project_name)||'-'}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Board:</div>
      <div class="detail-value">${escapeHtml(c.board_name)||'-'}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Created by:</div>
      <div class="detail-value">${escapeHtml(c.created_by)||'-'}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Assigned User:</div>
      <div class="detail-value">${escapeHtml(c.assigned_user)||'-'}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Assigned Role:</div>
      <div class="detail-value">${escapeHtml(c.assigned_role)||'-'}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Status:</div>
      <div class="detail-value">${escapeHtml(c.status)||'-'}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Priority:</div>
      <div class="detail-value">${escapeHtml(c.priority)||'-'}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Estimated Hours:</div>
      <div class="detail-value">${c.estimated_hours||'0'} hours</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Actual Hours:</div>
      <div class="detail-value">${c.actual_hours||'0'} hours</div>
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
      <div class="detail-value">${createdDate}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Due Date:</div>
      <div class="detail-value">${dueDate}</div>
    </div>
    ${membersHTML}
    ${subtasksHTML}
  `;

  const editBtn = document.getElementById('editToggleBtn');
  const deleteBtn = document.getElementById('deleteBtn');
  if (isDone) {
    if (editBtn) editBtn.style.display = 'none';
    if (deleteBtn) deleteBtn.style.display = 'none';
  }
}

// Helper functions untuk rendering
function renderSubtasks(subtasks) {
  if (!subtasks || subtasks.length === 0) {
    return `
      <div class="subtasks-section">
        <div class="subtasks-title">Subtasks by Assigned Member</div>
        <div class="no-subtasks">No subtasks created by assigned member yet</div>
      </div>
    `;
  }

  const statusConfig = {
    'done': { color: '#39b16a', label: 'Done' },
    'in_progress': { color: '#57a5ff', label: 'In Progress' },
    'review': { color: '#ffa557', label: 'Review' },
    'todo': { color: '#90a4ae', label: 'Todo' }
  };

  return `
    <div class="subtasks-section">
      <div class="subtasks-title">Subtasks by Assigned Member (${subtasks.length})</div>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        ${subtasks.map(subtask => {
          const status = subtask.status || 'todo';
          const config = statusConfig[status] || { color: '#90a4ae', label: status };

          return `
            <div class="subtask-item" style="border-left: 4px solid ${config.color}; cursor: default;">
              <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                <div style="flex: 1;">
                  <div class="subtask-item-title">${escapeHtml(subtask.subtask_title) || 'Untitled'}</div>
                  <div class="subtask-item-meta">
                    <span>By: ${escapeHtml(subtask.created_by_name) || 'Unknown'}</span>
                    <span style="background: ${config.color}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem;">
                      ${config.label}
                    </span>
                  </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                  <button onclick="event.stopPropagation(); showSubtaskComments(${subtask.subtask_id})"
                          style="background: #e3f2fd; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;"
                          onmouseover="this.style.background='#bbdefb'"
                          onmouseout="this.style.background='#e3f2fd'"
                          title="View Comments">
                    <span style="font-size: 1.2rem;">💬</span>
                  </button>
                  <button onclick="event.stopPropagation(); showSubtaskHelpRequests(${subtask.subtask_id})"
                          style="background: #fff3e0; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;"
                          onmouseover="this.style.background='#ffe0b2'"
                          onmouseout="this.style.background='#fff3e0'"
                          title="View Help Requests">
                    <span style="font-size: 1.2rem;">🆘</span>
                  </button>
                </div>
              </div>
              <div class="subtask-item-details">
                <span>Est: ${subtask.estimated_hours || 0}h</span>
                <span>Actual: ${subtask.actual_hours || 0}h</span>
                <span>Created: ${subtask.created_at ? new Date(subtask.created_at).toLocaleDateString() : '-'}</span>
              </div>
              ${subtask.description ? `
                <div style="font-size: 0.8rem; color: #666; margin-top: 8px; border-top: 1px solid #f0f0f0; padding-top: 8px;">
                  ${escapeHtml(subtask.description)}
                </div>
              ` : ''}
            </div>
          `;
        }).join('')}
      </div>
    </div>
  `;
}

// Fungsi untuk menampilkan komentar subtask dalam overlay yang sama
function showSubtaskComments(subtaskId) {
  console.log('Fetching comments for subtask:', subtaskId);

  // Set current subtask ID
  currentSubtaskId = subtaskId;

  fetch('<?= Url::to(['site/get-subtask-comments']) ?>?id=' + subtaskId, {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Failed to fetch comments');
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      renderSubtaskCommentsInOverlay(data.comments, subtaskId);
    } else {
      throw new Error(data.message || 'Failed to load comments');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Gagal memuat komentar. Silakan coba lagi.', 'error');
  });
}

// Fungsi untuk menampilkan help request subtask dalam overlay yang sama
function showSubtaskHelpRequests(subtaskId) {
  console.log('Fetching help requests for subtask:', subtaskId);

  // Set current subtask ID
  currentSubtaskId = subtaskId;

  // Load help requests
  fetch('<?= Url::to(['site/get-subtask-help-requests']) ?>?id=' + subtaskId, {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      renderSubtaskHelpRequestsInOverlay(data.help_requests, subtaskId);
    } else {
      throw new Error(data.message || 'Failed to load help requests');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Gagal memuat data. Silakan coba lagi.', 'error');
  });
}

// Fungsi untuk render komentar subtask dalam overlay utama
function renderSubtaskCommentsInOverlay(comments, subtaskId) {
  const body = document.getElementById('detailBody');
  const editBtn = document.getElementById('editToggleBtn');
  const deleteBtn = document.getElementById('deleteBtn');
  const backBtn = document.getElementById('backToDetailBtn');

  let commentsHTML = '';
  if (comments && comments.length > 0) {
    commentsHTML = comments.map(comment => `
      <div class="subtask-comment-item">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
          <strong style="color: #2c3e50;">${escapeHtml(comment.user_name || 'Unknown')}</strong>
          <span style="font-size: 0.8rem; color: #666;">${comment.created_at || '-'}</span>
        </div>
        <div style="color: #495057; line-height: 1.4;">${escapeHtml(comment.comment_text) || 'No content'}</div>
      </div>
    `).join('');
  } else {
    commentsHTML = '<div class="no-data-message">No comments yet for this subtask</div>';
  }

  const commentsSection = `
    <div class="subtask-comments-section">
      <div class="subtask-section-title">
        <span>💬</span>
        Subtask Comments
      </div>
      <div class="subtask-comments-list">
        ${commentsHTML}
      </div>
      
      <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid #e9ecef;">
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
          <h4 style="margin: 0 0 10px 0; color: #495057;">Add Comment</h4>
          <textarea id="subtaskComment" placeholder="Enter your comment..." 
                    style="width: 100%; min-height: 80px; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; resize: vertical; font-family: inherit;"></textarea>
          <button onclick="submitSubtaskComment()" 
                  style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500;">
            Submit Comment
          </button>
        </div>
      </div>
    </div>
  `;

  body.innerHTML = commentsSection;
  editBtn.style.display = 'none';
  deleteBtn.style.display = 'none';
  backBtn.style.display = 'inline-block';
}

function renderSubtaskHelpRequestsInOverlay(helpRequests, subtaskId) {
  const body = document.getElementById('detailBody');
  const editBtn = document.getElementById('editToggleBtn');
  const deleteBtn = document.getElementById('deleteBtn');
  const backBtn = document.getElementById('backToDetailBtn');

  let helpRequestsHTML = '';
  if (helpRequests && helpRequests.length > 0) {
    helpRequestsHTML = helpRequests.map(request => {
      const statusColor = request.status === 'completed' ? '#4caf50' :
                         request.status === 'fixed' ? '#8bc34a' :
                         request.status === 'in_progress' ? '#2196f3' : '#ff9800';

      let statusButtonsHTML = '';
      const currentStatus = request.status || 'pending';

      if (currentStatus === 'pending') {
        // Show all three buttons
        statusButtonsHTML = `
          <button onclick="updateHelpRequestStatus(${request.request_id}, 'in_progress')"
                  style="background: #2196f3; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease;"
                  onmouseover="this.style.background='#1976d2'"
                  onmouseout="this.style.background='#2196f3'"
                  title="Ubah status ke In Progress">
            In Progress
          </button>
          <button onclick="updateHelpRequestStatus(${request.request_id}, 'fixed')"
                  style="background: #8bc34a; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease;"
                  onmouseover="this.style.background='#7cb342'"
                  onmouseout="this.style.background='#8bc34a'"
                  title="Ubah status ke Fixed">
            Fixed
          </button>
          <button onclick="updateHelpRequestStatus(${request.request_id}, 'completed')"
                  style="background: #4caf50; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease;"
                  onmouseover="this.style.background='#45a049'"
                  onmouseout="this.style.background='#4caf50'"
                  title="Ubah status ke Completed">
            Completed
          </button>
        `;
      } else if (currentStatus === 'in_progress') {
        // Show only Fixed and Completed
        statusButtonsHTML = `
          <button onclick="updateHelpRequestStatus(${request.request_id}, 'fixed')"
                  style="background: #8bc34a; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease;"
                  onmouseover="this.style.background='#7cb342'"
                  onmouseout="this.style.background='#8bc34a'"
                  title="Ubah status ke Fixed">
            Fixed
          </button>
          <button onclick="updateHelpRequestStatus(${request.request_id}, 'completed')"
                  style="background: #4caf50; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease;"
                  onmouseover="this.style.background='#45a049'"
                  onmouseout="this.style.background='#4caf50'"
                  title="Ubah status ke Completed">
            Completed
          </button>
        `;
      } else if (currentStatus === 'fixed') {
        // Show only Completed
        statusButtonsHTML = `
          <button onclick="updateHelpRequestStatus(${request.request_id}, 'completed')"
                  style="background: #4caf50; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease;"
                  onmouseover="this.style.background='#45a049'"
                  onmouseout="this.style.background='#4caf50'"
                  title="Ubah status ke Completed">
            Completed
          </button>
        `;
      }
      // If status is 'completed', no buttons shown (statusButtonsHTML remains empty)

      return `
        <div class="subtask-help-request-item ${request.status || 'pending'}">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <strong style="color: #2c3e50;">${escapeHtml(request.creator_name || 'Unknown')}</strong>
            <span style="background: ${statusColor}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600;">
              ${(request.status || 'pending').replace('_', ' ').toUpperCase()}
            </span>
          </div>
          <div style="color: #495057; line-height: 1.4; margin-bottom: 8px;">
            <strong>Issue:</strong> ${escapeHtml(request.issue_description) || 'No description'}
          </div>
          ${request.resolution_notes ? `
            <div style="color: #495057; line-height: 1.4; margin-bottom: 8px;">
              <strong>Resolution:</strong> ${escapeHtml(request.resolution_notes)}
            </div>
          ` : ''}
          <div style="font-size: 0.8rem; color: #666; margin-bottom: 12px;">
            <div>Dibuat: ${request.created_at || '-'}</div>
            ${request.resolved_by_name ? `<div>Diselesaikan oleh: ${escapeHtml(request.resolved_by_name)}</div>` : ''}
          </div>
          ${statusButtonsHTML ? `<div style="display: flex; gap: 8px;">${statusButtonsHTML}</div>` : ''}
        </div>
      `;
    }).join('');
  } else {
    helpRequestsHTML = '<div class="no-data-message">No help requests for this subtask</div>';
  }

  

  const helpRequestsSection = `
    <div class="subtask-help-requests-section">
      <div class="subtask-section-title">
        <span>🆘</span>
        Subtask Help Requests
      </div>
      <div class="subtask-help-requests-list">
        ${helpRequestsHTML}
      </div>
    </div>
  `;

  body.innerHTML = helpRequestsSection;
  editBtn.style.display = 'none';
  deleteBtn.style.display = 'none';
  backBtn.style.display = 'inline-block';
}

function openEditHelpRequestModal(requestId, requestData) {
  console.log('Opening edit modal for request:', requestId, requestData);

  // Parse request data if it's a string
  let request = typeof requestData === 'string' ? JSON.parse(requestData) : requestData;

  // Set form values
  document.getElementById('edit_hr_id').value = requestId;
  document.getElementById('edit_hr_issue').value = request.issue_description || '';
  document.getElementById('edit_hr_status').value = request.status || 'pending';
  document.getElementById('edit_hr_resolution').value = request.resolution_notes || '';

  // Show modal
  document.getElementById('editHelpRequestModal').style.display = 'flex';
}

function closeEditHelpRequestModal() {
  document.getElementById('editHelpRequestModal').style.display = 'none';
}

function hideSubtaskSections() {
  const cardId = document.getElementById('edit_card_id').value;
  const backBtn = document.getElementById('backToDetailBtn');
  backBtn.style.display = 'none';
  
  if (cardId) {
    openDetail(cardId);
  }
}

function updateHelpRequestStatus(requestId, newStatus) {
    console.log('Updating help request:', requestId, 'to status:', newStatus);

    // Find and disable the buttons during update
    const allButtons = document.querySelectorAll('button[onclick*="updateHelpRequestStatus"]');
    allButtons.forEach(button => button.disabled = true);

    // Create form data
    const formData = new FormData();
    formData.append('_csrf', '<?= Yii::$app->request->csrfToken ?>');
    formData.append('HelpRequest[request_id]', requestId);
    formData.append('HelpRequest[status]', newStatus);

    // Send AJAX request
    fetch('<?= Url::to(['site/update-help-request']) ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showNotification(data.message || 'Status updated successfully', 'success');
            // Reload the help requests view after a short delay
            setTimeout(() => {
                if (currentSubtaskId) {
                    showSubtaskHelpRequests(currentSubtaskId);
                } else {
                    // If no currentSubtaskId, it might be a standalone help request, reload page
                    window.location.reload();
                }
            }, 1000);
        } else {
            throw new Error(data.message || 'Update failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Failed to update status. Please try again.', 'error');
        // Re-enable buttons on error
        allButtons.forEach(button => button.disabled = false);
    });
}

// Helper function untuk show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.custom-notification');
    existingNotifications.forEach(notification => notification.remove());

    const notification = document.createElement('div');
    notification.className = 'custom-notification';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        transition: all 0.3s ease;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

    if (type === 'info') {
        notification.style.background = '#2196f3';
    } else if (type === 'success') {
        notification.style.background = '#4caf50';
    } else if (type === 'error') {
        notification.style.background = '#f44336';
    }

    notification.textContent = message;
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100px)';
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
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

// Fungsi untuk mengirim komentar pada help request
function submitSubtaskComment() {
  const commentText = document.getElementById('subtaskComment').value.trim();
  
  if (!commentText) {
    showNotification('Please enter a comment', 'error');
    return;
  }

  if (!currentSubtaskId) {
    showNotification('Subtask ID not found', 'error');
    return;
  }

  // Disable button and show loading
  const button = document.querySelector('button[onclick="submitSubtaskComment()"]');
  const originalText = button.textContent;
  button.disabled = true;
  button.textContent = 'Submitting...';

  // Create form data
  const formData = new FormData();
  formData.append('_csrf', '<?= Yii::$app->request->csrfToken ?>');
  formData.append('Comment[subtask_id]', currentSubtaskId);
  formData.append('Comment[comment_text]', commentText);
  formData.append('Comment[comment_type]', 'subtask');

  // Send AJAX request
  fetch('<?= Url::to(['site/add-comment']) ?>', {
    method: 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json',
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const ct = response.headers.get('content-type') || '';
    if (ct.includes('application/json')) {
      return response.json();
    }
    throw new Error('Response is not JSON. Session may have ended or access denied.');
  })
  .then(data => {
    if (data.success) {
      showNotification(data.message || 'Comment added successfully', 'success');
      // Clear textarea
      document.getElementById('subtaskComment').value = '';
      // Reload the comments view to show the new comment
      setTimeout(() => {
        showSubtaskComments(currentSubtaskId);
      }, 1000);
    } else {
      throw new Error(data.message || 'Failed to add comment');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification(error.message || 'Failed to add comment. Please try again.', 'error');
  })
  .finally(() => {
    // Re-enable button
    button.disabled = false;
    button.textContent = originalText;
  });
}

function closeDetail(){ document.getElementById('detailOverlay').style.display='none'; }

function toggleEdit(show){
  const f = document.getElementById('editForm');
  const body = document.getElementById('detailBody');
  const editBtn = document.getElementById('editToggleBtn');
  const deleteBtn = document.getElementById('deleteBtn');
  const backBtn = document.getElementById('backToDetailBtn');

  if (show) {
    f.style.display = 'block';
    body.style.display = 'none';
    editBtn.style.display = 'none';
    deleteBtn.style.display = 'none';
    backBtn.style.display = 'none';
  } else {
    f.style.display = 'none';
    body.style.display = 'block';
    editBtn.style.display = 'inline-block';
    deleteBtn.style.display = 'inline-block';
    backBtn.style.display = 'none';
  }
}

// Delete functions
function confirmDelete(){
  document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal(){
  document.getElementById('deleteModal').style.display = 'none';
}

function deleteCard(){
  const deleteBtn = document.getElementById('deleteBtn');
  const cardId = deleteBtn.getAttribute('data-card-id');

  if (!cardId) {
    alert('Card ID not found');
    return;
  }

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?= Url::to(['site/delete-card']) ?>?id=' + cardId;

  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_csrf';
  csrfInput.value = '<?= Yii::$app->request->csrfToken ?>';
  form.appendChild(csrfInput);

  document.body.appendChild(form);
  form.submit();
}
</script>
