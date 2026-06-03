/**
 * SkillSwap — Search module
 * Recherche temps réel, affichage des matchs avec score de compatibilité.
 */

document.addEventListener('DOMContentLoaded', () => {
  const searchInput   = document.getElementById('searchInput');
  const searchBtn     = document.getElementById('searchBtn');
  const filterLevel   = document.getElementById('filterLevel');
  const filterAvail   = document.getElementById('filterAvailable');
  const resultsGrid   = document.getElementById('searchResults');
  const loader        = document.getElementById('searchLoader');
  const emptyState    = document.getElementById('searchEmpty');
  const initialState  = document.getElementById('searchInitial');
  const searchMeta    = document.getElementById('searchMeta');
  const searchCount   = document.getElementById('searchCount');
  const searchQuery   = document.getElementById('searchQuery');
  const clearBtn      = document.getElementById('clearSearch');
  const template      = document.getElementById('resultCardTemplate');

  if (!searchInput) return;

  let debounceTimer;

  // Charger tous les tuteurs au démarrage
  performSearch('');

  // Auto-search on input
  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const q = searchInput.value.trim();
    if (q.length >= 2) {
      debounceTimer = setTimeout(() => performSearch(q), 400);
    } else if (q.length === 0) {
      performSearch('');
    }
  });

  searchBtn?.addEventListener('click', () => {
    const q = searchInput.value.trim();
    if (q.length >= 1) performSearch(q);
  });

  searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      clearTimeout(debounceTimer);
      const q = searchInput.value.trim();
      if (q) performSearch(q);
    }
  });

  clearBtn?.addEventListener('click', () => {
    searchInput.value = '';
    performSearch('');
  });

  async function performSearch(q) {
    const level = filterLevel?.value || '0';
    const params = new URLSearchParams({ skill: q });
    if (level !== '0') params.set('level', level);

    showLoader();

    const json = await apiCall(`/api/matching?${params.toString()}`);

    if (!json.success) {
      showError();
      return;
    }

    renderResults(json.data, q);
  }

  function renderResults(matches, query) {
    resultsGrid.innerHTML = '';
    loader.style.display  = 'none';
    emptyState.style.display  = 'none';
    initialState.style.display = 'none';

    if (!matches || matches.length === 0) {
      emptyState.style.display  = 'block';
      searchMeta.style.display  = 'none';
      return;
    }

    // Update meta
    searchMeta.style.display = 'flex';
    searchCount.textContent  = `${matches.length} tuteur${matches.length > 1 ? 's' : ''}`;
    searchQuery.textContent  = query ? `pour "${query}"` : 'disponibles';

    matches.forEach(match => {
      const card = createResultCard(match);
      resultsGrid.appendChild(card);
    });
  }

  function createResultCard(match) {
    const tmpl  = template.content.cloneNode(true);
    const card  = tmpl.querySelector('.result-card');
    const user  = match.user;
    const score = match.score;
    const skill = match.matchedSkill;

    // Avatar
    if (user.photo) {
      const img = card.querySelector('.result-avatar');
      img.src = user.photo;
      img.alt = user.fullName || `${user.prenom} ${user.nom}`;
    } else {
      const img = card.querySelector('.result-avatar');
      img.style.display = 'none';
      const placeholder = card.querySelector('.result-avatar-placeholder');
      placeholder.style.display = 'flex';
      placeholder.textContent = ((user.prenom || '')[0] || '') + ((user.nom || '')[0] || '');
    }

    // Score ring
    const ring       = card.querySelector('.result-score-ring');
    const ringFill   = card.querySelector('.score-ring-fill');
    const ringValue  = card.querySelector('.score-ring-value');
    ringValue.textContent  = score + '%';
    ringFill.style.strokeDasharray = `${score}, 100`;
    ring.setAttribute('aria-label', `Score de compatibilité : ${score}%`);

    const scoreClass = score >= 70 ? 'high' : score >= 40 ? 'medium' : 'low';
    ring.classList.add(`score-ring--${scoreClass}`);

    // Info
    card.querySelector('.result-name').textContent        = user.fullName || `${user.prenom} ${user.nom}`;
    card.querySelector('.result-formation').textContent   = user.formation || '';
    card.querySelector('.result-skill-tag').textContent   = skill.nom;

    const niveauEl = card.querySelector('.result-niveau');
    niveauEl.innerHTML = `<span class="badge-niveau ${user.niveau}">${capitalizeFirst(user.niveau)}</span>`;

    const slotsEl = card.querySelector('.result-slots');
    slotsEl.innerHTML = match.commonSlots > 0
      ? `<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> ${match.commonSlots} créneau${match.commonSlots > 1 ? 'x' : ''} commun${match.commonSlots > 1 ? 's' : ''}`
      : 'Aucun créneau commun';

    // Links
    card.querySelector('.result-profile-link').href  = `/profile/${user.id}`;
    card.querySelector('.result-session-link').href  = `/sessions/new?tuteur=${user.id}`;

    return tmpl;
  }

  function showLoader() {
    loader.style.display      = 'flex';
    resultsGrid.innerHTML     = '';
    emptyState.style.display  = 'none';
    initialState.style.display = 'none';
    searchMeta.style.display  = 'none';
  }

  function showInitial() {
    loader.style.display       = 'none';
    resultsGrid.innerHTML      = '';
    emptyState.style.display   = 'none';
    searchMeta.style.display   = 'none';
    initialState.style.display = 'block';
  }

  function showError() {
    loader.style.display      = 'none';
    emptyState.style.display  = 'block';
  }

  function capitalizeFirst(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
  }
});
