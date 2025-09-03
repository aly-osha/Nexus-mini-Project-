<?php
echo "settings";
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Settings</h2>

  <div id="settingsAccordion">

    <!-- Profile Information -->
    <div class="card">
      <div class="card-header" id="headingProfile">
        <h5 class="mb-0">
          <button class="btn btn-link" data-toggle="collapse" data-target="#collapseProfile" aria-expanded="true" aria-controls="collapseProfile">
            Profile Information
          </button>
        </h5>
      </div>
      <div id="collapseProfile" class="collapse" aria-labelledby="headingProfile" data-parent="#settingsAccordion">
        <div class="card-body">
          <form method="post" action="update_profile.php">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="fullname" class="form-control" value="Kenneth Valdez">
            </div>
            <div class="form-group">
              <label>Bio</label>
              <textarea class="form-control" name="bio">A front-end developer...</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Account Settings -->
    <div class="card">
      <div class="card-header" id="headingAccount">
        <h5 class="mb-0">
          <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseAccount" aria-expanded="false" aria-controls="collapseAccount">
            Account Settings
          </button>
        </h5>
      </div>
      <div id="collapseAccount" class="collapse" aria-labelledby="headingAccount" data-parent="#settingsAccordion">
        <div class="card-body">
          <form method="post" action="update_account.php">
            <div class="form-group">
              <label>Username</label>
              <input type="text" name="username" class="form-control" value="kennethvaldez">
            </div>
            <button type="submit" class="btn btn-danger">Delete Account</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Security -->
    <div class="card">
      <div class="card-header" id="headingSecurity">
        <h5 class="mb-0">
          <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseSecurity" aria-expanded="false" aria-controls="collapseSecurity">
            Security
          </button>
        </h5>
      </div>
      <div id="collapseSecurity" class="collapse" aria-labelledby="headingSecurity" data-parent="#settingsAccordion">
        <div class="card-body">
          <form method="post" action="update_security.php">
            <div class="form-group">
              <label>Change Password</label>
              <input type="password" name="old_password" class="form-control" placeholder="Old Password">
              <input type="password" name="new_password" class="form-control mt-2" placeholder="New Password">
            </div>
            <button type="submit" class="btn btn-warning">Update Password</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Notification -->
    <div class="card">
      <div class="card-header" id="headingNotification">
        <h5 class="mb-0">
          <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseNotification" aria-expanded="false" aria-controls="collapseNotification">
            Notification
          </button>
        </h5>
      </div>
      <div id="collapseNotification" class="collapse" aria-labelledby="headingNotification" data-parent="#settingsAccordion">
        <div class="card-body">
          <form method="post" action="update_notifications.php">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="notif1" checked>
              <label for="notif1" class="form-check-label">Email Alerts</label>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Billing -->
    <div class="card">
      <div class="card-header" id="headingBilling">
        <h5 class="mb-0">
          <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseBilling" aria-expanded="false" aria-controls="collapseBilling">
            Billing
          </button>
        </h5>
      </div>
      <div id="collapseBilling" class="collapse" aria-labelledby="headingBilling" data-parent="#settingsAccordion">
        <div class="card-body">
          <p>No payment method added.</p>
          <button class="btn btn-info">Add Payment Method</button>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
