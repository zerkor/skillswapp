/**
 * SkillSwap — API client
 * Wrapper fetch avec gestion JWT, intercepteurs et format de réponse standard.
 */

const TOKEN_KEY = 'skillswap_jwt';

/** Récupère le token JWT stocké. */
function getToken() {
  return localStorage.getItem(TOKEN_KEY);
}

/** Stocke le token JWT. */
function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token);
}

/** Supprime le token JWT (déconnexion). */
function removeToken() {
  localStorage.removeItem(TOKEN_KEY);
}

/** Construit les headers d'authentification. */
function getAuthHeaders() {
  const token = getToken();
  const headers = { 'Content-Type': 'application/json' };
  if (token) {
    headers['Authorization'] = 'Bearer ' + token;
  }
  return headers;
}

/**
 * Wrapper fetch avec token JWT automatique.
 *
 * @param {string} url
 * @param {RequestInit} options
 * @returns {Promise<Response>}
 */
async function apiFetch(url, options = {}) {
  const defaults = {
    headers: getAuthHeaders(),
  };

  const config = {
    ...defaults,
    ...options,
    headers: {
      ...defaults.headers,
      ...(options.headers || {}),
    },
  };

  const response = await fetch(url, config);

  // Si 401, token expiré — rediriger vers login
  if (response.status === 401) {
    removeToken();
    if (!window.location.pathname.startsWith('/login')) {
      window.location.href = '/login?expired=1';
    }
  }

  return response;
}

/**
 * Appel API avec parsing JSON automatique.
 * Retourne { success, data, message } ou { success, error, code }.
 *
 * @param {string} url
 * @param {RequestInit} options
 * @returns {Promise<{success: boolean, data?: any, error?: string}>}
 */
async function apiCall(url, options = {}) {
  try {
    const res  = await apiFetch(url, options);
    const json = await res.json();
    return json;
  } catch (err) {
    return { success: false, error: 'Erreur réseau. Vérifiez votre connexion.', code: 0 };
  }
}

/**
 * Formate une date ISO en français.
 *
 * @param {string} isoDate
 * @returns {string}
 */
function formatDate(isoDate) {
  if (!isoDate) return '';
  const d = new Date(isoDate);
  const now = new Date();
  const diff = now - d;

  if (diff < 60_000)              return 'À l\'instant';
  if (diff < 3_600_000)           return `Il y a ${Math.floor(diff / 60_000)} min`;
  if (diff < 86_400_000)          return `Il y a ${Math.floor(diff / 3_600_000)} h`;
  if (diff < 7 * 86_400_000)      return `Il y a ${Math.floor(diff / 86_400_000)} j`;

  return d.toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric',
  });
}

/**
 * Affiche un toast de notification temporaire.
 *
 * @param {'success'|'error'|'warning'|'info'} type
 * @param {string} message
 * @param {number} duration  milliseconds
 */
function showToast(type, message, duration = 3500) {
  const existing = document.getElementById('skillswap-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.id = 'skillswap-toast';
  toast.className = `flash flash-${type}`;
  toast.setAttribute('role', 'alert');
  toast.style.cssText = `
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    max-width: 360px;
    border-radius: var(--radius-lg);
    animation: toastIn 0.25s ease;
  `;
  toast.textContent = message;

  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'toastOut 0.25s ease forwards';
    setTimeout(() => toast.remove(), 250);
  }, duration);
}

// Inject toast animations
(function injectToastStyles() {
  const style = document.createElement('style');
  style.textContent = `
    @keyframes toastIn  { from { opacity:0; transform:translateY(1rem); } to { opacity:1; transform:translateY(0); } }
    @keyframes toastOut { from { opacity:1; transform:translateY(0); } to { opacity:0; transform:translateY(1rem); } }
  `;
  document.head.appendChild(style);
})();
