// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {

    /**
     * Toggles the visibility of a sidebar dropdown menu and rotates its icon.
     * @param {string} dropdownId The ID of the dropdown element to toggle.
     */
    function toggleDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        const icon = document.getElementById(dropdownId + 'Icon');

        if (dropdown && icon) {
            dropdown.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    }

    // --- Sidebar Dropdown Functionality ---
    const shareBtn = document.getElementById('shareBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', () => toggleDropdown('shareDropdown'));
    }

    const settingsBtn = document.getElementById('settingsBtn');
    if (settingsBtn) {
        settingsBtn.addEventListener('click', () => toggleDropdown('settingsDropdown'));
    }
    
    // --- Study Timer Functionality ---
    const timerBtn = document.getElementById('studyTimerBtn');
    const timerDisplay = document.getElementById('timerDisplay');

    if (timerBtn && timerDisplay) {
        let timerInterval;
        let startTime;
        let elapsedTime = 0;
        let isRunning = false;

        // Formats milliseconds into a MM:SS or HH:MM:SS string
        const formatTime = (ms) => {
            const totalSeconds = Math.floor(ms / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            const pad = (num) => num.toString().padStart(2, '0');

            if (hours > 0) {
                return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
            }
            return `${pad(minutes)}:${pad(seconds)}`;
        };

        // Updates the timer display every second
        const updateTimer = () => {
            const currentTime = Date.now();
            elapsedTime = currentTime - startTime;
            timerDisplay.textContent = formatTime(elapsedTime);
        };

        // Toggles the timer on/off
        const toggleTimer = () => {
            if (isRunning) {
                // Stop the timer
                clearInterval(timerInterval);
                timerBtn.classList.remove('bg-red-500');
                timerBtn.classList.add('bg-gray-900');
                timerDisplay.textContent = 'Timer Start';
                isRunning = false;
                elapsedTime = 0; // Reset on stop
            } else {
                // Start the timer
                startTime = Date.now() - elapsedTime;
                timerInterval = setInterval(updateTimer, 1000);
                timerBtn.classList.remove('bg-gray-900');
                timerBtn.classList.add('bg-red-500');
                isRunning = true;
            }
        };

        timerBtn.addEventListener('click', toggleTimer);
    }
});