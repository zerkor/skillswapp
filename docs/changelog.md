# Changelog — SkillSwap

## [1.1.0] — 02/06/2026
*Retours comité client — priorités formateur*

### Ajouté
- **Entité `Education`** : diplôme, établissement, niveau académique (bac → doctorat), année de promotion — séparé des compétences
- **Repository `EducationRepository`**
- **Champ `pseudo`** (VARCHAR 50, unique) sur l'entité `User` — pseudonyme public obligatoire
- **Méthode `getDisplayName()`** sur `User` — retourne le pseudo ou prénom en fallback
- **Tableau de bord admin** (`/admin`) avec 3 KPIs formateur :
  - KPI 1 : Nombre d'inscriptions (total, vérifiés, 7j, 30j)
  - KPI 2 : Niveau d'utilisation (sessions actives, posts, avis)
  - KPI 3 : Mises en relation réalisées via le matching (⭐ priorité formateur)
- **`AdminController`** avec route `/admin` (ROLE_ADMIN requis)
- **Badges avec tooltips** : champ `condition_label` sur `Badge`, CSS `.badge-wrapper` / `.badge-tooltip` (hover desktop, modal mobile)
- **Réactions multi-types** sur les posts : 👍 ❤️ 💡 🎉 (stockées en JSON `reaction_counts`)
- **Endpoint `POST /api/posts/{id}/react`** avec validation du type
- **Migrations** :
  - `Version20260602000000` : colonne `pseudo` + table `education` + colonne `reaction_counts`
  - `Version20260602000001` : colonne `condition_label` sur `badge`
- **Méthode `getAverageRatingForUser()`** sur `ReviewRepository` — note moyenne réelle basée sur les avis (utilisée par le matching)
- **Score breakdown** dans les résultats matching (`scoreBreakdown: {skill, slots, reputation}`)
- **Champ pseudo** au formulaire d'inscription (étape 1, validé côté client)
- **Fichiers docs/** : `mvp-scope.md`, `post-mvp.md`, `budget-optimisation.md`, `ux-recommendations.md`, `changelog.md`
- **Classe CSS `.post-mvp-feature`** : masque les éléments post-MVP avec `display: none !important`
- **Lien Admin** dans la navigation (visible ROLE_ADMIN uniquement)
- **Bannière critères formateur** dans le dashboard admin

### Modifié
- **`MatchingService`** : refactorisé pour utiliser la **note moyenne réelle des avis** (ReviewRepository) à la place du score brut normalisé — algorithme plus précis et équitable pour les nouveaux tuteurs (score neutre 0.5 si aucun avis)
- **Séparation compétences / formations** : les compétences = savoir-faire concrets uniquement (Python, Figma, Excel…), les diplômes sont dans `Education`
- **Affichage pseudo partout** : feed, recherche, leaderboard, nav → `@displayName` au lieu du vrai nom
- **Feed** : réactions 4 types remplacent le simple like, CSS `.reactions-bar` / `.reaction-btn`
- **feed.js** : gestion réactions multi-types, pseudo dans l'auteur et les commentaires
- **search.js** : affichage pseudo, tooltip score breakdown
- **profile/show.html.twig** : section "Parcours académique" séparée, badges avec tooltips + modal mobile, CTA "Proposer une session" promu en btn-primary btn-lg
- **leaderboard** : Top 10 simple (MVP), post-MVP masqué, affichage pseudo
- **base.html.twig** : chargement `badges.css`, lien admin conditionnel, `@displayName` dans la nav
- **`UserRepository`** : ajout `countVerified()`, `countLastDays()`
- **`SessionRepository`** : ajout `countMatchingMadeConnections()`, `countActive()`, `countLastDays()`
- **Fixtures v1.1** : 10 utilisateurs avec pseudos, formations dans `Education`, compétences corrigées (aucun diplôme dans les skills), badges avec `condition_label`, 7 badges distincts

### Corrigé
- **Données fixtures** : suppression des diplômes dans les compétences (ex: "Master Informatique" → déplacé dans `Education`)
- **Filtres de recherche** : ne cherchent que dans les vraies compétences (type teach)
- **Algorithme matching** : réputation basée sur les avis réels, pas sur le score de points (plus juste)

### Reporté (Post-MVP — code conservé, masqué)
- Challenges saisonniers et défis hebdomadaires
- Cartes cadeaux et récompenses physiques
- Classements complexes et statistiques avancées
- Concours entre utilisateurs
- Application mobile React Native (Phase 2)

---

## [1.0.0] — 01/06/2026
*Version initiale*

### Ajouté
- Architecture Symfony 7 + API-first (controllers Web + API séparés)
- Entités : User, Skill, Availability, Session, Review, Badge, UserBadge, Post, Comment
- Authentification JWT (lexik/jwt-authentication-bundle) + sessions Symfony
- Algorithme de matching (compétence 50% + créneaux 30% + réputation 20%)
- 8 modules API REST (auth, users, skills, matching, sessions, reviews, gamification, feed)
- 6 pages web Twig : Home, Login, Register, Profile, Search, Sessions, Feed, Leaderboard
- Design system complet (Plus Jakarta Sans + Inter, palette bleu/cyan)
- CSS modulaire (variables, reset, components, layout, pages/)
- JavaScript vanilla (api.js, auth.js, search.js, profile.js, session.js, feed.js)
- Gamification : points, niveaux (Novice→Légende), badges, classement
- Fixtures avec 10 utilisateurs, sessions, posts, badges de démonstration
- Migration initiale complète
- README avec doc API, guide déploiement VPS, guide Git
