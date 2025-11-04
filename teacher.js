// === Profile Drawer Toggle ===
// Guard against missing elements when script loads before DOM fragments are injected.
const pic = document.getElementById('profilePic');
const drawer = document.getElementById('drawer');
if (pic && drawer) {
  pic.addEventListener('click', () => {
    drawer.classList.toggle('open');
  });

  document.addEventListener('click', function (event) {
    if (!pic.contains(event.target) && !drawer.contains(event.target)) {
      drawer.classList.remove('open');
    }
  });
}

// === Link Click Handler ===
document.querySelectorAll('.nav-links a, .nav-links button').forEach(el => {
  el.addEventListener('click', (e) => {
    // Prevent link navigation, but don't mess with buttons
    if (el.tagName.toLowerCase() === 'a') {
      e.preventDefault();
    }

    const page = el.getAttribute('data-page');
    loadContent(page, el, true);
  });
});

// === Load Content Dynamically ===
function loadContent(page, element, push = true) {
  fetch(page)
    .then(response => response.text())
    .then(data => {
      document.getElementById('main-content').innerHTML = data;

      if (push) {
        window.history.pushState({ page: page }, '', `?page=${page}`);
      }

      document.querySelectorAll('.nav-links a, .nav-links button').forEach(el => {
        el.classList.remove('active');
      });

      if (element) {
        element.classList.add('active');
      } else {
        const autoEl = Array.from(document.querySelectorAll('.nav-links a, .nav-links button'))
          .find(el => el.getAttribute('data-page') === page);
        if (autoEl) autoEl.classList.add('active');
      }

    })
    .catch(error => {
      document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
      console.error(error);
    });
}

// === Handle Browser Back/Forward ===
window.addEventListener('popstate', event => {
  const page = (event.state && event.state.page) || 'teacher_home.php';
  loadContent(page, null, false);
});

// === Initial Load Based on URL ===
window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const page = params.get('page') || 'teacher_home.php';
  loadContent(page, null, false);
});

// === Utility Functions ===
function confirmDelete(message) {
  return confirm(message || 'Are you sure you want to delete this item?');
}

function showAlert(message, type = 'info') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type}`;
  alertDiv.textContent = message;
  alertDiv.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 6px;
    color: white;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
  `;
  
  const colors = {
    success: '#27ae60',
    error: '#e74c3c',
    warning: '#f39c12',
    info: '#3498db'
  };
  
  alertDiv.style.backgroundColor = colors[type] || colors.info;
  
  document.body.appendChild(alertDiv);
  
  setTimeout(() => {
    alertDiv.remove();
  }, 3000);
}

// === Form Submission Handler ===
function submitForm(form, successCallback) {
  const formData = new FormData(form);
  
  fetch(form.action, {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    if (data.includes('success') || data.includes('updated') || data.includes('created')) {
      showAlert('Operation completed successfully!', 'success');
      if (successCallback) successCallback();
    } else if (data.includes('error')) {
      showAlert('An error occurred. Please try again.', 'error');
    }
    
    // If the response is HTML content, update the main content
    if (data.includes('<') && data.includes('>')) {
      document.getElementById('main-content').innerHTML = data;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('Network error. Please check your connection.', 'error');
  });
}

// === Global handlers for course management UI (used by dynamically loaded pages) ===
function submitCourseForm(e, form) {
  e.preventDefault();
  const data = new FormData(form);
  fetch(form.action, { method: 'POST', body: data })
    .then(res => res.text())
    .then(html => {
      // Replace main-content if server returned HTML
      if (html.includes('<') && html.includes('>')) {
        document.getElementById('main-content').innerHTML = html;
      } else {
        showAlert('Operation completed', 'success');
      }
    })
    .catch(err => {
      console.error(err);
      showAlert('Network error', 'error');
    });
}

function deleteCourse(courseId) {
  if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
    loadContent('teacher_course_management.php?delete_course=' + courseId, null, false);
  }
}

function showCreateForm() {
  const el = document.getElementById('create-course-form');
  if (el) {
    // Hide any open edit forms first
    document.querySelectorAll('.inline-edit-form').forEach(function(f){ f.style.display = 'none'; });
    el.style.display = 'block';
    el.scrollIntoView({behavior: 'smooth'});
  }
}

function hideCreateForm() {
  const el = document.getElementById('create-course-form');
  if (el) el.style.display = 'none';
}

function showEditForm(cid) {
  // When editing, hide the create form to keep only one open
  const createEl = document.getElementById('create-course-form');
  if (createEl) createEl.style.display = 'none';
  document.querySelectorAll('.inline-edit-form').forEach(function(el){ el.style.display = 'none'; });
  var el = document.getElementById('edit-form-' + cid);
  if (el) {
    el.style.display = 'block';
    el.scrollIntoView({behavior: 'smooth'});
  }
}

function hideEditForm(cid) {
  var el = document.getElementById('edit-form-' + cid);
  if (el) el.style.display = 'none';
}

// === Add CSS for animations ===
const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  .alert {
    animation: slideIn 0.3s ease-out;
  }
`;
document.head.appendChild(style);
function viewAssignmentDetails(assignmentId) {
    // This would open a modal or navigate to a detailed view
    showAlert('Assignment details view will be available in the next update!', 'info');
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.textContent = message;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 6px;
        color: white;
        z-index: 1000;
        font-weight: 500;
    `;
    
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    
    alertDiv.style.backgroundColor = colors[type] || colors.info;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}
