<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\User;

$this->title = 'Kelola Users';
?>

<style>
:root {
    --primary: rgb(37,49,109);
    --secondary: rgb(95,111,148);
    --accent: rgb(151,210,236);
    --bg: rgba(255, 255, 255, 1);
    --white: #fff;
    --success: #28a745;
    --warning: #ffc107;
    --danger: #dc3545;
    --info: #17a2b8;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--bg);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: var(--primary);
    line-height: 1.6;
    overflow-x: hidden;
}

.users-page {
    padding: 20px 30px;
    min-height: 100vh;
    position: relative;
}

.page-header {
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
}

.btn-add {
    background: var(--primary);
    color: var(--white);
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-add:hover {
    background: var(--secondary);
    transform: translateY(-2px);
}

/* Table Styles */
.users-table {
    background: var(--white);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(151,210,236,0.15);
    margin-bottom: 20px;
}

.table-header {
    background: var(--primary);
    color: var(--white);
    padding: 15px 20px;
    font-weight: 600;
}

.table-container {
    overflow-x: auto;
}

.users-table table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th,
.users-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid rgba(151,210,236,0.3);
}

.users-table th {
    background: rgba(151,210,236,0.1);
    font-weight: 600;
    color: var(--primary);
}

.users-table tbody tr:hover {
    background: rgba(151,210,236,0.05);
}

/* Profile Picture Styles */
.profile-picture {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--accent);
}

.profile-picture-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-weight: bold;
    font-size: 14px;
}

.role-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.role-admin {
    background: var(--danger);
    color: var(--white);
}

.role-member {
    background: var(--info);
    color: var(--white);
}

.status-active {
    color: var(--success);
    font-weight: 600;
}

.status-inactive {
    color: var(--danger);
    font-weight: 600;
}

.task-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.task-working {
    background: var(--success);
    color: var(--white);
}

.task-idle {
    background: var(--secondary);
    color: var(--white);
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-edit,
.btn-delete {
    padding: 6px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn-edit {
    background: var(--accent);
    color: var(--primary);
}

.btn-edit:hover {
    background: rgba(151,210,236,0.8);
}

.btn-delete {
    background: var(--danger);
    color: var(--white);
}

.btn-delete:hover {
    background: rgba(220,53,69,0.8);
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
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: var(--white);
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
    background: var(--primary);
    color: var(--white);
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
    font-weight: 600;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: var(--primary);
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 2px solid rgba(151,210,236,0.3);
    border-radius: 6px;
    font-size: 0.9rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--accent);
}

/* File Upload Styles */
.file-upload {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}

.file-upload-input {
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-label {
    display: block;
    padding: 8px 12px;
    border: 2px dashed rgba(151,210,236,0.5);
    border-radius: 6px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload-label:hover {
    border-color: var(--accent);
    background: rgba(151,210,236,0.1);
}

.file-upload-preview {
    margin-top: 10px;
    text-align: center;
}

.file-upload-preview img {
    max-width: 100px;
    max-height: 100px;
    border-radius: 8px;
    border: 2px solid var(--accent);
}

.current-profile-picture {
    text-align: center;
    margin-bottom: 15px;
}

.current-profile-picture img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--accent);
}

.modal-footer {
    padding: 15px 20px;
    background: rgba(151,210,236,0.1);
    border-radius: 0 0 12px 12px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-secondary {
    background: var(--secondary);
    color: var(--white);
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: rgba(95,111,148,0.8);
}

.btn-primary {
    background: var(--primary);
    color: var(--white);
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: var(--secondary);
}

/* Flash Messages - Improved */
.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    animation: slideIn 0.3s ease-out;
}

.alert-success {
    background: rgba(40,167,69,0.1);
    color: var(--success);
    border: 1px solid rgba(40,167,69,0.3);
}

.alert-error {
    background: rgba(220,53,69,0.1);
    color: var(--danger);
    border: 1px solid rgba(220,53,69,0.3);
}

.alert-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: inherit;
    padding: 0;
    margin-left: 10px;
}

@keyframes slideIn {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
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

/* Responsive */
@media (max-width: 768px) {
    .users-page {
        padding: 15px 20px;
    }

    .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }

    .users-table th,
    .users-table td {
        padding: 8px 10px;
        font-size: 0.8rem;
    }

    .action-buttons {
        flex-direction: column;
        gap: 3px;
    }

    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}
</style>

