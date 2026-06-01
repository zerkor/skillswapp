/**
 * SkillSwap — Auth module
 * Login, logout, token management.
 */

/**
 * Tente une connexion via l'API JWT.
 *
 * @param {string} email
 * @param {string} password
 * @returns {Promise<{success:boolean, token?:string, user?:object, error?:string}>}
 */
async function apiLogin(email, password) {
  try {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    });
    const json = await res.json();

    if (res.ok && json.token) {
      setToken(json.token);
      return { success: true, token: json.token, user: json.user };
    }

    return { success: false, error: json.message || 'Identifiants incorrects.' };
  } catch {
    return { success: false, error: 'Erreur réseau.' };
  }
}

/**
 * Déconnecte l'utilisateur (supprime le token local).
 */
function apiLogout() {
  removeToken();
}

/**
 * Vérifie si un token JWT est présent (non décodé côté client).
 */
function isLoggedIn() {
  return !!getToken();
}

// Synchronise le header Authorization dans les requêtes fetch globalement
document.addEventListener('DOMContentLoaded', () => {
  // Restore token from sessionStorage fallback
  if (!getToken() && sessionStorage.getItem('skillswap_jwt_session')) {
    setToken(sessionStorage.getItem('skillswap_jwt_session'));
  }
});
