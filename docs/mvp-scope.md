# Scope MVP SkillSwap
**Version 1.1 — Comité client 02/06/2026**

---

## ✅ MVP — Fonctionnalités incluses

| Fonctionnalité | Statut | Priorité formateur |
|----------------|--------|-------------------|
| Inscription / authentification (email + pseudo anonyme) | ✅ Livré | Critique |
| Profil étudiant (compétences *savoir-faire*, formation séparée, disponibilités, pseudo) | ✅ Livré | Critique |
| **Système de matching** (algorithme scoring 3 critères : compétence 50% + créneaux 30% + réputation 20%) | ✅ Livré | **PRIORITÉ ABSOLUE** |
| Recherche par compétence avec filtres (niveau, disponibilité) | ✅ Livré | Critique |
| Gestion des sessions (proposer, confirmer, refuser, compléter, évaluer) | ✅ Livré | Critique |
| Système d'avis et notation des tuteurs | ✅ Livré | Critique |
| Points, badges (avec tooltips desktop + modal mobile), niveaux (Novice → Légende) | ✅ Livré | Important |
| Feed communautaire (posts, réactions 4 types, commentaires, pseudo affiché) | ✅ Livré | Important |
| Tableau de bord admin (3 KPIs : inscriptions, utilisation, mises en relation) | ✅ Livré | Important |
| Pseudonymes / anonymat utilisateurs (vrai nom privé) | ✅ Livré | Validé formateur |
| Séparation compétences / formations académiques | ✅ Livré | Correctif formateur |
| Sécurité (JWT, bcrypt cost 12, CSRF, XSS, headers sécurité, rate limiting) | ✅ Livré | Critique |
| Conformité RGPD (email non affiché publiquement, pseudonyme) | ✅ Livré | Obligatoire |

### Parcours utilisateur MVP validés

**Parcours 1 — Apprendre une compétence**
1. Recherche d'une compétence → `/search`
2. Liste des tuteurs avec score de compatibilité visible
3. Consultation du profil complet du tuteur
4. Proposition de session → `/sessions/new?tuteur=X`
5. Suivi et évaluation

**Parcours 2 — Enseigner une compétence**
1. Création/édition du profil avec compétences teach → `/profile/edit`
2. Ajout des disponibilités hebdomadaires
3. Réception des demandes → `/sessions`
4. Validation ou refus de la session
5. Réalisation et avis

---

## ⏸ Post-MVP — Fonctionnalités reportées

| Fonctionnalité | Raison du report | Phase |
|----------------|-----------------|-------|
| Application mobile React Native | Architecture API-first prête — développement Phase 2 | Phase 2 |
| Challenges saisonniers et défis hebdomadaires | Non prioritaire formateur | Phase 2 |
| Cartes cadeaux et récompenses physiques | Nécessite partenariat commercial | Phase 2 |
| Classements complexes et statistiques avancées | Leaderboard simple conservé | Phase 2 |
| Notifications push | Nécessite service tiers | Phase 2 |
| Mode hors-ligne | PWA Phase 2 | Phase 2 |
| Intégration Google Calendar | API tierce complexe | Phase 2 |
| Système de paiement / monétisation | Modèle économique à définir | Phase 3 |
| Concours entre utilisateurs | Non prioritaire MVP | Phase 2 |

---

## 📊 KPIs d'évaluation formateur

> Le projet sera évalué sur 3 métriques précises :

1. **Nombre d'inscriptions** → `/admin` → KPI 1
2. **Niveau d'utilisation** → `/admin` → KPI 2 (sessions actives, posts)
3. **Nombre de mises en relation via le matching** → `/admin` → KPI 3 ⭐ **PRIORITÉ**
