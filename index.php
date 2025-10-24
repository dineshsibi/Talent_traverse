<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Talent Traverse - Home</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="weather.css">
  <link rel="stylesheet" href="styles/index.css">
</head>
<body>
  <!-- Animated Background -->
  <div class="animated-bg">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>

  <!-- Floating Elements -->
  <div class="floating-elements">
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
  </div>

  <!-- Page Transition Overlay -->
  <div class="page-transition" id="pageTransition"></div>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="logo">
      <video autoplay loop muted playsinline>
        <source src="./img/Talent1.webm" type="video/webm">
        <!-- Fallback for browsers that don't support webm -->
        <img src="./img/fallback-logo.png" alt="Logo">
      </video>
      <div class="logo-text">
        <span class="main-title">Talent Traverse HR TECH PVT LTD</span>
        <span class="tagline" style="text-align: right;">Beyond Boundaries</span>
      </div>
    </div>
    <div class="nav-links">
      <a href="login.php" id="loginLink">Login</a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <h1>Your Trusted Partner in HR Compliance & Payroll Excellence</h1>
      <p>We simplify compliance, manage payroll, and empower businesses with innovative HR solutions. Let us handle your compliance while you focus on growth.</p>
    </div>
    <div class="hero-slideshow">
      <div class="slideshow-container">
        <!-- Slideshow images -->
        <img class="slide active" src="./img/c1.png" alt="HR Solutions">
        <img class="slide" src="./img/c2.png" alt="Team Collaboration">
        <img class="slide" src="./img/c3.png" alt="Payroll Management">
        <img class="slide" src="./img/c4.png" alt="Business Growth">
        <img class="slide" src="./img/c5.png" alt="Business Growth">
        <img class="slide" src="./img/c6.png" alt="Business Growth">
        
        <!-- Navigation dots -->
        <div class="slide-controls">
          <span class="dot active" onclick="currentSlide(1)"></span>
          <span class="dot" onclick="currentSlide(2)"></span>
          <span class="dot" onclick="currentSlide(3)"></span>
          <span class="dot" onclick="currentSlide(4)"></span>
          <span class="dot" onclick="currentSlide(5)"></span>
          <span class="dot" onclick="currentSlide(6)"></span>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    © 2025 Talent Traverse HR TECH PVT LTD | All Rights Reserved
  </footer>

  <script>
    // Slideshow functionality
    let slideIndex = 0;
    const slides = document.getElementsByClassName("slide");
    const dots = document.getElementsByClassName("dot");
    
    // Initialize the slideshow
    showSlides();
    
    function showSlides() {
      // Hide all slides
      for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
        dots[i].classList.remove("active");
      }
      
      // Move to next slide
      slideIndex++;
      if (slideIndex > slides.length) { slideIndex = 1; }
      
      // Show current slide
      slides[slideIndex - 1].classList.add("active");
      dots[slideIndex - 1].classList.add("active");
      
      // Change slide every 5 seconds
      setTimeout(showSlides, 5000);
    }

    // Manual navigation function
    function currentSlide(n) {
      slideIndex = n;
      
      // Hide all slides
      for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
        dots[i].classList.remove("active");
      }
      
      // Show selected slide
      slides[slideIndex - 1].classList.add("active");
      dots[slideIndex - 1].classList.add("active");
    }

    // Page transition animation
    function animatePageTransition(href) {
      const transition = document.getElementById('pageTransition');
      transition.style.transform = 'translateX(0)';
      transition.style.transition = 'transform 0.5s ease-in-out';
      
      setTimeout(() => {
        window.location.href = href;
      }, 500);
    }

    // Add transition to login link
    document.getElementById('loginLink').addEventListener('click', function(e) {
      e.preventDefault();
      animatePageTransition(this.href);
    });

    // Check if page is loading for the first time and animate in
    window.addEventListener('load', function() {
      document.getElementById('pageTransition').style.transform = 'translateX(-100%)';
      document.getElementById('pageTransition').style.transition = 'transform 0.5s ease-in-out 0.3s';
    });
  </script>
</body>
</html>