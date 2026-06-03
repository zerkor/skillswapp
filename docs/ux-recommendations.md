# Recommandations UX — SkillSwap
**Pour la présentation client — Comité du 02/06/2026**

---

## Objectif UX
> L'utilisateur doit comprendre ce qu'est SkillSwap et savoir quoi faire en **moins de 30 secondes**.

---

## 5 Améliorations UX Implémentées

### 1. Hero page d'accueil — Compréhension < 10s

**Avant** : Texte long, multiples CTA, manque de hiérarchie visuelle
**Après** : 
- H1 clair : *"Échange tes compétences avec tes pairs"*
- Tagline 1 phrase : *"SkillSwap connecte les étudiants qui veulent apprendre avec ceux qui savent."*
- 2 CTA max : `S'inscrire gratuitement` (primary) + `Découvrir les profils` (secondary)
- Cartes flottantes décoratives montrant la valeur immédiatement

**Métriques visées** : Temps de compréhension < 10s, taux de clic CTA > 15%

---

### 2. Page de recherche — Résultats sans scroll desktop

**Avant** : Filtres cachés, barre centrée mais résultats invisibles above the fold
**Après** :
- Hero compact avec barre de recherche proéminente
- Filtres inline (niveau + disponibilité) visibles directement
- Résultats en grille 3 colonnes → visibles dès le premier résultat
- Score de compatibilité en anneau circulaire coloré (vert/orange/rouge) → lecture instantanée
- Tooltip sur le score : décomposition (compétence / créneaux / réputation)

**Métriques visées** : Recherche en < 2 clics, résultat visible sans scroll sur 1280px+

---

### 3. Profil tuteur — CTA "Proposer une session" dominant

**Avant** : Bouton parmi d'autres, même taille que les liens secondaires
**Après** :
- Bouton `btn-primary btn-lg` avec icône calendrier
- Positionné en haut à droite du header profil
- Reste visible même en scroll (position sticky sur mobile)

**Métriques visées** : Taux de clic sur "Proposer session" > 20% des visites profil

---

### 4. Formulaire d'inscription — 3 étapes max, progression visible

**Avant** : Formulaire monolithique décourageant
**Après** :
- 3 étapes : Compte → Profil → Compétences
- Barre de progression visuelle avec états (actif / complété / futur)
- Max 4 champs par étape
- Validation en temps réel (force mot de passe, unicité pseudo)
- Message d'erreur immédiat et précis

**Métriques visées** : Taux de complétion inscription > 70%

---

### 5. Anonymat et confiance — Pseudonymes partout

**Avant** : Vrai nom affiché publiquement → frein à l'inscription pour certains étudiants
**Après** :
- Pseudo choisi à l'inscription (@AliceM, @CodingWizard...)
- Vrai nom visible uniquement dans les sessions confirmées entre les 2 participants
- Email jamais affiché
- Indicateur "compte vérifié" pour la confiance

**Métriques visées** : Hausse du taux d'inscription, réduction du taux d'abandon à l'étape profil

---

## Parcours de compréhension testé (30s rule)

```
0-5s  : Logo + tagline → "Ah, c'est un réseau d'échange de compétences entre étudiants"
5-15s : Hero → "Je peux apprendre Python en trouvant quelqu'un qui enseigne ça"
15-25s : Stats + features → "Beaucoup d'inscrits, des sessions, des badges"
25-30s : CTA → "Je clique sur S'inscrire"
```

---

## Règles UX appliquées

| Règle | Application |
|-------|-------------|
| 1 CTA primaire par page | ✅ Chaque page a un bouton principal évident |
| Feedback immédiat | ✅ Toasts, alerts, animations de confirmation |
| Hiérarchie visuelle claire | ✅ H1 > H2 > body, couleur primary sur actions clés |
| Mobile-first | ✅ Breakpoints 640/768/1024/1200px |
| Accessibilité | ✅ ARIA labels, focus visible, skip-link |
| Micro-animations | ✅ Hover cards (-2px), boutons (translateY(-1px)), réactions (scale 1.05) |

---

## Métriques UX cibles (prochaine itération)

| Métrique | Cible |
|----------|-------|
| Temps de compréhension page accueil | < 30s |
| Taux de complétion inscription | > 70% |
| Temps jusqu'à 1ère recherche | < 2 min après inscription |
| Taux de conversion profil → session proposée | > 10% |
| Score SUS (System Usability Scale) | > 75/100 |
