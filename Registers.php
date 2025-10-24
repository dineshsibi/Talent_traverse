<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Talent Traverse HR TECH Solutions PVT LTD</title>
  <link rel="stylesheet" href="styles/Registers.css">
  <script type="text/javascript">
    function preventBack() {
      window.history.forward()
    };
    setTimeout("preventBack()", 0);
    window.onunload = function() {
      null;
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <!-- Animated Background -->
  <div class="animated-bg">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>

  <!-- Page Transition Overlay -->
  <div class="page-transition" id="pageTransition"></div>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="logo">
      <img src="./img/Talent.png" alt="Logo" />
      <div class="logo-text">
        <span class="main-title">Talent Traverse HR TECH PVT LTD</span>
        <span class="tagline" style="text-align: right;">Beyond Boundaries</span>
      </div>
    </div>
    <div class="nav-links">
      <!-- Only Logout link remains -->
      <a href="logout.php" id="logoutLink">Logout</a>
    </div>
  </nav>

  <!-- Products Content (Shown by default after login) -->
  <div class="products-container" id="products-content">
    <div class="page-header">
      <h1>Our Compliance Services</h1>
      <p class="page-description">Select a compliance category to manage your documents and stay compliant with regulations</p>
    </div>

    <div class="compliance-categories">
      <!-- Establishment Compliance -->
      <div class="category-card" data-category="establishment">
        <img src="./img/establishment.jpeg" alt="Establishment Compliance" class="card-image">
        <div class="card-content">
          <h2>Establishment Compliance</h2>
          <a href="upload.php?category=establishment" class="card-button">Explore</a>
        </div>
      </div>

      <!-- Payroll Compliance -->
      <div class="category-card" data-category="payroll">
        <img src="./img/payroll.jpeg" alt="Payroll Compliance" class="card-image">
        <div class="card-content">
          <h2>Payroll Compliance</h2>
          <a href="upload.php?category=payroll" class="card-button">Explore</a>
        </div>
      </div>

      <!-- Contract Compliance -->
      <div class="category-card" data-category="contract">
        <img src="./img/contract.jpeg" alt="Contract Compliance" class="card-image">
        <div class="card-content">
          <h2>Contract Compliance</h2>
          <a href="upload.php?category=contract" class="card-button">Explore</a>
        </div>
      </div>

      <!-- Factory Compliance -->
      <div class="category-card" data-category="factory">
        <img src="./img/factory.jpeg" alt="Factory Compliance" class="card-image">
        <div class="card-content">
          <h2>Factory Compliance</h2>
          <a href="upload.php?category=factory" class="card-button">Explore</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle category card clicks
      document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function(e) {
          // Don't trigger if the click was on the button
          if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
            const category = this.getAttribute('data-category');
            // Animate before redirecting
            animatePageTransition(`upload.php?category=${category}`);
          }
        });
      });

      // Handle button clicks specifically
      document.querySelectorAll('.card-button').forEach(button => {
        button.addEventListener('click', function(e) {
          e.stopPropagation(); // Prevent the card click event from firing
          const category = this.closest('.category-card').getAttribute('data-category');
          animatePageTransition(`upload.php?category=${category}`);
        });
      });

      // Page transition animation
      function animatePageTransition(href) {
        const transition = document.getElementById('pageTransition');
        transition.style.transform = 'translateX(0)';
        transition.style.transition = 'transform 0.5s ease-in-out';

        setTimeout(() => {
          window.location.href = href;
        }, 500);
      }

      // Add transition to logout link
      document.getElementById('logoutLink').addEventListener('click', function(e) {
        e.preventDefault();
        animatePageTransition(this.href);
      });

      // Check if page is loading for the first time and animate in
      window.addEventListener('load', function() {
        document.getElementById('pageTransition').style.transform = 'translateX(-100%)';
        document.getElementById('pageTransition').style.transition = 'transform 0.5s ease-in-out 0.3s';

        // Add subtle animation to cards after page load
        setTimeout(() => {
          document.querySelectorAll('.category-card').forEach(card => {
            card.style.opacity = '1';
          });
        }, 300);
      });
    });
  </script>
</body>

</html>