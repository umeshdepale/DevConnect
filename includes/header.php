<nav class="bg-white shadow-sm w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo Section -->
            <div class="flex items-center">
            <a href="index.php"><h1 class="text-xl font-bold text-indigo-600">DevConnect</h1></a> 
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex space-x-4">
                
            <?php if(isset($_SESSION['user_id'])) { ?>

                    
                <a href="topup.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Top Up</a>
                <a href="developer_mode.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Switch to Developer Mode</a>
                <a href="http://localhost/cam/includes/logout.php" target="" class="text-indigo-600 hover:text-indigo-800">
                <i class="bi bi-box-arrow-right"></i>
            </a>
                <?php }else{ ?>
                <a href="login.php" class="text-gray-600 hover:text-gray-900">Login</a>
                <a href="signup.php" class="text-gray-600 hover:text-gray-900">Sign Up</a> <?php } ?>
            </div>

            <!-- Mobile Hamburger Menu -->
            <div class="lg:hidden">
                <button id="navToggle" class="text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobileMenu" class="hidden lg:hidden bg-white shadow-md">
    <?php if(isset($_SESSION['user_id'])) { ?>
        <a href="topup.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Top Up</a>
        <a href="developer_mode.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Switch to Developer Mode</a>
        <a href="logout.php" target="_blank" class="text-indigo-600 hover:text-indigo-800">
        <i class="bi bi-box-arrow-right"></i>
            </a>
        <?php }else{ ?>
        <a href="login.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Login</a>
        <a href="signup.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Sign Up</a> <?php } ?>
    </div>
</nav>

<script>
    // Toggle menu visibility for mobile
    document.getElementById("navToggle").addEventListener("click", function () {
        const mobileMenu = document.getElementById("mobileMenu");
        mobileMenu.classList.toggle("hidden");
    });
</script>
