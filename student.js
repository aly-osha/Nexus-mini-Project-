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

// === Link Click Handler ===
// === Link/Button Click Handler ===
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
      const main = document.getElementById('main-content');
      main.innerHTML = data;

      // 🪄 Re-run any <script> tags from the loaded content
      main.querySelectorAll('script').forEach(oldScript => {
        const newScript = document.createElement('script');
        if (oldScript.src) {
          // External script file (e.g., <script src="something.js"></script>)
          newScript.src = oldScript.src;
        } else {
          // Inline script (e.g., <script>toggleSubmissionForm()</script>)
          newScript.textContent = oldScript.textContent;
        }
        document.body.appendChild(newScript); // append to execute
        oldScript.remove(); // cleanup old one to avoid duplicates
      });

      // === History handling ===
      if (push) {
        window.history.pushState({ page: page }, '', `?page=${page}`);
      }

      // === Active link highlighting ===
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
  const page = (event.state && event.state.page) || 'student_home.php';
  loadContent(page, null, false);
});

// === Initial Load Based on URL ===
window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const page = params.get('page') || 'student_home.php';
  loadContent(page, null, false);
});