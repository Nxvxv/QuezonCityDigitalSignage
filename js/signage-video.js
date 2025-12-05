document.addEventListener('DOMContentLoaded', function () {
  const preview = document.querySelector('.video-preview');
  if (!preview) return;
  let video = preview.querySelector('video');

  // Helper to create video element if missing
  function ensureVideo() {
    video = preview.querySelector('video');
    if (!video) {
      video = document.createElement('video');
      video.setAttribute('playsinline', '');
      video.setAttribute('preload', 'auto');
      video.setAttribute('autoplay', '');
      video.setAttribute('loop', '');
      preview.appendChild(video);
    }
  }
  ensureVideo();

  // Request autoplay with sound. Modern browsers often block this — if blocked,
  // Poll server for current active video and set src accordingly.
  const API = '../php/current_video.php';
  let currentVideoFile = null;

  async function loadCurrentVideo() {
    try {
      const res = await fetch(API, { cache: 'no-store' });
      const j = await res.json();
      if (j.success && j.video) {
        const file = j.video;
        if (file !== currentVideoFile) {
          currentVideoFile = file;
          ensureVideo();
          const src = '../assets/uploads/videos/' + encodeURIComponent(file);
          // replace source(s)
          video.innerHTML = '';
          const source = document.createElement('source');
          source.src = src;
          source.type = 'video/mp4';
          video.appendChild(source);
          // autoplay muted to satisfy browser policies
          video.muted = true;
          video.volume = 0.9;
          tryPlayWithRetries();
        }
      } else {
        // no active video; pause and clear src
        currentVideoFile = null;
        if (video && !video.paused) video.pause();
        video && (video.innerHTML = '');
      }
    } catch (e) {
      console.error('Failed to load current video', e);
    }
  }

  const tryPlayWithRetries = () => {
    const attempt = () => {
      const p = video.play();
      if (p !== undefined) {
        p.catch((err) => {
          console.warn('Autoplay with sound blocked (no UI):', err);
        });
      }
    };
    attempt();
    setTimeout(attempt, 500);
    setTimeout(attempt, 1500);
  };

  // initial load and poll every 30s
  loadCurrentVideo();
  setInterval(loadCurrentVideo, 30000);
});
