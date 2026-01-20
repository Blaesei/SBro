/**
 * SpotBro Theme Toggle - Shared Implementation
 * Place this file in: frontend/assets/js/theme-toggle.js
 * 
 * Usage: Include this script in all pages that need theme toggle
 * <script src="../assets/js/theme-toggle.js"></script>
 */


(function() {
    'use strict';
    
    // Theme constants
    const THEME_KEY = 'spotbro-theme';
    const DARK_CLASS = 'dark';
    const LIGHT_THEME = 'light';
    const DARK_THEME = 'dark';
    
    /**
     * Get current theme from localStorage or default to light
     */
    function getCurrentTheme() {
        return localStorage.getItem(THEME_KEY) || LIGHT_THEME;
    }
    
    /**
     * Apply theme to document
     */
    function applyTheme(theme) {
        if (theme === DARK_THEME) {
            document.documentElement.classList.add(DARK_CLASS);
            document.body.classList.add(DARK_CLASS);
        } else {
            document.documentElement.classList.remove(DARK_CLASS);
            document.body.classList.remove(DARK_CLASS);
        }

        // Update toggle button icons if they exist
        updateToggleIcons(theme);

        // Update logo if it exists
        updateLogo(theme);

        // Store in localStorage
        localStorage.setItem(THEME_KEY, theme);
    }
    
    /**
     * Update toggle button icons based on current theme
     */
    function updateToggleIcons(theme) {
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');

        if (!sunIcon || !moonIcon) return;

        if (theme === DARK_THEME) {
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
        } else {
            sunIcon.classList.remove('hidden');
            moonIcon.classList.add('hidden');
        }
    }

    /**
     * Update logo based on current theme
     */
    function updateLogo(theme) {
        const logo = document.getElementById('logo');

        if (!logo) return;

        if (theme === DARK_THEME) {
            logo.src = '../assets/images/logo_light.png';
        } else {
            logo.src = '../assets/images/logo.png';
        }
    }
    
    /**
     * Toggle between light and dark themes
     */
    function toggleTheme() {
        const currentTheme = getCurrentTheme();
        const newTheme = currentTheme === DARK_THEME ? LIGHT_THEME : DARK_THEME;
        applyTheme(newTheme);
        
        // Dispatch custom event for other components that might need to know
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: newTheme } 
        }));
    }
    
    /**
     * Initialize theme on page load
     */
    function initTheme() {
        // Apply saved theme immediately to prevent flash
        const savedTheme = getCurrentTheme();
        applyTheme(savedTheme);
        
        // Wait for DOM to be ready before setting up listeners
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupThemeToggle);
        } else {
            setupThemeToggle();
        }
    }
    
    /**
     * Setup theme toggle button event listener
     */
    function setupThemeToggle() {
        const themeToggleBtn = document.getElementById('themeToggle');
        
        if (themeToggleBtn) {
            // Remove any existing listeners (prevent duplicates)
            themeToggleBtn.replaceWith(themeToggleBtn.cloneNode(true));
            
            // Get the new button reference and add listener
            const newToggleBtn = document.getElementById('themeToggle');
            newToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleTheme();
            });
            
            // Update icons to match current theme
            updateToggleIcons(getCurrentTheme());
        }
    }
    
    /**
     * Listen for storage changes from other tabs/windows
     */
    window.addEventListener('storage', function(e) {
        if (e.key === THEME_KEY) {
            const newTheme = e.newValue || LIGHT_THEME;
            applyTheme(newTheme);
            updateLogo(newTheme); // Also update logo for storage changes
        }
    });
    
    // Initialize theme immediately
    initTheme();
    
    // Expose API for manual control if needed
    window.SpotBroTheme = {
        toggle: toggleTheme,
        set: applyTheme,
        get: getCurrentTheme
    };

    // Runs immediately when script loads (not waiting for DOMContentLoaded)
    const savedTheme = getCurrentTheme();
    if (savedTheme === DARK_THEME) {
        document.documentElement.classList.add(DARK_CLASS);
    if (document.body) {
        document.body.classList.add(DARK_CLASS);
    }
    };
})();
