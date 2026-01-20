<?php
// Get current page filename for active class
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- Navigation -->
<nav class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between max-w-7xl mx-auto">
        <div class="flex items-center space-x-2">
            <img id="logo" src="../assets/images/logo.png" alt="SpotBro Logo" class="h-12" onerror="this.style.display='none'">
        </div>
        <div class="flex items-center space-x-6">
            <a href="home.php" class="nav-item <?php echo ($current_page == 'home') ? 'active' : ''; ?> flex items-center space-x-2 px-4 py-2 rounded-lg">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span class="font-medium">Home</span>
            </a>
            <a href="about.php" class="nav-item <?php echo ($current_page == 'about') ? 'active' : ''; ?> flex items-center space-x-2 px-4 py-2 rounded-lg">
                <svg class="icon" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <path d="M12 17h.01"></path>
                </svg>
                <span class="font-medium">About Us</span>
            </a>
            <a href="faq.php" class="nav-item <?php echo ($current_page == 'faq') ? 'active' : ''; ?> flex items-center space-x-2 px-4 py-2 rounded-lg">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M9 12h6"></path>
                    <path d="M9 16h6"></path>
                    <path d="M9 8h6"></path>
                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                    <path d="M8 16H3v5"></path>
                </svg>
                <span class="font-medium">FAQ</span>
            </a>
            <a href="exercises.php" class="nav-item <?php echo ($current_page == 'exercises') ? 'active' : ''; ?> flex items-center space-x-2 px-4 py-2 rounded-lg">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="m6.5 6.5 11 11"></path>
                    <path d="m21 21-1-1"></path>
                    <path d="m3 21 9-9"></path>
                    <circle cx="10.5" cy="10.5" r="7.5"></circle>
                </svg>
                <span class="font-medium">Exercises</span>
            </a>
            <a href="progress.php" class="nav-item <?php echo ($current_page == 'progress') ? 'active' : ''; ?> flex items-center space-x-2 px-4 py-2 rounded-lg">
                <svg class="icon" viewBox="0 0 24 24">
                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                    <polyline points="16 7 22 7 22 13"></polyline>
                </svg>
                <span class="font-medium">Progress</span>
            </a>

            <button id="themeToggle" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg">
                <svg class="icon" id="sunIcon" viewBox="0 0 24 24">
                     <circle cx="12" cy="12" r="5"></circle>
                     <line x1="12" y1="1" x2="12" y2="3"></line>
                     <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                 </svg>
                 <svg class="icon hidden" id="moonIcon" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>

            <a href="#" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50" id="logoutBtn">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" x2="9" y1="12" y2="12"></line>
                </svg>
                <span class="font-medium">Logout</span>
            </a>
        </div>
    </div>
</nav>
