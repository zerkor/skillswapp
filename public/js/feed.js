/**
 * SkillSwap — Feed module
 * Chargement du feed, publication, likes, commentaires.
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

      // Prepend the new post
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
      avatar.alt = post.user.fullName || '';
    } else {
      avatar.style.display = 'none';
      placeholder.style.display = 'flex';
      const initials = ((post.user?.prenom || '')[0] || '') + ((post.user?.nom || '')[0] || '');
      placeholder.textContent = initials;
    }

    // Author link
    const authorName = card.querySelector('.post-author-name');
    authorName.textContent = post.user?.fullName || '';
    authorName.href        = `/profile/${post.user?.id}`;

    // Date
    const dateEl = card.querySelector('.post-date');
    dateEl.textContent = formatDate(post.createdAt);
    dateEl.setAttribute('datetime', post.createdAt || '');

    // Content
    card.querySelector('.post-content').textContent = post.contenu;

    // Tag
    const tagEl = card.querySelector('.post-tag');
    if (post.competenceTag) {
      tagEl.textContent = '#' + post.competenceTag;
      tagEl.style.display = 'inline-flex';
    }

    // Like button
    const likeBtn   = card.querySelector('.post-like-btn');
    const likeCount = card.querySelector('.like-count');
    likeCount.textContent = post.likesCount || 0;
    if (post.isLiked) likeBtn.classList.add('is-liked');

    likeBtn.addEventListener('click', async () => {
      const json = await apiCall(`/api/posts/${post.id}/like`, { method: 'POST' });
      if (json.success) {
        likeCount.textContent = json.data.likesCount;
        likeBtn.classList.toggle('is-liked', json.data.liked);
      }
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
        const el = document.createElement('div');
        el.className = 'comment-item';
        el.innerHTML = `
          <div class="avatar avatar-sm avatar-placeholder">${((c.user?.prenom || '')[0] || '') + ((c.user?.nom || '')[0] || '')}</div>
          <div class="comment-body">
            <div class="comment-author">${c.user?.fullName || ''}</div>
            <div class="comment-text">${escapeHtml(c.contenu)}</div>
          </div>`;
        commentsList.appendChild(el);
        const cnt = parseInt(commentCount.textContent) + 1;
        commentCount.textContent = cnt;
      } else {
        showToast('error', json.error);
      }
    });

    // Delete button (only visible for own posts — server enforces this)
    const deleteBtn = card.querySelector('.post-delete-btn');
    // Show delete button if current user owns the post
    // We check via data from JWT payload if available
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
