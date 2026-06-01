/**
 * SkillSwap — Profile module
 * Upload photo, gestion compétences via API.
 */

document.addEventListener('DOMContentLoaded', () => {
  const avatarInput = document.getElementById('avatarInput');

  // Upload avatar
  if (avatarInput) {
    avatarInput.addEventListener('change', async () => {
      const file = avatarInput.files[0];
      if (!file) return;

      const userId = window.location.pathname.split('/profile/')[1]?.split('/')[0];
      if (!userId) return;

      const formData = new FormData();
      formData.append('avatar', file);

      showToast('info', 'Upload en cours…');

      try {
        const res  = await apiFetch(`/api/users/${userId}/avatar`, {
          method: 'POST',
          headers: { Authorization: 'Bearer ' + getToken() },
          body: formData,
        });
        const json = await res.json();

        if (json.success) {
          showToast('success', 'Photo de profil mise à jour !');
          // Update avatar display
          const avatarEl = document.querySelector('.profile-avatar-wrap .avatar-xl');
          if (avatarEl) {
            if (avatarEl.tagName === 'IMG') {
              avatarEl.src = json.data.photo + '?t=' + Date.now();
            }
          }
        } else {
          showToast('error', json.error || 'Erreur lors de l\'upload.');
        }
      } catch {
        showToast('error', 'Erreur réseau.');
      }
    });
  }
});
