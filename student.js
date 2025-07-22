function switchTab(tabId) {
  document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('visible'));
  document.getElementById(tabId).classList.add('visible');

  document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
  event.target.classList.add('active');
}

function toggleSubTab(tabId) {
  document.querySelectorAll('.subtab-content').forEach(tab => tab.classList.remove('visible'));
  document.getElementById(tabId).classList.add('visible');

  document.querySelectorAll('.subtab').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
}
