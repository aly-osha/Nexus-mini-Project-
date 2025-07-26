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
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const page = link.getAttribute('data-page');
        loadContent(page, link, true);
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

          document.querySelectorAll('.nav-links a').forEach(link => {
            link.classList.remove('active');
          });

          if (element) {
            element.classList.add('active');
          } else {
            const autoLink = Array.from(document.querySelectorAll('.nav-links a'))
              .find(link => link.getAttribute('data-page') === page);
            if (autoLink) autoLink.classList.add('active');
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