/**
 * SkillSwap — Feed module v1.1
 * Chargement du feed, publication, réactions 4 types, commentaires.
 * Affichage pseudo (@displayName) — pas le vrai nom.
 */

document.addEventListener('DOMContentLoaded', () => {
  const feedContainer  = document.getElementById('feedContainer');
  const feedLoader     = document.getElementById('feedLoader');
  const feedPagination = document.getElementById('feedPagination');
  const loadMoreBtn    = document.getElementById('loadMoreBtn');
  const submitPost     = document.getElementById('submitPost');
  const postContent    = document.getElementById('postContent');
  const postTag        = document.getElementById('postTag');
  const postCharCount  = document.getElementById('postCharCount');
  const template       = document.getElementById('postTemplate');

  if (!feedContainer || !template) return;

  let currentPage = 1;
  let totalPages  = 1;

  // Char counter
  postContent?.addEventListener('input', () => {
    const len = postContent.value.length;
    if (postCharCount) {
      postCharCount.textContent = `${len}/2000`;
      postCharCount.style.color = len > 1800 ? 'var(--color-error)' : '';
    }
  });

  // Submit post
  submitPost?.addEventListener('click', async () => {
    const contenu = postContent?.value.trim();
    if (!contenu) {
      showToast('warning', 'Le contenu ne peut pas être vide.');
      return;
    }

    submitPost.disabled = true;

    const json = await apiCall('/api/posts', {
      method: 'POST',
      body: JSON.stringify({
        contenu,
        competence_tag: postTag?.value.trim().replace('#', '') || null,
      }),
    });

    submitPost.disabled = false;

    if (json.success) {
      if (postContent) postContent.value = '';
      if (postTag) postTag.value = '';
      if (postCharCount) postCharCount.textContent = '0/2000';

      const el = buildPostCard(json.data);
      const loader = feedContainer.querySelector('.feed-loader');
      if (loader) {
        feedContainer.insertBefore(el, loader.nextSibling);
      } else {
        feedContainer.prepend(el);
      }
      showToast('success', 'Post publié !');
    } else {
      showToast('error', json.error);
    }
  });

  // Load more
  loadMoreBtn?.addEventListener('click', async () => {
    if (currentPage < totalPages) {
      currentPage++;
      await loadFeed(currentPage, true);
    }
  });

  // Initial load
  loadFeed(1, false);

  async function loadFeed(page, append) {
    if (!append) {
      feedLoader.style.display = 'flex';
    } else {
      loadMoreBtn.textContent = 'Chargement…';
      loadMoreBtn.disabled    = true;
    }

    const json = await apiCall(`/api/feed?page=${page}`);

    feedLoader.style.display = 'none';

    if (!json.success) {
      showToast('error', 'Erreur lors du chargement du feed.');
      return;
    }

    const { posts, total, pages } = json.data;
    totalPages = pages;

    if (!append && posts.length === 0) {
      feedContainer.innerHTML = '<p style="text-align:center;color:var(--color-text-muted);padding:2rem;">Aucun post pour l\'instant. Soyez le premier à publier !</p>';
    }

    posts.forEach(post => {
      feedContainer.appendChild(buildPostCard(post));
    });

    if (currentPage < totalPages) {
      feedPagination.style.display = 'block';
      loadMoreBtn.textContent = 'Charger plus';
      loadMoreBtn.disabled    = false;
    } else {
      feedPagination.style.display = 'none';
    }
  }

  function buildPostCard(post) {
    const tmpl = template.content.cloneNode(true);
    const card = tmpl.querySelector('.post-card');

    card.dataset.postId = post.id;

    // Avatar
    const avatar      = card.querySelector('.post-avatar');
    const placeholder = card.querySelector('.post-avatar-placeholder');
    if (post.user?.photo) {
      avatar.src = post.user.photo;
      avatar.alt = '@' + (post.user.displayName || post.user.pseudo || post.user.prenom);
    } else {
      avatar.style.display = 'none';
      placeholder.style.display = 'flex';
      const initials = ((post.user?.prenom || '')[0] || '') + ((post.user?.nom || '')[0] || '');
      placeholder.textContent = initials;
    }

    // Auteur — afficher le PSEUDO (@displayName), jamais le vrai nom sur le feed
    const authorName = card.querySelector('.post-author-name');
    const displayName = post.user?.pseudo || post.user?.displayName || post.user?.prenom || 'Utilisateur';
    authorName.textContent = '@' + displayName;
    authorName.href        = `/profile/${post.user?.id}`;

    // Date relative
    const dateEl = card.querySelector('.post-date');
    dateEl.textContent = formatDate(post.createdAt);
    dateEl.setAttribute('datetime', post.createdAt || '');

    // Content
    card.querySelector('.post-content').textContent = post.contenu;

    // Tag compétence
    const tagEl = card.querySelector('.post-tag');
    if (post.competenceTag) {
      tagEl.textContent = '#' + post.competenceTag;
      tagEl.style.display = 'inline-flex';
    }

    // ── Réactions 4 types v1.1 ──
    const reactions = post.reactionCounts || { like: 0, heart: 0, idea: 0, celebrate: 0 };
    card.querySelectorAll('.reaction-btn').forEach(btn => {
      const type = btn.dataset.reaction;
      const countEl = btn.querySelector('.reaction-count');
      if (countEl) countEl.textContent = reactions[type] || 0;

      btn.addEventListener('click', async () => {
        const json = await apiCall(`/api/posts/${post.id}/react`, {
          method: 'POST',
          body: JSON.stringify({ type }),
        });
        if (json.success) {
          const newCounts = json.data.reactionCounts;
          card.querySelectorAll('.reaction-btn').forEach(b => {
            const t = b.dataset.reaction;
            const c = b.querySelector('.reaction-count');
            if (c) c.textContent = newCounts[t] || 0;
          });
          btn.classList.add('is-active');
          setTimeout(() => btn.classList.remove('is-active'), 600);
        }
      });
    });

    // Comments toggle
    const commentToggle = card.querySelector('.post-comment-toggle');
    const commentsEl    = card.querySelector('.post-comments');
    const commentCount  = card.querySelector('.comment-count');
    commentCount.textContent = post.commentsCount || 0;

    commentToggle.addEventListener('click', () => {
      const isOpen = commentsEl.style.display === 'block';
      commentsEl.style.display = isOpen ? 'none' : 'block';
      commentToggle.setAttribute('aria-expanded', !isOpen);
    });

    // Comment submit
    const commentInput  = card.querySelector('.comment-input');
    const commentSubmit = card.querySelector('.comment-submit');
    const commentsList  = card.querySelector('.comments-list');

    commentSubmit.addEventListener('click', async () => {
      const contenu = commentInput.value.trim();
      if (!contenu) return;

      const json = await apiCall(`/api/posts/${post.id}/comments`, {
        method: 'POST',
        body: JSON.stringify({ contenu }),
      });

      if (json.success) {
        commentInput.value = '';
        const c = json.data;
        const pseudoAuteur = c.user?.pseudo || c.user?.displayName || c.user?.prenom || '?';
        const el = document.createElement('div');
        el.className = 'comment-item';
        el.innerHTML = `
          <div class="avatar avatar-sm avatar-placeholder">${((c.user?.prenom || '')[0] || '')}${((c.user?.nom || '')[0] || '')}</div>
          <div class="comment-body">
            <div class="comment-author">@${escapeHtml(pseudoAuteur)}</div>
            <div class="comment-text">${escapeHtml(c.contenu)}</div>
          </div>`;
        commentsList.appendChild(el);
        const cnt = parseInt(commentCount.textContent) + 1;
        commentCount.textContent = cnt;
      } else {
        showToast('error', json.error);
      }
    });

    // Delete button
    const deleteBtn = card.querySelector('.post-delete-btn');
    const currentUserId = getCurrentUserId();
    if (currentUserId && post.user?.id === currentUserId) {
      deleteBtn.style.display = 'flex';
      deleteBtn.addEventListener('click', async () => {
        if (!confirm('Supprimer ce post ?')) return;
        const json = await apiCall(`/api/posts/${post.id}`, { method: 'DELETE' });
        if (json.success) {
          card.remove();
          showToast('success', 'Post supprimé.');
        } else {
          showToast('error', json.error);
        }
      });
    }

    return tmpl;
  }

  function getCurrentUserId() {
    const token = getToken();
    if (!token) return null;
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      return payload.id || null;
    } catch {
      return null;
    }
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
});
