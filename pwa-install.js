// PWA Installation and Service Worker Registration Handler
// Guard against being loaded more than once
if (typeof window.__pwaInstallLoaded === 'undefined') {
  window.__pwaInstallLoaded = true;

let deferredPrompt;
let isInstalled = false;

// Check if app is installed
if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
  isInstalled = true;
  console.log('[PWA] App is installed and running in standalone mode');
}

// Register Service Worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', async () => {
    try {
      const registration = await navigator.serviceWorker.register('/service-worker.js', {
        scope: '/'
      });
      
      console.log('[PWA] Service Worker registered successfully:', registration.scope);
      
      // Check for updates
      registration.addEventListener('updatefound', () => {
        const newWorker = registration.installing;
        console.log('[PWA] New Service Worker found');
        
        newWorker.addEventListener('statechange', () => {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            // New service worker available
            showUpdateNotification();
          }
        });
      });
      
      // Check for updates every hour
      setInterval(() => {
        registration.update();
      }, 3600000);
      
    } catch (error) {
      console.error('[PWA] Service Worker registration failed:', error);
    }
  });
}

// Handle install prompt
window.addEventListener('beforeinstallprompt', (e) => {
  console.log('[PWA] Install prompt available');
  
  // Prevent the default install prompt
  e.preventDefault();
  
  // Store the event for later use
  deferredPrompt = e;
  
  // Show custom install button
  showInstallButton();
});

// Show install button
function showInstallButton() {
  const installButton = document.getElementById('pwa-install-button');
  
  if (!installButton && !isInstalled) {
    // Create install button if it doesn't exist
    const button = document.createElement('button');
    button.id = 'pwa-install-button';
    button.innerHTML = '<i class="fas fa-download"></i> Install App';
    button.className = 'pwa-install-btn';
    button.onclick = promptInstall;
    
    // Add button to page
    document.body.appendChild(button);
    
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
      .pwa-install-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px 25px;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
      }
      
      .pwa-install-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
      }
      
      .pwa-install-btn i {
        font-size: 18px;
      }
      
      .pwa-update-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        color: #333;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        max-width: 350px;
        animation: slideIn 0.3s ease;
      }
      
      @keyframes slideIn {
        from {
          transform: translateX(400px);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      
      .pwa-update-notification h4 {
        margin: 0 0 10px 0;
        color: #667eea;
      }
      
      .pwa-update-notification p {
        margin: 0 0 15px 0;
        font-size: 14px;
      }
      
      .pwa-update-notification button {
        background: #667eea;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        cursor: pointer;
        margin-right: 10px;
        font-weight: 600;
      }
      
      .pwa-update-notification button:hover {
        background: #5568d3;
      }
      
      .pwa-update-notification button.secondary {
        background: #f0f0f0;
        color: #333;
      }
      
      .pwa-update-notification button.secondary:hover {
        background: #e0e0e0;
      }
      
      @media (max-width: 768px) {
        .pwa-install-btn {
          bottom: 80px;
          right: 15px;
          padding: 12px 20px;
          font-size: 14px;
        }
        
        .pwa-update-notification {
          top: 10px;
          right: 10px;
          left: 10px;
          max-width: none;
        }
      }
    `;
    document.head.appendChild(style);
  }
}

// Prompt installation
async function promptInstall() {
  if (!deferredPrompt) {
    console.log('[PWA] Install prompt not available');
    return;
  }
  
  // Show the install prompt
  deferredPrompt.prompt();
  
  // Wait for user choice
  const { outcome } = await deferredPrompt.userChoice;
  
  console.log('[PWA] User choice:', outcome);
  
  if (outcome === 'accepted') {
    console.log('[PWA] User accepted the install prompt');
    hideInstallButton();
  } else {
    console.log('[PWA] User dismissed the install prompt');
  }
  
  // Clear the deferred prompt
  deferredPrompt = null;
}

// Hide install button
function hideInstallButton() {
  const button = document.getElementById('pwa-install-button');
  if (button) {
    button.style.display = 'none';
  }
}

// Show update notification
function showUpdateNotification() {
  const notification = document.createElement('div');
  notification.className = 'pwa-update-notification';
  notification.innerHTML = `
    <h4><i class="fas fa-sync-alt"></i> Update Available</h4>
    <p>A new version of Find and Return is available. Reload to update?</p>
    <button onclick="reloadPage()">Update Now</button>
    <button class="secondary" onclick="dismissUpdate(this)">Later</button>
  `;
  
  document.body.appendChild(notification);
  
  // Auto dismiss after 30 seconds
  setTimeout(() => {
    if (notification.parentElement) {
      dismissUpdate(notification);
    }
  }, 30000);
}

// Reload page to activate new service worker
function reloadPage() {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistration().then((registration) => {
      if (registration && registration.waiting) {
        registration.waiting.postMessage({ action: 'skipWaiting' });
      }
    });
  }
  window.location.reload();
}

// Dismiss update notification
function dismissUpdate(element) {
  const notification = element.closest ? element.closest('.pwa-update-notification') : element;
  if (notification && notification.parentElement) {
    notification.style.animation = 'slideIn 0.3s ease reverse';
    setTimeout(() => {
      notification.remove();
    }, 300);
  }
}

// Handle app installed event
window.addEventListener('appinstalled', () => {
  console.log('[PWA] App installed successfully');
  isInstalled = true;
  hideInstallButton();
  
  // Track installation (optional analytics)
  if (typeof gtag !== 'undefined') {
    gtag('event', 'pwa_install', {
      event_category: 'engagement',
      event_label: 'PWA Installed'
    });
  }
});

// Check online/offline status
window.addEventListener('online', () => {
  console.log('[PWA] Back online');
  showNetworkStatus('You are back online', 'success');
});

window.addEventListener('offline', () => {
  console.log('[PWA] Gone offline');
  showNetworkStatus('You are offline. Some features may be limited.', 'warning');
});

// Show network status notification
function showNetworkStatus(message, type) {
  const notification = document.createElement('div');
  notification.className = `pwa-network-status ${type}`;
  notification.textContent = message;
  
  const style = `
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: ${type === 'success' ? '#28a745' : '#ffc107'};
    color: white;
    padding: 15px 30px;
    border-radius: 50px;
    z-index: 9999;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
  `;
  
  notification.style.cssText = style;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.style.animation = 'slideUp 0.3s ease reverse';
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// Add slideUp animation
const animStyle = document.createElement('style');
animStyle.textContent = `
  @keyframes slideUp {
    from {
      transform: translateX(-50%) translateY(100px);
      opacity: 0;
    }
    to {
      transform: translateX(-50%) translateY(0);
      opacity: 1;
    }
  }
`;
document.head.appendChild(animStyle);

// Export functions for use in other scripts
window.PWA = {
  promptInstall,
  reloadPage,
  isInstalled: () => isInstalled
};

console.log('[PWA] Install script loaded');

} // end __pwaInstallLoaded guard
