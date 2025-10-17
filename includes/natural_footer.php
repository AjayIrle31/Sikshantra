  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Simple JavaScript enhancements
  document.addEventListener('DOMContentLoaded', function() {
    // Add active state to current navigation item
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
      if (currentPath.includes(link.getAttribute('href'))) {
        link.classList.add('active');
      }
    });
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      setTimeout(() => {
        if (alert && alert.parentNode) {
          alert.style.opacity = '0';
          setTimeout(() => {
            if (alert.parentNode) {
              alert.parentNode.removeChild(alert);
            }
          }, 300);
        }
      }, 5000);
    });
  });
</script>

</body>
</html>