# Fonctionnalités Post-MVP — SkillSwap Phase 2+

Ce document liste toutes les fonctionnalités dont le code est **conservé dans le projet** mais masqué dans l'interface (`display: none` / condition Twig `post-mvp-feature`). Elles seront déployées en Phase 2.

---

## 📱 Application Mobile (Phase 2 — priorité)

**Stack** : React Native (iOS + Android)
**Avantage** : l'architecture API-first est déjà en place — aucune modification backend nécessaire.

Fonctionnalités mobiles prévues :
- Push notifications (nouvelle demande de session, réponse)
- Mode hors-ligne avec synchronisation
- Géolocalisation pour sessions en présentiel
- Interface tactile optimisée

---

## 🏆 Gamification avancée

### Challenges saisonniers
- Défis hebdomadaires thématiques ("Semaine Python", "Semaine Design")
- Points bonus temporaires
- Tableau de progression spécial

### Concours
- Tournois entre utilisateurs
- Prix communautaires (titre, badge exclusif)
- Classements spéciaux par catégorie de compétence

### Récompenses physiques
- Système de points convertibles en avantages
- Partenariats avec librairies / plateformes e-learning
- Cartes cadeaux (Amazon, FNAC, etc.)

---

## 📊 Statistiques et Analytics avancés

- Dashboard utilisateur : temps moyen de session, progression par compétence
- Heatmap d'activité (GitHub-style)
- Graphiques d'évolution du score
- Export des données personnelles (RGPD)

---

## 📅 Intégrations externes

- **Google Calendar** : synchronisation automatique des sessions
- **Outlook** : rappels de session
- **Slack / Discord** : notifications de messagerie
- **LinkedIn** : import du profil académique

---

## 💳 Monétisation (Phase 3)

- Sessions premium payantes
- Abonnement établissement (école, université)
- API commerciale pour partenaires
- Système de pourboires optionnels entre étudiants

---

## 🔔 Notifications Push

- Nouvelle demande de session
- Confirmation / refus de session
- Nouveau message
- Badge obtenu
- Rappel de session à venir

---

## 🛡️ Modération avancée

- Signalement de profils / posts
- Système de vérification d'identité (email institutionnel obligatoire)
- Modération automatique du contenu (filtrage mots)
- Panel modérateur communautaire

---

## Notes techniques

Le code des fonctionnalités post-MVP est annoté avec `/* POST-MVP */` dans le CSS et masqué via la classe `.post-mvp-feature { display: none !important; }`. Pour activer une fonctionnalité, supprimer cette classe dans le template concerné.
