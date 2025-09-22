// Clock and date updater for Live Display
// Moves inline script from index.php into a separate file.

(function () {
  function getCurrentDate() {
    const now = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return now.toLocaleDateString('en-US', options).toUpperCase();
  }

  function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString('en-US', { hour12: true }).toUpperCase();
  }

  function updateDisplay() {
    const dateElement = document.getElementById('date');
    const timeElement = document.getElementById('time');
    if (dateElement) dateElement.textContent = getCurrentDate();
    if (timeElement) timeElement.textContent = getCurrentTime();
  }

  function startClock() {
    updateDisplay();
    setInterval(updateDisplay, 1000);
  }

  document.addEventListener('DOMContentLoaded', startClock);
})();