<div class="users-page">
    <div class="page-header">
        <div class="page-title">👥 Manage Users</div>
        <a href="#" class="btn-add" onclick="openCreateModal()">+ Add New User</a>
    </div>

    <div class="users-table">
        <div class="table-header">User List</div>
        <div class="table-container">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <div class="icon">👥</div>
                    <div class="text">No users have been registered yet</div>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Profile Picture</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Current Task</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if ($user->profile_picture): ?>
                                        <img src="<?= Html::encode($user->profile_picture) ?>" 
                                             alt="Profile" 
                                             class="profile-picture"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="profile-picture-placeholder" style="display: none;">
                                            <?= strtoupper(substr($user->username, 0, 1)) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="profile-picture-placeholder">
                                            <?= strtoupper(substr($user->username, 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode($user->username) ?></td>
                                <td><?= Html::encode($user->full_name) ?></td>
                                <td><?= Html::encode($user->email) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $user->role ?>">
                                        <?= Html::encode(ucfirst($user->role)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="task-status task-<?= Html::encode($user->current_task_status ? $user->current_task_status : 'idle') ?>">
                                        <?= Html::encode(ucfirst($user->current_task_status ? $user->current_task_status : 'idle')) ?>
                                    </span>
                                </td>
                                <td><?= Html::encode(date('d/m/Y', strtotime($user->created_at))) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="#" class="btn-edit" onclick="openEditModal(<?= $user->user_id ?>)">Edit</a>
                                        <a href="#" class="btn-delete" onclick="confirmDelete(<?= $user->user_id ?>, '<?= Html::encode($user->username) ?>')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Add New User</div>
        <form method="post" action="<?= Url::to(['site/create-user']) ?>" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>" />

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="User[username]" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="User[full_name]" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="User[email]" required>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="User[password]" required>
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="User[role]" required>
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select id="is_active" name="User[is_active]" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Profile Picture</label>
                    <div class="file-upload">
                        <input type="file" id="profile_picture" name="profile_picture" 
                               class="file-upload-input" accept="image/*">
                        <label for="profile_picture" class="file-upload-label">
                            📷 Click to upload profile picture
                        </label>
                    </div>
                    <div class="file-upload-preview" id="createPreview"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Edit User</div>
        <form method="post" action="<?= Url::to(['site/update-user']) ?>" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>" />
                <input type="hidden" id="edit_user_id" name="User[user_id]" />

                <div class="current-profile-picture" id="currentProfilePicture">
                    <!-- Current profile picture will be loaded here -->
                </div>

                <div class="form-group">
                    <label for="edit_username">Username *</label>
                    <input type="text" id="edit_username" name="User[username]" required>
                </div>

                <div class="form-group">
                    <label for="edit_full_name">Full Name *</label>
                    <input type="text" id="edit_full_name" name="User[full_name]" required>
                </div>

                <div class="form-group">
                    <label for="edit_email">Email *</label>
                    <input type="email" id="edit_email" name="User[email]" required>
                </div>

                <div class="form-group">
                    <label for="edit_password">Password (leave blank if not changed)</label>
                    <input type="password" id="edit_password" name="User[password]">
                </div>

                <div class="form-group">
                    <label for="edit_role">Role *</label>
                    <select id="edit_role" name="User[role]" required>
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_is_active">Status</label>
                    <select id="edit_is_active" name="User[is_active]" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>New Profile Picture</label>
                    <div class="file-upload">
                        <input type="file" id="edit_profile_picture" name="profile_picture" 
                               class="file-upload-input" accept="image/*">
                        <label for="edit_profile_picture" class="file-upload-label">
                            📷 Click to upload new profile picture
                        </label>
                    </div>
                    <div class="file-upload-preview" id="editPreview"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete User Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Confirm Delete User</div>
        <div class="modal-body">
            <p>Are you sure you want to delete user "<span id="delete_username"></span>"?</p>
            <p style="color: var(--danger); font-weight: 600;">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn-primary" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<script>
// Auto-hide flash messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.alert');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
});

// File upload preview functionality
function setupFileUpload(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    });
}

function openCreateModal() {
    document.getElementById('createModal').style.display = 'block';
    // Reset form and preview
    document.getElementById('createPreview').innerHTML = '';
    setupFileUpload('profile_picture', 'createPreview');
}

function openEditModal(userId) {
    // Fetch user data via AJAX
    fetch(`<?= Url::to(['site/get-user-detail']) ?>?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('edit_user_id').value = user.user_id;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_full_name').value = user.full_name;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_role').value = user.role;
                document.getElementById('edit_is_active').value = user.is_active ? '1' : '0';
                document.getElementById('edit_password').value = '';
                
                // Set current profile picture
                const profilePictureContainer = document.getElementById('currentProfilePicture');
                if (user.profile_picture) {
                    profilePictureContainer.innerHTML = `
                        <img src="${user.profile_picture}" alt="Current Profile" 
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiByeD0iNDAiIGZpbGw9IiM5N0QyRUMiLz4KPHRleHQgeD0iNDAiIHk9IjQ1IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSJ3aGl0ZSIgZm9udC1zaXplPSIyNCIgZm9udC13ZWlnaHQ9ImJvbGQiPgo8L3RleHQ+Cjwvc3ZnPgo='; this.alt='Profile Placeholder'">
                        <p style="margin-top: 8px; font-size: 0.8rem; color: var(--secondary);">Current Profile Picture</p>
                    `;
                } else {
                    const initial = user.username ? user.username.charAt(0).toUpperCase() : 'U';
                    profilePictureContainer.innerHTML = `
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--accent); 
                             display: flex; align-items: center; justify-content: center; color: white; 
                             font-weight: bold; font-size: 24px; margin: 0 auto;">
                            ${initial}
                        </div>
                        <p style="margin-top: 8px; font-size: 0.8rem; color: var(--secondary);">No profile picture</p>
                    `;
                }
                
                document.getElementById('editModal').style.display = 'block';
                setupFileUpload('edit_profile_picture', 'editPreview');
            } else {
                alert('Failed to load user data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading user data');
        });
}

function closeModal() {
    document.getElementById('createModal').style.display = 'none';
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete(userId, username) {
    document.getElementById('delete_username').textContent = username;
    document.getElementById('deleteModal').style.display = 'block';

    // Set up the delete button to submit the form
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.onclick = function() {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = `<?= Url::to(['site/delete-user']) ?>?id=${userId}`;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_csrf';
        csrfInput.value = '<?= Yii::$app->request->csrfToken ?>';
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    };
}

// Close modal when clicking outside
window.onclick = function(event) {
    const createModal = document.getElementById('createModal');
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');
    if (event.target == createModal) {
        createModal.style.display = 'none';
    }
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
    if (event.target == deleteModal) {
        deleteModal.style.display = 'none';
    }
}

// Initialize file upload previews when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupFileUpload('profile_picture', 'createPreview');
    setupFileUpload('edit_profile_picture', 'editPreview');
});
</script>