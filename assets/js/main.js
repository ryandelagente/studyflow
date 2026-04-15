// assets/js/main.js

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

document.addEventListener('DOMContentLoaded', () => {
    
    // --- Study Timer Functionality ---
    const timerBtn = document.getElementById('studyTimerBtn');
    const timerDisplay = document.getElementById('timerDisplay');

    if (timerBtn && timerDisplay) {
        let timerInterval;
        let startTime;
        let elapsedTime = 0;
        let isRunning = false;
        let isPaused = false;

        // Formats milliseconds into MM:SS or HH:MM:SS
        const formatTime = (ms) => {
            const totalSeconds = Math.floor(ms / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            const pad = (n) => n.toString().padStart(2, '0');
            return hours > 0
                ? `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`
                : `${pad(minutes)}:${pad(seconds)}`;
        };

        const updateTimer = () => {
            elapsedTime = Date.now() - startTime;
            timerDisplay.textContent = formatTime(elapsedTime);
        };

        const saveSession = () => {
            const durationSeconds = Math.floor(elapsedTime / 1000);
            if (durationSeconds > 0) {
                const base = (typeof window.APP_BASE_URL !== 'undefined') ? window.APP_BASE_URL : '';
                fetch(base + '/api/save-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ duration_seconds: durationSeconds })
                });
            }
        };

        timerBtn.addEventListener('click', () => {
            if (!isRunning && !isPaused) {
                // START
                startTime = Date.now();
                elapsedTime = 0;
                timerInterval = setInterval(updateTimer, 1000);
                timerBtn.classList.replace('bg-gray-900', 'bg-red-500');
                timerBtn.title = 'Pause timer';
                isRunning = true;
            } else if (isRunning) {
                // PAUSE
                clearInterval(timerInterval);
                elapsedTime = Date.now() - startTime;
                timerBtn.classList.replace('bg-red-500', 'bg-yellow-500');
                timerDisplay.textContent = formatTime(elapsedTime) + ' ⏸';
                timerBtn.title = 'Resume timer';
                isRunning = false;
                isPaused = true;
            } else if (isPaused) {
                // RESUME
                startTime = Date.now() - elapsedTime;
                timerInterval = setInterval(updateTimer, 1000);
                timerBtn.classList.replace('bg-yellow-500', 'bg-red-500');
                timerBtn.title = 'Pause timer';
                isRunning = true;
                isPaused = false;
            }
        });

        // Double-click to stop and save
        timerBtn.addEventListener('dblclick', () => {
            if (isRunning || isPaused) {
                clearInterval(timerInterval);
                saveSession();
                elapsedTime = 0;
                isRunning = false;
                isPaused = false;
                timerBtn.classList.remove('bg-red-500', 'bg-yellow-500');
                timerBtn.classList.add('bg-gray-900');
                timerDisplay.textContent = 'Timer Start';
                timerBtn.title = 'Start timer';
            }
        });
    }

    // --- Sidebar Dropdown Toggles ---
    const shareBtn = document.getElementById('shareBtn');
    const settingsBtn = document.getElementById('settingsBtn');

    if (shareBtn) {
        shareBtn.addEventListener('click', () => toggleDropdown('shareDropdown'));
    }
    if (settingsBtn) {
        settingsBtn.addEventListener('click', () => toggleDropdown('settingsDropdown'));
    }
});