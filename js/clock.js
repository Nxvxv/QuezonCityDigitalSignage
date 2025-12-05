// Clock and date updater for Live Display
// Shows HH:MM with a tightly-attached AM/PM element to avoid wrapping.

(function () {
  function getCurrentDate() {
    const now = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return now.toLocaleDateString('en-US', options).toUpperCase();
  }

  // Return an object with hour:minute and meridiem so we can render them separately
  function getCurrentTimeParts() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = now.getMinutes();
    const isAM = hours < 12;
    const meridiem = isAM ? 'AM' : 'PM';
    // convert to 12-hour
    hours = hours % 12;
    if (hours === 0) hours = 12;
    const hh = String(hours).padStart(2, '0');
    const mm = String(minutes).padStart(2, '0');
    return { time: `${hh}:${mm}`, meridiem };
  }

  function updateDisplay() {
    const dateElement = document.getElementById('date');
    const timeElement = document.getElementById('time');
    if (dateElement) dateElement.textContent = getCurrentDate();
    if (timeElement) {
      const parts = getCurrentTimeParts();
      // Render time and AM/PM in separate spans so CSS can keep them glued and prevent wrapping.
      timeElement.innerHTML = `${parts.time}<span class="ampm">${parts.meridiem}</span>`;
    }
  }

  function startClock() {
    updateDisplay();
    setInterval(updateDisplay, 1000);
  }

  document.addEventListener('DOMContentLoaded', startClock);
})();
