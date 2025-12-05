/*
  signage-autoplay.js
  Attempts to autoplay the #signageVideo with sound. Many browsers block autoplay with sound;
  this script falls back to muted playback and then attempts to unmute shortly after playback starts.
*/
(function(){
  'use strict';
  function tryAutoplayWithSound(){
    var v = document.getElementById('signageVideo');
    if(!v) return;
    try { v.volume = 0.8; } catch(e) { console.warn('Could not set volume:', e); }
    var p = v.play();
    if (p !== undefined) {
      p.catch(function(err){
        console.warn('Autoplay with sound blocked, attempting muted play...', err);
        v.muted = true;
        v.play().then(function(){
          // After muted playback starts, attempt to unmute after a short delay
          setTimeout(function(){
            try { v.muted = false; } catch(e){ console.warn('Could not unmute:', e); }
          }, 800);
        }).catch(function(e){ console.warn('Muted autoplay also failed:', e); });
      });
    }
  }

  // Run when DOM is ready. This file is typically loaded at the end of body, but guard anyway.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryAutoplayWithSound);
  } else {
    tryAutoplayWithSound();
  }
})();
