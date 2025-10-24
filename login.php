<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login to Your Account | Talent Traverse</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/login.css">
  <script type="text/javascript">
    function preventBack() {
      window.history.forward()
    };
    setTimeout("preventBack()", 0);
    window.onunload = function() {
      null;
    }
  </script>
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
      <img src="./img/Talent.png" alt="Talent Traverse Logo" />
      <div class="logo-text">
        <span class="main-title">Talent Traverse HR TECH PVT LTD</span>
        <span class="tagline" style="text-align: right;">Beyond Boundaries</span>
      </div>
    </div>
    <div class="nav-links">
      <a href="index.php" id="homeLink">Home</a>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container">
    <!-- Image/Art Section with gradient card styling -->
    <div class="image-section">
      <div class="card">
        <div class="card2">
          <div class="image-container">
            <div class="compliance-image">
              <img src="./img/compliance.png" alt="Compliance Image">
            </div>
          </div>
          <div class="image-content">
            <h2>Welcome to Talent Traverse</h2>
            <p>Stay Stress-Free, Stay Compliant – We Handle the Complexity, You Focus on Success.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Login Section with old card styling -->
    <div class="login-section">
      <form class="form" action="includes/login_action.php" method="POST" id="loginForm">
        <!-- Error message container -->
        <div class="error-message" id="errorMessage">
          <?php
          if (isset($_SESSION['error'])) {
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); // remove it so it won't persist on reload
          }
          ?>
        </div>

        <div class="flex-column">
          <label>Email</label>
        </div>
        <div class="inputForm">
          <i class="fas fa-envelope"></i>
          <input type="email" class="input" name="email" placeholder="Enter your Email" required value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
        </div>

        <div class="flex-column">
          <label>Password</label>
        </div>
        <div class="inputForm">
          <i class="fas fa-lock"></i>
          <input type="password" class="input" id="password" name="password" placeholder="Enter your Password" required>
          <button type="button" class="toggle-password" id="togglePassword">
            <i class="fas fa-eye"></i>
          </button>
        </div>

        <!-- CAPTCHA Section with improved styling -->
        <div class="captcha-container">
          <div class="captcha-content">
            <div class="captcha-code" id="captchaText">ABCD12</div>
            <button type="button" class="captcha-refresh" id="refreshCaptcha">
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>

          <div class="captcha-input">
            <input type="text" id="captchaInput" name="captcha" class="captcha-input-field" placeholder="Enter CAPTCHA" required>
          </div>
          <input type="hidden" id="captchaSession" name="captcha_session" value="">
          <div class="captcha-terms">


          </div>
        </div>

        <button type="submit" class="button-submit">Sign In</button>

      </form>
    </div>
  </div>
  <script>
    // Toggle password visibility
    document.addEventListener('DOMContentLoaded', function() {
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const errorMessage = document.getElementById('errorMessage');
      const loginForm = document.getElementById('loginForm');
      const captchaSessionField = document.getElementById('captchaSession');

      // Show error message if it exists
      if (errorMessage.textContent.trim() !== '') {
        errorMessage.style.display = 'block';
      }

      togglePassword.addEventListener('click', function() {
        // Toggle the type attribute
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Toggle the eye icon
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
      });

      // CAPTCHA functionality
      const captchaText = document.getElementById('captchaText');
      const refreshButton = document.getElementById('refreshCaptcha');
      const captchaInput = document.getElementById('captchaInput');

      // Generate random CAPTCHA
      function generateCaptcha() {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < 6; i++) {
          result += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        return result;
      }

      // Set new CAPTCHA
      function setNewCaptcha() {
        const newCaptcha = generateCaptcha();
        captchaText.textContent = newCaptcha;
        captchaInput.value = '';
        captchaSessionField.value = newCaptcha;
      }

      // Refresh CAPTCHA on button click
      refreshButton.addEventListener('click', function() {
        // Add rotation animation
        this.style.transition = 'transform 0.5s ease';
        this.style.transform = 'rotate(360deg)';

        // Generate new CAPTCHA
        setNewCaptcha();

        // Reset rotation after animation completes
        setTimeout(() => {
          this.style.transform = 'rotate(0deg)';
        }, 500);
      });

      // Initialize with a CAPTCHA
      setNewCaptcha();

      // Page transition animation
      function animatePageTransition(href) {
        const transition = document.getElementById('pageTransition');
        transition.style.transform = 'translateX(0)';
        transition.style.transition = 'transform 0.5s ease-in-out';

        setTimeout(() => {
          window.location.href = href;
        }, 500);
      }

      // Add transition to home link
      document.getElementById('homeLink').addEventListener('click', function(e) {
        e.preventDefault();
        animatePageTransition(this.href);
      });

      // Client-side CAPTCHA validation
      loginForm.addEventListener('submit', function(e) {
        const enteredCaptcha = captchaInput.value;
        const expectedCaptcha = captchaSessionField.value;

        // Case-sensitive comparison
        if (enteredCaptcha !== expectedCaptcha) {
          e.preventDefault();
          errorMessage.textContent = 'Please enter a Valid Captcha';
          errorMessage.style.display = 'block';

          // Add shake animation to CAPTCHA container
          const captchaContainer = document.querySelector('.captcha-container');
          captchaContainer.style.animation = 'shake 0.5s';
          setTimeout(() => {
            captchaContainer.style.animation = '';
          }, 500);

          // Generate a new CAPTCHA
          setNewCaptcha();
        }
      });

      // Check if page is loading for the first time and animate in
      window.addEventListener('load', function() {
        document.getElementById('pageTransition').style.transform = 'translateX(-100%)';
        document.getElementById('pageTransition').style.transition = 'transform 0.5s ease-in-out 0.3s';
      });
    });

    // Add shake animation for CAPTCHA errors
    const style = document.createElement('style');
    style.textContent = `
      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>

</html>