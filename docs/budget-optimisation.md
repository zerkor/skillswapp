# Rapport d'optimisation budgétaire — SkillSwap
**Présenté lors du comité client du 02/06/2026**

---

## Stratégie commerciale proposée

> Nous accordons une **remise de 15%** sur le développement de la webapp Phase 1 en échange d'un engagement du client sur le développement de l'**application mobile Phase 2**, confié à notre équipe.

Cette approche est mutuellement bénéfique :
- **Client** : économie immédiate de 2 000 € environ sur la Phase 1
- **Équipe** : engagement sur la Phase 2 (valeur estimée 18 000–22 000 €)

---

## Analyse par fonctionnalité

| Fonctionnalité | Estimation | Jours/dév | Importance MVP | Reportable ? | Gain potentiel |
|----------------|-----------|-----------|----------------|--------------|----------------|
| **Matching + recherche** | 2 800 € | 4j | ⭐ Critique | **Non** | 0 € |
| Authentification + profils + pseudo | 2 100 € | 3j | Critique | Non | 0 € |
| Gestion sessions (CRUD + statuts) | 1 750 € | 2.5j | Critique | Non | 0 € |
| Feed communautaire + réactions | 1 400 € | 2j | Important | Non | 0 € |
| Badges + niveaux + tooltips | 700 € | 1j | Important | Non | 0 € |
| Tableau de bord admin (3 KPIs) | 700 € | 1j | Important | Non | 0 € |
| Séparation compétences/diplômes (Education) | 350 € | 0.5j | Correctif | Non | 0 € |
| Sécurité (JWT, bcrypt, CSRF, XSS, RGPD) | 700 € | 1j | Obligatoire | Non | 0 € |
| Hébergement + déploiement VPS | 420 € | — | Infrastructure | Non | 0 € |
| **Challenges saisonniers** | 1 400 € | 2j | Optionnel | **Oui (Post-MVP)** | 1 400 € |
| **Classements complexes / stats avancées** | 700 € | 1j | Optionnel | **Oui (Post-MVP)** | 700 € |
| **Récompenses physiques** | 350 € | 0.5j | Optionnel | **Oui (Post-MVP)** | 350 € |
| **App mobile Phase 1 (ébauche)** | 1 750 € | 2.5j | Non (Phase 2) | **Oui** | 1 750 € |
| Design system + intégration CSS/JS | 700 € | 1j | Inclus | Non | 0 € |
| **TOTAL INITIAL** | **~15 820 €** | | | | |

---

## Budget révisé Phase 1

| Poste | Montant |
|-------|---------|
| Budget initial Phase 1 | 15 820 € |
| Fonctionnalités reportées (challenges + stats + récompenses) | **− 2 450 €** |
| App mobile reportée Phase 2 | **− 1 750 €** |
| Remise commerciale 15% (contre engagement Phase 2) | **− 1 743 €** |
| **Budget Phase 1 proposé** | **≈ 9 877 €** |

---

## Budget Phase 2 (application mobile — engagement futur)

| Fonctionnalité | Estimation |
|----------------|-----------|
| App React Native iOS + Android | 8 000 € |
| Challenges saisonniers | 1 400 € |
| Classements complexes | 700 € |
| Notifications push | 700 € |
| Intégrations Google Calendar | 700 € |
| Tests + déploiement stores | 1 500 € |
| **Total Phase 2** | **≈ 13 000 €** |

---

## Fonctionnalités non négociables (Phase 1)

- ✅ Sécurité des comptes (JWT, bcrypt, CSRF, XSS, headers HTTP)
- ✅ Conformité RGPD (pseudonymes, email non public)
- ✅ **Système de matching** ← priorité absolue formateur
- ✅ Intégration Back-End complète (API REST + Symfony 7)
- ✅ Gestion des profils étudiants avec pseudonymes
- ✅ Séparation compétences / diplômes
- ✅ Tableau de bord admin (3 KPIs formateur)

---

## Calendrier proposé

| Phase | Durée | Livraison |
|-------|-------|-----------|
| Phase 1 — MVP Web | 3 semaines | J+21 |
| Phase 1.1 — Correctifs comité client | 1 semaine | J+28 |
| Phase 2 — App mobile (sous réserve d'engagement) | 6-8 semaines | J+90 |
