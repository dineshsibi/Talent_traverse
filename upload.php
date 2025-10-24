<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  echo "Unauthorized: Please login first.";
  exit();
}

// Get the category from URL parameter
$category = $_GET['category'] ?? 'establishment';

// Check for upload messages from handle_upload files
$uploadMessage = '';
$messageType = ''; // 'success', 'warning', 'error'

if (isset($_SESSION['success'])) {
  $uploadMessage = $_SESSION['success'];
  $messageType = 'success';
  unset($_SESSION['success']);
} elseif (isset($_SESSION['warning'])) {
  $uploadMessage = $_SESSION['warning'];
  $messageType = 'warning';
  unset($_SESSION['warning']);
} elseif (isset($_SESSION['error'])) {
  $uploadMessage = $_SESSION['error'];
  $messageType = 'error';
  unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Upload Excel File - Talent Traverse HR TECH Solutions PVT LTD</title>
  <link rel="stylesheet" href="styles/upload.css">
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

 <!-- Navbar -->
  <nav class="navbar">
    <div class="logo">
      <img src="./img/Talent.png" alt="Logo" />
      <div class="logo-text">
        <span class="main-title">Talent Traverse HR TECH PVT LTD</span>
        <span class="tagline" style="text-align:right;">Beyond Boundaries</span>
      </div>
    </div>
    <div class="nav-links">
      <a href="Registers.php">Back</a>
      <a href="logout.php" id="logoutLink">Logout</a>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container-wrapper">
    <!-- Compliance Details Page -->
    <div class="compliance-details" id="compliance-details">
      <div class="details-container">
        <div class="sidebar">
          <div class="sidebar-header">
            <h2 contenteditable="true" id="sidebar-title">Products</h2>
          </div>
          <ul class="sidebar-menu" id="sidebar-menu">
            <!-- Menu items will be dynamically inserted here -->
          </ul>
        </div>
        <div class="content">
          <!-- Button Container (initially hidden) -->
          <div class="button-container" id="button-container" style="display: none;">
            <!-- Download Button -->
            <form action="download.php" method="POST" id="download-form">
              <input type="hidden" name="file" id="download-file-input" value="">
              <button class="download-button" type="submit">
                <span class="button__text">Format</span>
                <span class="button__icon">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 35 35" id="bdd05811-e15d-428c-bb53-8661459f9307" data-name="Layer 2" class="svg">
                    <path d="M17.5,22.131a1.249,1.249,0,0,1-1.25-1.25V2.187a1.25,1.25,0,0,1,2.5,0V20.881A1.25,1.25,0,0,1,17.5,22.131Z"></path>
                    <path d="M17.5,22.693a3.189,3.189,0,0,1-2.262-.936L8.487,15.006a1.249,1.249,0,0,1,1.767-1.767l6.751,6.751a.7.7,0,0,0,.99,0l6.751-6.751a1.25,1.25,0,0,1,1.768,1.767l-6.752,6.751A3.191,3.191,0,0,1,17.5,22.693Z"></path>
                    <path d="M31.436,34.063H3.564A3.318,3.318,0,0,1,.25,30.749V22.011a1.25,1.25,0,0,1,2.5,0v8.738a.815.815,0,0,0,.814.814H31.436a.815.815,0,0,0,.814-.814V22.011a1.25,1.25,0,1,1,2.5,0v8.738A3.318,3.318,0,0,1,31.436,34.063Z"></path>
                  </svg>
                </span>
              </button>
            </form>
          </div>
          
          <div class="content-header">
            <h1 id="content-title">Compliance Details</h1>
            <p class="content-description" id="content-description">Select an option from the sidebar to view details.</p>
          </div>
          <div class="content-area" id="content-area">
            <!-- Upload message will be displayed here -->
            <?php if (!empty($uploadMessage)): ?>
              <div class="upload-message-container message-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($uploadMessage); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarTitle = document.getElementById('sidebar-title');
      const sidebarMenu = document.getElementById('sidebar-menu');
      const contentTitle = document.getElementById('content-title');
      const contentDescription = document.getElementById('content-description');
      const contentArea = document.getElementById('content-area');
      const buttonContainer = document.getElementById('button-container');
      const downloadFileInput = document.getElementById('download-file-input');
      const downloadForm = document.getElementById('download-form');
      
      // Get the category from PHP
      const category = '<?php echo $category; ?>';
      
      // Define the menu options for each category
      const categoryOptions = {
        establishment: [
          { text: "Registers", type: "se", url: "#" },
          { text: "Returns", url: "establishment_compliance.php?service=returns" },
          { text: "Display Notices", url: "establishment_compliance.php?service=notices" },
          { text: "POG Forms", url: "establishment_compliance.php?service=pog" }
        ],
        payroll: [
          { text: "Remittances", url: "payroll_compliance.php?service=pf" },
          { text: "Gratuity", url: "payroll_compliance.php?service=tds" },
          { text: "Minimum Wages", url: "payroll_compliance.php?service=esi" },
          { text: "Overtime and Holiday", url: "payroll_compliance.php?service=bonus" }
        ],
        contract: [
          { text: "Registers", type: "clra", url: "#" },
          { text: "Returns", url: "contract_compliance.php?service=license" },
          { text: "Display Notices", url: "contract_compliance.php?service=agreement" },
          { text: "POG Forms", url: "contract_compliance.php?service=monitoring" }
        ],
        factory: [
          { text: "Registers", url: "factory_compliance.php?service=boiler" },
          { text: "Returns", url: "factory_compliance.php?service=license" },
          { text: "Display Notices", url: "factory_compliance.php?service=safety" },
          { text: "Up coming", url: "factory_compliance.php?service=water" }
        ]
      };
      
      // Define titles and descriptions for each category
      const categoryDetails = {
        establishment: {
          title: "Establishment Compliance",
          description: "Manage all aspects of your establishment compliance from one place."
        },
        payroll: {
          title: "Payroll Compliance",
          description: "Streamline your payroll processes while ensuring full compliance."
        },
        contract: {
          title: "Contract Compliance",
          description: "Manage contract labor compliance effectively with our specialized services."
        },
        factory: {
          title: "Factory Compliance",
          description: "Maintain full compliance with factory regulations and safety standards."
        }
      };

      // Define content titles for each menu item
      const menuItemTitles = {
        establishment: {
          "Registers": "Establishment Registers",
          "Returns": "Establishment Returns",
          "Display Notices": "Establishment Display Notices",
          "POG Forms": "Establishment POG Forms"
        },
        payroll: {
          "Remittances": "Payroll Remittances",
          "Gratuity": "Gratuity Management",
          "Minimum Wages": "Minimum Wages Compliance",
          "Overtime and Holiday": "Overtime and Holiday Management"
        },
        contract: {
          "Registers": "Contract Labor Registers",
          "Returns": "Contract Labor Returns",
          "Display Notices": "Contract Labor Display Notices",
          "POG Forms": "Contract Labor POG Forms"
        },
        factory: {
          "Registers": "Factory Registers",
          "Returns": "Factory Returns",
          "Display Notices": "Factory Display Notices",
          "Up coming": "Upcoming Factory Compliance"
        }
      };

      // Define content descriptions for each menu item
      const menuItemDescriptions = {
        establishment: {
          "Registers": "Upload and manage establishment registers",
          "Returns": "File and track establishment returns",
          "Display Notices": "Manage display notices for your establishment",
          "POG Forms": "Handle POG forms for establishment compliance"
        },
        payroll: {
          "Remittances": "Manage payroll remittances and contributions",
          "Gratuity": "Calculate and process gratuity payments",
          "Minimum Wages": "Ensure compliance with minimum wage regulations",
          "Overtime and Holiday": "Track and manage overtime and holiday payments"
        },
        contract: {
          "Registers": "Upload and manage contract labor registers",
          "Returns": "File and track contract labor returns",
          "Display Notices": "Manage display notices for contract labor",
          "POG Forms": "Handle POG forms for contract labor compliance"
        },
        factory: {
          "Registers": "Upload and manage factory registers",
          "Returns": "File and track factory returns",
          "Display Notices": "Manage display notices for your factory",
          "Up coming": "View upcoming factory compliance requirements"
        }
      };

      // Variable to store the current register type
      let currentRegisterType = '';
      
      // Update the sidebar and content based on the selected category
      updateComplianceDetails(category);
      
      // Function to update the compliance details based on category
      function updateComplianceDetails(category) {
        // Update titles
        sidebarTitle.textContent = "Products";
        contentTitle.textContent = categoryDetails[category].title;
        contentDescription.textContent = categoryDetails[category].description;
        
        // Clear previous menu items
        sidebarMenu.innerHTML = '';
        
        // Add new menu items
        categoryOptions[category].forEach(item => {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = item.url;
          a.textContent = item.text;
          a.setAttribute('data-type', item.type || '');
          a.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
              link.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Update content title and description based on the clicked menu item
            contentTitle.textContent = menuItemTitles[category][item.text];
            contentDescription.textContent = menuItemDescriptions[category][item.text];
            
            // Clear the content area completely (except upload message)
            const uploadMessage = contentArea.querySelector('.upload-message-container');
            contentArea.innerHTML = '';
            if (uploadMessage) {
              contentArea.appendChild(uploadMessage);
            }
            
            // If this is a "Registers" item, show the upload containers and buttons
            if (item.text === "Registers" && item.type) {
              currentRegisterType = item.type;
              showUploadContainers(item.type);
              showButtons(item.type);
            } else {
              // For other items, show regular content and hide buttons
              hideUploadContainers();
              hideButtons();
              const welcomeMessage = document.createElement('div');
              welcomeMessage.className = 'welcome-message';
              welcomeMessage.innerHTML = `
                <h2>${menuItemTitles[category][item.text]}</h2>
                <p>${menuItemDescriptions[category][item.text]}</p>
                <p>In a real application, this would show specific details and forms related to ${item.text}.</p>
              `;
              contentArea.appendChild(welcomeMessage);
            }
          });
          li.appendChild(a);
          sidebarMenu.appendChild(li);
        });
        
        // Reset content area (preserve upload message if exists)
        const uploadMessage = contentArea.querySelector('.upload-message-container');
        contentArea.innerHTML = '';
        if (uploadMessage) {
          contentArea.appendChild(uploadMessage);
        }
        
        const welcomeMessage = document.createElement('div');
        welcomeMessage.className = 'welcome-message';
        welcomeMessage.innerHTML = `
          <h2>Welcome to ${categoryDetails[category].title}</h2>
          <p>${categoryDetails[category].description}</p>
          <p>Select an option from the sidebar to get started.</p>
        `;
        contentArea.appendChild(welcomeMessage);
        
        // Hide buttons by default
        hideButtons();
      }
      
      // Function to show upload containers
      function showUploadContainers(type) {
        // Hide any existing upload containers
        hideUploadContainers();
        
        // Create upload containers
        const uploadType = type;
        const uploadScript = (uploadType === 'clra') ? 'handle_upload1.php' : 'handle_upload.php';
        const uploadTitle = (uploadType === 'clra') ? 'Upload CLRA Excel Form' : 'Upload S&E Excel Form (Input / NFH / CLRA)';
        
        // Create upload container
        const uploadContainer = document.createElement('div');
        uploadContainer.className = 'upload-container';
        uploadContainer.innerHTML = `
          <h2>${uploadTitle}</h2>
          <form action="${uploadScript}" method="POST" enctype="multipart/form-data" id="upload-form">
            <input type="file" name="excel_file" accept=".xlsx, .xls" required>
            <button class="upload-btn" type="submit">Upload</button>
          </form>
          <div id="upload-status"></div>
        `;
        
        // Create generate section
        const generateSection = document.createElement('div');
        generateSection.className = 'generate-section';
        generateSection.innerHTML = `
          <p class="upload-message">If You Already Uploaded the Data, Then Click the Generate Button</p>
          <button class="generate-btn" id="generate-button">
            <span>Generate</span>
            <span class="arrow">>>></span>
          </button>
        `;
        
        // Add animation to the generate button
        const generateBtn = generateSection.querySelector('#generate-button');
        setTimeout(() => {
          generateBtn.classList.add('animate');
        }, 1000);
        
        // Add event listener to the Generate button
        generateBtn.addEventListener('click', function() {
          // Redirect based on the current register type
          if (currentRegisterType === 'se') {
            window.location.href = 'temp.php';
          } else if (currentRegisterType === 'clra') {
            window.location.href = 'temp1.php';
          }
        });
        
        // Add form submission handler
        const uploadForm = uploadContainer.querySelector('#upload-form');
        uploadForm.addEventListener('submit', function(e) {
          const submitBtn = this.querySelector('.upload-btn');
          submitBtn.disabled = true;
          submitBtn.textContent = 'Uploading...';
        });
        
        // Add container to content area
        contentArea.appendChild(uploadContainer);
        contentArea.appendChild(generateSection);
        
        // Show the container
        uploadContainer.style.display = 'block';
      }
      
      // Function to show buttons
      function showButtons(type) {
        const formatFile = (type === 'clra') ? 'CLRA Format.xlsx' : 'S&E Format.xlsx';
        downloadFileInput.value = formatFile;
        buttonContainer.style.display = 'flex';
      }
      
      // Function to hide buttons
      function hideButtons() {
        buttonContainer.style.display = 'none';
      }
      
      // Function to hide upload containers
      function hideUploadContainers() {
        const existingUpload = document.querySelector('.upload-container');
        if (existingUpload) existingUpload.remove();
        
        const existingGenerate = document.querySelector('.generate-section');
        if (existingGenerate) existingGenerate.remove();
      }
    });
  </script>
</body>
</html>
