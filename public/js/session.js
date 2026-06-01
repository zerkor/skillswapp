/**
 * SkillSwap — Session module
 * Gestion des sessions, tabs, avis.
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── Tabs filtrage ──
  const tabBtns      = document.querySelectorAll('.tab-btn');
  const sessionCards = document.querySelectorAll('.session-card');

  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      tabBtns.forEach(b => {
        b.classList.remove('tab-btn--active');
        b.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('tab-btn--active');
      btn.setAttribute('aria-selected', 'true');

      const filter = btn.dataset.filter;

      sessionCards.forEach(card => {
        const show = filter === 'all' || card.dataset.filter === filter;
        card.style.display = show ? '' : 'none';
      });
    });
  });

  // ── Star ratings ──
  document.querySelectorAll('.star-rating').forEach(container => {
    const stars = container.querySelectorAll('.star-btn');
    let selected = 0;

    stars.forEach(star => {
      star.addEventListener('mouseenter', () => {
        const val = parseInt(star.dataset.value);
        stars.forEach((s, i) => s.classList.toggle('selected', i < val));
      });

      star.addEventListener('mouseleave', () => {
        stars.forEach((s, i) => s.classList.toggle('selected', i < selected));
      });

      star.addEventListener('click', () => {
        selected = parseInt(star.dataset.value);
        container.dataset.selected = selected;
        stars.forEach((s, i) => s.classList.toggle('selected', i < selected));
      });
    });
  });

});

async function confirmSession(id, btn) {
  btn.disabled = true;
  const json = await apiCall(`/api/sessions/${id}/confirm`, { method: 'PUT' });
  if (json.success) {
    showToast('success', 'Session confirmée !');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast('error', json.error);
    btn.disabled = false;
  }
}

async function declineSession(id, btn) {
  if (!confirm('Refuser cette session ?')) return;
  btn.disabled = true;
  const json = await apiCall(`/api/sessions/${id}/decline`, { method: 'PUT' });
  if (json.success) {
    showToast('success', 'Session refusée.');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast('error', json.error);
    btn.disabled = false;
  }
}

async function cancelSession(id, btn) {
  if (!confirm('Annuler cette session ?')) return;
  btn.disabled = true;
  const json = await apiCall(`/api/sessions/${id}/cancel`, { method: 'PUT' });
  if (json.success) {
    showToast('success', 'Session annulée.');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast('error', json.error);
    btn.disabled = false;
  }
}

async function completeSession(id, btn) {
  btn.disabled = true;
  const json = await apiCall(`/api/sessions/${id}/complete`, { method: 'PUT' });
  if (json.success) {
    showToast('success', 'Session marquée comme complète !');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast('error', json.error);
    btn.disabled = false;
  }
}

function showReviewForm(sessionId, btn) {
  const form = document.getElementById('review-' + sessionId);
  if (!form) return;
  const isVisible = form.style.display !== 'none';
  form.style.display = isVisible ? 'none' : 'flex';
  form.style.flexDirection = 'column';
}

async function submitReview(sessionId, btn) {
  const form  = document.getElementById('review-' + sessionId);
  const stars  = form.querySelector('.star-rating');
  const note   = parseInt(stars.dataset.selected || 5);
  const comment = form.querySelector('.review-textarea').value.trim();

  if (!note) {
    showToast('warning', 'Choisissez une note.');
    return;
  }

  btn.disabled = true;
  const json = await apiCall('/api/reviews', {
    method: 'POST',
    body: JSON.stringify({ session_id: sessionId, note, commentaire: comment }),
  });

  if (json.success) {
    showToast('success', 'Avis publié !');
    form.innerHTML = '<p style="color:var(--color-success);font-size:var(--text-sm);">✓ Avis envoyé</p>';
  } else {
    showToast('error', json.error);
    btn.disabled = false;
  }
}
