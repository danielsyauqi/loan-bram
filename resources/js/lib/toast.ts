/**
 * Simple toast notification helper
 * This utility uses the browser's native toasts where available and falls back to alert
 */

type ToastType = 'success' | 'error' | 'info' | 'warning';

/**
 * Show a toast notification
 * @param message The message to display
 * @param type The type of toast (success, error, info, warning)
 * @param duration Duration in milliseconds to show the toast
 */
export function showToast(message: string, type: ToastType = 'info', duration: number = 5000): void {
  // Check if the browser supports the Notification API
  if ('Notification' in window && Notification.permission === 'granted') {
    new Notification('Loan Application System', {
      body: message,
      icon: type === 'success' ? '/icons/success.png' : 
            type === 'error' ? '/icons/error.png' : 
            type === 'warning' ? '/icons/warning.png' : '/icons/info.png'
    });
    return;
  }

  // Fallback to a custom toast implementation
  const toast = document.createElement('div');
  toast.textContent = message;
  toast.style.position = 'fixed';
  toast.style.bottom = '20px';
  toast.style.right = '20px';
  toast.style.padding = '12px 24px';
  toast.style.borderRadius = '4px';
  toast.style.zIndex = '9999';
  toast.style.boxShadow = '0 3px 6px rgba(0, 0, 0, 0.16)';
  toast.style.transition = 'all 0.3s ease';
  toast.style.opacity = '0';
  toast.style.transform = 'translateY(20px)';

  // Set style based on type
  switch (type) {
    case 'success':
      toast.style.backgroundColor = '#4CAF50';
      toast.style.color = 'white';
      break;
    case 'error':
      toast.style.backgroundColor = '#F44336';
      toast.style.color = 'white';
      break;
    case 'warning':
      toast.style.backgroundColor = '#FF9800';
      toast.style.color = 'white';
      break;
    case 'info':
    default:
      toast.style.backgroundColor = '#2196F3';
      toast.style.color = 'white';
      break;
  }

  // Add to DOM
  document.body.appendChild(toast);

  // Trigger animation
  setTimeout(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
  }, 10);

  // Remove after duration
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
    
    // Remove from DOM after animation
    setTimeout(() => {
      document.body.removeChild(toast);
    }, 300);
  }, duration);
} 