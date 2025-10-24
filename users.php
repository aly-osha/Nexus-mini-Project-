<!-- users.php -->
<div class="users-container">
  <!-- Tabs -->
  <div class="usertabs">
    <button class="tab-btn active" onclick="loadUserPage('student_list.php', this)">Students</button>
    <button class="tab-btn" onclick="loadUserPage('teacher_list.php', this)">Teachers</button>
    <button class="tab-btn" onclick="loadUserPage('admin_user_manage.php', this)">Pending Verification</button>
  </div>

  <!-- Content Area -->
  <div id="user-content" style="margin-top:15px;">
    <?php include "student_list.php"; ?> <!-- default load -->
  </div>
</div>

<style>
  .usertabs {
    display: flex;
    background: #1e293b;
  }
  .tab-btn {
    flex: 1;
    padding: 10px;
    cursor: pointer;
    background: #1e293b;
    color: white;
    font-weight: bold;
    border: none;
    transition: background 0.3s;
  }
  .tab-btn:hover {
    background: #575757;
  }
  .tab-btn.active {
    background: #334155;
  }
</style>

