

window.toggleSubmissionForm = function(assignmentId) {
    const form = document.getElementById('form_' + assignmentId);
    if (!form) {
        console.error("Form not found for assignment:", assignmentId);
        return;
    }
    form.style.display = (form.style.display === 'none' || form.style.display === '') 
        ? 'block' : 'none';
    if (form.style.display === 'block') {
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
};

window.submitAssignmentForm = function(e, form) {
    e.preventDefault();
    const data = new FormData(form);
    fetch(form.action || 'student.php', { method: "POST", body: data })
      .then(res => res.text())
      .then(html => {
        document.getElementById("main-content").innerHTML = html;
      });
};

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