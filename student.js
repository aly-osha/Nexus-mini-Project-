// === Profile Drawer Toggle ===
const pic = document.getElementById('profilePic');
const drawer = document.getElementById('drawer');

pic.addEventListener('click', () => {
  drawer.classList.toggle('open');
});

document.addEventListener('click', function (event) {
  if (!pic.contains(event.target) && !drawer.contains(event.target)) {
    drawer.classList.remove('open');
  }
});

// === Load Page Content Dynamically + Manage History ===
function loadContent(page, element) {
  fetch(page)
    .then(response => response.text())
    .then(data => {
      document.getElementById('main-content').innerHTML = data;

      // Update browser URL + history
      window.history.pushState({ page: page }, '', `?page=${page}`);

      // Highlight active nav link
      document.querySelectorAll('.nav-links a').forEach(link => {
        link.classList.remove('active');
      });
      if (element) element.classList.add('active');
    })
    .catch(error => {
      document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
      console.error(error);
    });
}

// === Handle Back/Forward Navigation ===
window.addEventListener('popstate', event => {
  const page = (event.state && event.state.page) || 'home.html';

  // Try to find the matching nav link by checking its onclick content
  const matchingLink = Array.from(document.querySelectorAll('.nav-links a')).find(link => {
    return link.getAttribute('onclick')?.includes(page);
  });

  loadContent(page, matchingLink);
});

// === Load Initial Content from URL (on page refresh or direct access) ===
window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const page = params.get('page') || 'home.html';

  // Find the matching nav link for initial highlight
  const matchingLink = Array.from(document.querySelectorAll('.nav-links a')).find(link => {
    return link.getAttribute('onclick')?.includes(page);
  });

  loadContent(page, matchingLink);
});
