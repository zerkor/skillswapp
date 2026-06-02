<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Availability;
use App\Entity\Badge;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Review;
use App\Entity\Session;
use App\Entity\Skill;
use App\Entity\User;
use App\Entity\UserBadge;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures de démonstration — 20 utilisateurs, sessions, posts, commentaires, badges.
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $badges = $this->createBadges($manager);
        $users  = $this->createUsers($manager);
        $posts  = $this->createPosts($manager, $users);
        $this->createComments($manager, $users, $posts);
        $this->createSessions($manager, $users);
        $this->assignBadges($manager, $users, $badges);

        $manager->flush();
    }

    /** @return Badge[] */
    private function createBadges(ObjectManager $manager): array
    {
        $data = [
            ['nom' => 'Premier pas',       'description' => 'Complétez votre première session',          'icone' => '👣', 'conditionType' => 'first_session',           'conditionValue' => 1],
            ['nom' => 'Mentor confirmé',   'description' => '5 sessions en tant que tuteur',             'icone' => '🎓', 'conditionType' => 'sessions_completed_tutor', 'conditionValue' => 5],
            ['nom' => 'Fondateur',         'description' => 'Parmi les 50 premiers inscrits',            'icone' => '🏛️', 'conditionType' => 'registered_early',          'conditionValue' => 1],
            ['nom' => 'Expert partage',    'description' => '10 sessions d\'enseignement complétées',   'icone' => '🔬', 'conditionType' => 'sessions_completed_tutor', 'conditionValue' => 10],
            ['nom' => 'Fidèle',            'description' => '30 jours d\'activité sur la plateforme',   'icone' => '🌟', 'conditionType' => 'registered_early',          'conditionValue' => 1],
            ['nom' => 'Curieux',           'description' => 'Participer à 3 types de sessions différents', 'icone' => '🔍', 'conditionType' => 'first_session',         'conditionValue' => 3],
            ['nom' => 'Influenceur',       'description' => 'Obtenir 50 likes sur vos posts',           'icone' => '🚀', 'conditionType' => 'registered_early',          'conditionValue' => 1],
            ['nom' => 'Top Évaluateur',    'description' => 'Rédiger 10 avis détaillés',               'icone' => '⭐', 'conditionType' => 'first_session',             'conditionValue' => 10],
        ];

        $badges = [];
        foreach ($data as $d) {
            $badge = new Badge();
            $badge->setNom($d['nom'])
                  ->setDescription($d['description'])
                  ->setIcone($d['icone'])
                  ->setConditionType($d['conditionType'])
                  ->setConditionValue($d['conditionValue']);
            $manager->persist($badge);
            $badges[] = $badge;
        }

        return $badges;
    }

    /** @return User[] */
    private function createUsers(ObjectManager $manager): array
    {
        $usersData = [
            // --- Utilisateurs originaux ---
            ['prenom' => 'Alice',    'nom' => 'Martin',    'email' => 'alice@skillswap.fr',    'formation' => 'Master Informatique',   'promotion' => '2025', 'score' => 850,  'niveau' => 'expert',
             'bio' => 'Passionnée de Python et machine learning. Je propose des cours adaptés à tous niveaux.',
             'skills' => [['nom' => 'Python', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'], ['nom' => 'Machine Learning', 'type' => 'teach', 'niveau' => 3, 'cat' => 'IA'], ['nom' => 'React', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['lun', '09:00', '11:00'], ['mer', '14:00', '17:00'], ['sam', '10:00', '12:00']]],

            ['prenom' => 'Baptiste', 'nom' => 'Durand',   'email' => 'baptiste@skillswap.fr', 'formation' => 'Licence Design',        'promotion' => '2026', 'score' => 320,  'niveau' => 'mentor',
             'bio' => 'Designer UI/UX depuis 3 ans. Figma et Adobe sont mes outils du quotidien.',
             'skills' => [['nom' => 'Figma', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Design'], ['nom' => 'Photoshop', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mar', '13:00', '15:00'], ['jeu', '10:00', '12:00']]],

            ['prenom' => 'Clara',    'nom' => 'Lefebvre',  'email' => 'clara@skillswap.fr',    'formation' => 'DUT Réseaux',           'promotion' => '2025', 'score' => 1650, 'niveau' => 'legende',
             'bio' => 'Admin sys et réseau. Certifiée Cisco CCNA. Adepte du libre.',
             'skills' => [['nom' => 'Linux', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Systèmes'], ['nom' => 'Cisco', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Réseaux'], ['nom' => 'Docker', 'type' => 'teach', 'niveau' => 3, 'cat' => 'DevOps'], ['nom' => 'Kubernetes', 'type' => 'learn', 'niveau' => 2, 'cat' => 'DevOps']],
             'avails' => [['lun', '18:00', '20:00'], ['mer', '18:00', '20:00'], ['ven', '14:00', '16:00']]],

            ['prenom' => 'David',    'nom' => 'Bernard',   'email' => 'david@skillswap.fr',    'formation' => 'Master Finance',        'promotion' => '2025', 'score' => 145,  'niveau' => 'apprenti',
             'bio' => 'Passionné d\'économie et de finance. Je cherche à renforcer mes compétences tech.',
             'skills' => [['nom' => 'Excel', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Bureautique'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'SQL', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Base de données']],
             'avails' => [['mar', '12:00', '14:00'], ['jeu', '16:00', '18:00']]],

            ['prenom' => 'Emma',     'nom' => 'Petit',     'email' => 'emma@skillswap.fr',     'formation' => 'Licence Anglais',       'promotion' => '2026', 'score' => 210,  'niveau' => 'mentor',
             'bio' => 'Bilingue anglais-français. Cours de conversation et rédaction académique.',
             'skills' => [['nom' => 'Anglais', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Espagnol', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Langues'], ['nom' => 'Photoshop', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Design']],
             'avails' => [['lun', '14:00', '16:00'], ['mer', '09:00', '11:00'], ['sam', '14:00', '16:00']]],

            ['prenom' => 'Florian',  'nom' => 'Thomas',    'email' => 'florian@skillswap.fr',  'formation' => 'Master Dev Web',        'promotion' => '2025', 'score' => 560,  'niveau' => 'mentor',
             'bio' => 'Full-stack JS. Je maîtrise React, Node.js, et les bases SQL/NoSQL.',
             'skills' => [['nom' => 'React', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Web'], ['nom' => 'Node.js', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Web'], ['nom' => 'Docker', 'type' => 'learn', 'niveau' => 2, 'cat' => 'DevOps']],
             'avails' => [['lun', '19:00', '21:00'], ['ven', '10:00', '12:00']]],

            ['prenom' => 'Gaëlle',   'nom' => 'Roux',      'email' => 'gaelle@skillswap.fr',   'formation' => 'BTS Comptabilité',      'promotion' => '2026', 'score' => 45,   'niveau' => 'novice',
             'bio' => 'Débutante en informatique. Je veux apprendre à coder !',
             'skills' => [['nom' => 'Comptabilité', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Gestion'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'Excel', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Bureautique']],
             'avails' => [['mar', '17:00', '19:00'], ['dim', '10:00', '12:00']]],

            ['prenom' => 'Hugo',     'nom' => 'Moreau',    'email' => 'hugo@skillswap.fr',     'formation' => 'Licence Maths',         'promotion' => '2025', 'score' => 400,  'niveau' => 'mentor',
             'bio' => 'Matheux passionné. Spécialiste en algèbre linéaire et statistiques.',
             'skills' => [['nom' => 'Mathématiques', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'Statistiques', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'R', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Programmation'], ['nom' => 'React', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['mer', '10:00', '12:00'], ['sam', '09:00', '11:00']]],

            ['prenom' => 'Inès',     'nom' => 'Simon',     'email' => 'ines@skillswap.fr',     'formation' => 'Master Cybersécurité',  'promotion' => '2025', 'score' => 720,  'niveau' => 'expert',
             'bio' => 'Passionnée de sécu, CTF et pentesting. Certifiée CEH.',
             'skills' => [['nom' => 'Cybersécurité', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sécurité'], ['nom' => 'Kali Linux', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Sécurité'], ['nom' => 'SQL', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Base de données']],
             'avails' => [['jeu', '20:00', '22:00'], ['sam', '15:00', '17:00']]],

            ['prenom' => 'Julien',   'nom' => 'Lemaire',   'email' => 'julien@skillswap.fr',   'formation' => 'DUT Génie Civil',       'promotion' => '2026', 'score' => 80,   'niveau' => 'novice',
             'bio' => 'Futur ingénieur. Cherche à améliorer mon anglais et mes compétences en gestion de projet.',
             'skills' => [['nom' => 'AutoCAD', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Ingénierie'], ['nom' => 'Anglais', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Langues'], ['nom' => 'Gestion de projet', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Management']],
             'avails' => [['lun', '12:00', '14:00'], ['mer', '12:00', '14:00']]],

            // --- 10 nouveaux utilisateurs ---
            ['prenom' => 'Karim',    'nom' => 'Benali',    'email' => 'karim@skillswap.fr',    'formation' => 'Master IA',             'promotion' => '2025', 'score' => 930,  'niveau' => 'expert',
             'bio' => 'Data scientist chez une startup. Je travaille sur des modèles NLP et vision par ordinateur.',
             'skills' => [['nom' => 'TensorFlow', 'type' => 'teach', 'niveau' => 4, 'cat' => 'IA'], ['nom' => 'PyTorch', 'type' => 'teach', 'niveau' => 4, 'cat' => 'IA'], ['nom' => 'SQL', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Base de données'], ['nom' => 'Kubernetes', 'type' => 'learn', 'niveau' => 1, 'cat' => 'DevOps']],
             'avails' => [['lun', '20:00', '22:00'], ['sam', '09:00', '11:00'], ['dim', '14:00', '16:00']]],

            ['prenom' => 'Léa',      'nom' => 'Fontaine',  'email' => 'lea@skillswap.fr',      'formation' => 'BTS Communication',    'promotion' => '2026', 'score' => 175,  'niveau' => 'apprenti',
             'bio' => 'Passionnée de marketing digital et réseaux sociaux. J\'apprends le HTML/CSS.',
             'skills' => [['nom' => 'Marketing digital', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Marketing'], ['nom' => 'Canva', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design'], ['nom' => 'HTML/CSS', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['mar', '10:00', '12:00'], ['jeu', '14:00', '16:00'], ['sam', '11:00', '13:00']]],

            ['prenom' => 'Marc',     'nom' => 'Vasseur',   'email' => 'marc@skillswap.fr',     'formation' => 'Licence Philosophie',  'promotion' => '2025', 'score' => 290,  'niveau' => 'mentor',
             'bio' => 'Prof de philo reconverti dans la data. J\'adore vulgariser les concepts complexes.',
             'skills' => [['nom' => 'Prise de parole', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Communication'], ['nom' => 'Rédaction', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Communication'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Programmation']],
             'avails' => [['mer', '15:00', '17:00'], ['ven', '09:00', '11:00']]],

            ['prenom' => 'Nadia',    'nom' => 'Cherif',    'email' => 'nadia@skillswap.fr',    'formation' => 'Master Droit',         'promotion' => '2025', 'score' => 480,  'niveau' => 'mentor',
             'bio' => 'Juriste spécialisée en droit du numérique. RGPD et propriété intellectuelle.',
             'skills' => [['nom' => 'Droit numérique', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Droit'], ['nom' => 'RGPD', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Droit'], ['nom' => 'Excel', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Bureautique']],
             'avails' => [['lun', '13:00', '15:00'], ['jeu', '18:00', '20:00']]],

            ['prenom' => 'Oscar',    'nom' => 'Girard',    'email' => 'oscar@skillswap.fr',    'formation' => 'DUT Informatique',     'promotion' => '2026', 'score' => 60,   'niveau' => 'novice',
             'bio' => 'Étudiant en informatique, premier semestre. Je cherche de l\'aide sur les bases.',
             'skills' => [['nom' => 'HTML/CSS', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Web'], ['nom' => 'Java', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'Algorithmique', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mar', '08:00', '10:00'], ['ven', '14:00', '16:00']]],

            ['prenom' => 'Pauline',  'nom' => 'Marchand',  'email' => 'pauline@skillswap.fr',  'formation' => 'Master Marketing',     'promotion' => '2025', 'score' => 615,  'niveau' => 'expert',
             'bio' => 'Growth hacker et experte SEO. J\'aide les startups à scaler leur acquisition.',
             'skills' => [['nom' => 'SEO', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Marketing'], ['nom' => 'Google Analytics', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Marketing'], ['nom' => 'SQL', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Base de données']],
             'avails' => [['lun', '10:00', '12:00'], ['mer', '10:00', '12:00'], ['ven', '16:00', '18:00']]],

            ['prenom' => 'Quentin',  'nom' => 'Lacombe',   'email' => 'quentin@skillswap.fr',  'formation' => 'Bachelor Game Design', 'promotion' => '2026', 'score' => 135,  'niveau' => 'apprenti',
             'bio' => 'Passionné de jeux vidéo et de programmation. J\'apprends Unity et C#.',
             'skills' => [['nom' => 'Unity', 'type' => 'teach', 'niveau' => 2, 'cat' => 'GameDev'], ['nom' => 'C#', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Programmation'], ['nom' => 'Blender', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design 3D']],
             'avails' => [['mar', '20:00', '22:00'], ['dim', '15:00', '17:00']]],

            ['prenom' => 'Rania',    'nom' => 'El Amrani', 'email' => 'rania@skillswap.fr',    'formation' => 'Master Traduction',    'promotion' => '2025', 'score' => 345,  'niveau' => 'mentor',
             'bio' => 'Traductrice français-arabe-anglais. Je propose des cours d\'arabe littéraire.',
             'skills' => [['nom' => 'Arabe', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Anglais', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mer', '16:00', '18:00'], ['sam', '13:00', '15:00'], ['dim', '10:00', '12:00']]],

            ['prenom' => 'Sophie',   'nom' => 'Renard',    'email' => 'sophie@skillswap.fr',   'formation' => 'Master Biologie',      'promotion' => '2025', 'score' => 510,  'niveau' => 'expert',
             'bio' => 'Chercheuse en bioinformatique. Je travaille sur l\'analyse de séquences ADN.',
             'skills' => [['nom' => 'Bioinformatique', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'Python', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Programmation'], ['nom' => 'R', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'], ['nom' => 'Machine Learning', 'type' => 'learn', 'niveau' => 2, 'cat' => 'IA']],
             'avails' => [['lun', '17:00', '19:00'], ['jeu', '09:00', '11:00']]],

            ['prenom' => 'Thomas',   'nom' => 'Barbier',   'email' => 'thomas@skillswap.fr',   'formation' => 'Licence Économie',     'promotion' => '2026', 'score' => 190,  'niveau' => 'apprenti',
             'bio' => 'Étudiant en éco, fan de crypto et blockchain. Je cherche à approfondir mes bases en programmation.',
             'skills' => [['nom' => 'Blockchain', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Finance'], ['nom' => 'Économie', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Finance'], ['nom' => 'JavaScript', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Programmation']],
             'avails' => [['mar', '15:00', '17:00'], ['ven', '12:00', '14:00']]],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $user = new User();
            $user->setEmail($data['email'])
                 ->setNom($data['nom'])
                 ->setPrenom($data['prenom'])
                 ->setFormation($data['formation'])
                 ->setPromotion($data['promotion'])
                 ->setScore($data['score'])
                 ->setNiveau($data['niveau'])
                 ->setBio($data['bio'])
                 ->setIsVerified(true)
                 ->setPassword($this->passwordHasher->hashPassword(new User(), 'Password123!'));

            foreach ($data['skills'] as $sd) {
                $skill = new Skill();
                $skill->setNom($sd['nom'])
                      ->setType($sd['type'])
                      ->setNiveau($sd['niveau'])
                      ->setCategorie($sd['cat'])
                      ->setUser($user);
                $manager->persist($skill);
            }

            foreach ($data['avails'] as [$jour, $debut, $fin]) {
                $avail = new Availability();
                $avail->setJourSemaine($jour)
                      ->setHeureDebut(new \DateTimeImmutable($debut))
                      ->setHeureFin(new \DateTimeImmutable($fin))
                      ->setUser($user);
                $manager->persist($avail);
            }

            $manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    /** @return Post[] */
    private function createPosts(ObjectManager $manager, array $users): array
    {
        $postsData = [
            ['user' => 0,  'contenu' => "Venez de finir mon cours Python sur les décorateurs. Résultat : mon code est maintenant 3x plus lisible ! 🐍 Qui veut que je lui explique le concept ?", 'tag' => 'Python'],
            ['user' => 2,  'contenu' => "Docker + Compose = la combo parfaite pour isoler vos environnements de dev. Fini les \"ça marche chez moi\" ! J'organise un atelier la semaine prochaine.", 'tag' => 'Docker'],
            ['user' => 4,  'contenu' => "Petite astuce anglais : pour améliorer son accent, écoutez des podcasts en demi-vitesse sur Spotify. Ça change tout ! 🎧", 'tag' => 'Anglais'],
            ['user' => 5,  'contenu' => "React 19 est sorti ! Les Server Components changent vraiment la donne. Quelqu'un veut co-apprendre les nouvelles features ensemble ?", 'tag' => 'React'],
            ['user' => 7,  'contenu' => "Tip maths : pour visualiser les matrices, pensez à elles comme des transformations dans l'espace. Ça rend l'algèbre linéaire tellement plus intuitive !", 'tag' => 'Mathématiques'],
            ['user' => 8,  'contenu' => "CTF de la semaine résolu ! La faille était une injection SQL pas très bien cachée... Rappel : TOUJOURS utiliser des requêtes préparées 🔒", 'tag' => 'Cybersécurité'],
            ['user' => 1,  'contenu' => "Nouvelle ressource Figma : les variables de design sont enfin disponibles pour tous ! Le design system devient tellement plus maintenable.", 'tag' => 'Figma'],
            ['user' => 3,  'contenu' => "Première session SkillSwap terminée avec Alice sur Python. En 1h j'ai compris les list comprehensions. Merci la plateforme ! 🚀", 'tag' => 'Python'],
            ['user' => 6,  'contenu' => "Question pour les codeurs : vous utilisez quoi comme éditeur ? VSCode avec Vim keybindings ici, mais curieuse de découvrir autre chose.", 'tag' => null],
            ['user' => 9,  'contenu' => "AutoCAD tip : les blocs dynamiques permettent de créer des composants réutilisables. Indispensable pour les plans d'architecture !", 'tag' => 'AutoCAD'],
            ['user' => 10, 'contenu' => "PyTorch 2.0 c'est un game changer pour la perf des modèles. La compilation avec torch.compile() réduit le temps d'entraînement de 30% sur mes benchmarks.", 'tag' => 'Machine Learning'],
            ['user' => 11, 'contenu' => "Si vous ne connaissez pas Canva Pro, vous ratez quelque chose ! Créer des visuels pro en 10 minutes, c'est possible. Je peux vous montrer mes templates gratuits.", 'tag' => 'Canva'],
            ['user' => 12, 'contenu' => "La vulgarisation c'est un art. Expliquer le gradient descent comme une randonnée en montagne les yeux bandés : on descend à tâtons jusqu'au fond de la vallée 🏔️", 'tag' => 'Machine Learning'],
            ['user' => 13, 'contenu' => "Le RGPD fête ses 6 ans ! Et encore beaucoup d'entreprises ne sont pas conformes. Si vous traitez des données perso, je propose un audit gratuit de 30 min.", 'tag' => 'RGPD'],
            ['user' => 15, 'contenu' => "Core Web Vitals mis à jour par Google. Le LCP et le CLS impactent maintenant fortement le référencement. Mon guide complet pour optimiser votre score.", 'tag' => 'SEO'],
            ['user' => 16, 'contenu' => "Unity 2024 LTS est incroyable. Le nouveau système de particules GPU permet des effets visuels dingues. Je partage mon projet de démonstration open source.", 'tag' => 'Unity'],
            ['user' => 17, 'contenu' => "Cours d'arabe littéraire disponible ! L'arabe est la 5e langue la plus parlée au monde. Commencez avec l'alphabet en 3 sessions. 🌙", 'tag' => 'Arabe'],
            ['user' => 18, 'contenu' => "BLAST+ pour l'alignement de séquences protéiques, BioPython pour l'automatisation : mon pipeline d'analyse génomique tourne en 20min au lieu de 3h.", 'tag' => 'Bioinformatique'],
            ['user' => 19, 'contenu' => "Débat du jour : la blockchain va-t-elle vraiment révolutionner la finance ? Je prépare une session d'introduction pour démystifier le sujet.", 'tag' => 'Blockchain'],
            ['user' => 0,  'contenu' => "Nouveau tutoriel disponible : pandas profiling pour l'EDA automatique. En 3 lignes de code, générez un rapport complet sur vos données. Lien en commentaire !", 'tag' => 'Python'],
        ];

        $posts = [];
        foreach ($postsData as $pd) {
            $post = new Post();
            $post->setUser($users[$pd['user']])
                 ->setContenu($pd['contenu'])
                 ->setCompetenceTag($pd['tag']);

            $likers = array_rand($users, rand(2, 8));
            if (!is_array($likers)) $likers = [$likers];
            foreach ($likers as $li) {
                if ($li !== $pd['user']) {
                    $post->addLike($users[$li]);
                }
            }

            $manager->persist($post);
            $posts[] = $post;
        }

        return $posts;
    }

    private function createComments(ObjectManager $manager, array $users, array $posts): void
    {
        $commentsData = [
            // Post 0 — Python décorateurs (Alice)
            ['post' => 0, 'user' => 3,  'contenu' => 'Super article ! J\'ai justement un projet où j\'aurais besoin d\'un décorateur de cache. Tu peux m\'aider ?'],
            ['post' => 0, 'user' => 6,  'contenu' => 'J\'ai essayé les décorateurs hier soir, le concept de wrapper de fonction est vraiment élégant.'],
            ['post' => 0, 'user' => 19, 'contenu' => 'Ça ressemble au pattern Decorator en POO finalement ?'],
            ['post' => 0, 'user' => 10, 'contenu' => '@thomas Oui exactement ! C\'est la même idée appliquée à Python de façon pythonique.'],

            // Post 1 — Docker (Clara)
            ['post' => 1, 'user' => 5,  'contenu' => 'J\'utilise Docker depuis 2 ans et je ne retournerais plus en arrière. Compose c\'est la vie !'],
            ['post' => 1, 'user' => 14, 'contenu' => 'Tu peux faire un atelier sur docker swarm aussi ? Je galère avec le clustering.'],
            ['post' => 1, 'user' => 2,  'contenu' => 'Bien sûr Oscar ! Je prévois justement une session avancée sur l\'orchestration.'],
            ['post' => 1, 'user' => 7,  'contenu' => 'Interessant ! La différence Docker/Kubernetes m\'a toujours semblé floue, ce serait cool d\'avoir une session là-dessus.'],

            // Post 2 — Anglais (Emma)
            ['post' => 2, 'user' => 9,  'contenu' => 'Tip génial ! J\'avais jamais pensé à ralentir la vitesse des podcasts. Je teste ce soir avec BBC Learning English.'],
            ['post' => 2, 'user' => 17, 'contenu' => 'La même technique marche pour apprendre l\'arabe avec des émissions Al Jazeera !'],
            ['post' => 2, 'user' => 4,  'contenu' => 'Et pour l\'accent britannique vs américain, vous recommandez quoi comme podcasts spécifiques ?'],

            // Post 3 — React (Florian)
            ['post' => 3, 'user' => 0,  'contenu' => 'Oui ! Je cherche justement à apprendre React. On organise un groupe d\'étude ?'],
            ['post' => 3, 'user' => 7,  'contenu' => 'Je suis partant pour co-apprendre. J\'ai les bases JS mais jamais touché un framework.'],
            ['post' => 3, 'user' => 19, 'contenu' => 'Les Server Components c\'est vraiment complexe à comprendre. Une session dédiée serait top.'],
            ['post' => 3, 'user' => 11, 'contenu' => 'Question de débutante : quelle différence avec Vue.js ?'],

            // Post 4 — Maths (Hugo)
            ['post' => 4, 'user' => 0,  'contenu' => 'C\'est exactement la métaphore que j\'utilise pour expliquer les transformations linéaires à mes étudiants !'],
            ['post' => 4, 'user' => 18, 'contenu' => 'En bioinformatique on utilise beaucoup l\'algèbre linéaire pour l\'analyse en composantes principales. Tu fais des sessions sur le PCA ?'],
            ['post' => 4, 'user' => 10, 'contenu' => 'La décomposition SVD c\'est aussi très utile en NLP. Lien entre maths et IA !'],

            // Post 5 — Cybersécurité (Inès)
            ['post' => 5, 'user' => 2,  'contenu' => 'Les injections SQL c\'est encore trop fréquent dans les audits. Tu partages tes writeups de CTF quelque part ?'],
            ['post' => 5, 'user' => 3,  'contenu' => 'Moi qui faisais du SQL direct dans mes requêtes... Cette semaine je migre vers PDO préparé !'],
            ['post' => 5, 'user' => 13, 'contenu' => 'D\'un point de vue RGPD, une fuite due à une SQLi peut coûter jusqu\'à 4% du chiffre d\'affaires mondial. Prenez ça au sérieux !'],

            // Post 6 — Figma (Baptiste)
            ['post' => 6, 'user' => 11, 'contenu' => 'Les variables Figma c\'est révolutionnaire pour gérer les thèmes dark/light. Tu as une session prévue là-dessus ?'],
            ['post' => 6, 'user' => 4,  'contenu' => 'Je commence Figma cette semaine. Des ressources pour débutantes que tu recommandes ?'],
            ['post' => 6, 'user' => 1,  'contenu' => 'Je vais uploader un fichier de démarrage sur ma page. Design system minimaliste clé en main !'],

            // Post 7 — Python (David)
            ['post' => 7, 'user' => 0,  'contenu' => 'Avec plaisir David ! Tu es motivé, ça se voit. N\'hésite pas à réserver une deuxième session.'],
            ['post' => 7, 'user' => 6,  'contenu' => 'Moi aussi j\'ai fait une session avec Alice et les list comprehensions m\'ont ouvert les yeux !'],
            ['post' => 7, 'user' => 10, 'contenu' => 'La plateforme est vraiment pratique pour trouver quelqu\'un qui connaît exactement ce qu\'on cherche à apprendre.'],

            // Post 8 — Éditeur de code (Gaëlle)
            ['post' => 8, 'user' => 0,  'contenu' => 'VSCode avec Python extension, Pylance, et Black formatter. Impossible de revenir en arrière !'],
            ['post' => 8, 'user' => 2,  'contenu' => 'Neovim all the way. Oui je suis cliché mais une fois configuré c\'est imbattable.'],
            ['post' => 8, 'user' => 5,  'contenu' => 'WebStorm pour le JS, VSCode pour tout le reste. Les deux valent le coup selon le projet.'],
            ['post' => 8, 'user' => 8,  'contenu' => 'Vim sur terminal dans la VM. Quand on fait du pentest on n\'a pas toujours accès à une belle interface graphique !'],

            // Post 9 — AutoCAD (Julien)
            ['post' => 9, 'user' => 2,  'contenu' => 'Tu as des équivalents libres à proposer ? Je pense à FreeCAD ou LibreCAD pour les étudiants qui n\'ont pas les licences.'],
            ['post' => 9, 'user' => 12, 'contenu' => 'En philosophie on dirait que la forme suit la fonction. Idem en architecture ! Ton tip est très pratique.'],

            // Post 10 — PyTorch (Karim)
            ['post' => 10, 'user' => 0,  'contenu' => 'Tu as testé avec des modèles de vision ? Je travaille sur de la détection d\'objets et j\'aimerais voir tes chiffres.'],
            ['post' => 10, 'user' => 18, 'contenu' => 'Est-ce que ça marche aussi bien sur des séquences biologiques ? J\'ai un projet de prédiction de structure de protéines.'],
            ['post' => 10, 'user' => 10, 'contenu' => 'La prochaine milestone c\'est torch.export() pour le déploiement. T\'as regardé ça ?'],

            // Post 11 — Canva (Léa)
            ['post' => 11, 'user' => 1,  'contenu' => 'Canva c\'est bien pour débuter mais pour les projets sérieux on perd vite en flexibilité par rapport à Figma ou Illustrator.'],
            ['post' => 11, 'user' => 15, 'contenu' => 'Pour le marketing de contenu Canva est imbattable en efficacité. Je l\'utilise pour tous mes contenus réseaux sociaux !'],
            ['post' => 11, 'user' => 11, 'contenu' => 'Partage tes templates ! Je cherche justement un bon format pour les stories Instagram.'],

            // Post 12 — Vulgarisation IA (Marc)
            ['post' => 12, 'user' => 10, 'contenu' => 'La métaphore est parfaite ! J\'utilise aussi des analogies géographiques pour expliquer l\'espace de paramètres.'],
            ['post' => 12, 'user' => 7,  'contenu' => 'La pédagogie par l\'analogie est validée par les sciences cognitives. Tu fais des sessions de vulgarisation ?'],
            ['post' => 12, 'user' => 0,  'contenu' => 'Je cherche quelqu\'un comme toi pour expliquer l\'IA à mes parents ! Tu fais des sessions pour non-techniciens ?'],

            // Post 13 — RGPD (Nadia)
            ['post' => 13, 'user' => 5,  'contenu' => 'Notre startup a justement besoin de cet audit. Est-ce que tu peux aussi revoir nos CGU ?'],
            ['post' => 13, 'user' => 8,  'contenu' => 'Bon timing ! J\'ai trouvé une faille de sécurité chez un client la semaine passée et la question RGPD s\'est posée immédiatement.'],
            ['post' => 13, 'user' => 13, 'contenu' => 'RGPD + sécurité = combo gagnant. On devrait collaborer sur une session commune !'],

            // Post 14 — SEO (Pauline)
            ['post' => 14, 'user' => 11, 'contenu' => 'Est-ce que le CLS s\'améliore si on pré-dimensionne les images en CSS ? J\'ai du mal à descendre sous 0.1.'],
            ['post' => 14, 'user' => 5,  'contenu' => 'Pour le LCP j\'ai passé du SSR avec Next.js et ça a tout changé. Le temps de rendu serveur fait la différence.'],
            ['post' => 14, 'user' => 15, 'contenu' => 'Oui @Léa, pré-dimensionner ET utiliser l\'attribut `aspect-ratio` en CSS. J\'explique ça dans mes sessions SEO technique.'],

            // Post 15 — Unity (Quentin)
            ['post' => 15, 'user' => 16, 'contenu' => 'J\'adorerais voir le projet ! Je débute Unity et les systèmes de particules me font peur.'],
            ['post' => 15, 'user' => 5,  'contenu' => 'Le lien entre Unity et le web me fascine. Tu as regardé WebGL export ?'],
            ['post' => 15, 'user' => 16, 'contenu' => 'Je vais partager le repo GitHub. C\'est un système météo procédural : pluie, neige, vent avec particules GPU.'],

            // Post 16 — Arabe (Rania)
            ['post' => 16, 'user' => 9,  'contenu' => 'Je veux apprendre l\'arabe pour mon stage au Maroc l\'année prochaine. Tu peux adapter le niveau débutant absolu ?'],
            ['post' => 16, 'user' => 4,  'contenu' => 'Je parle le darija mais pas l\'arabe classique. C\'est très différent ?'],
            ['post' => 16, 'user' => 17, 'contenu' => 'Très différent @Emma mais pas incompatible ! Le darija t\'aidera pour la prononciation. Je peux adapter la session.'],

            // Post 17 — Bioinformatique (Sophie)
            ['post' => 17, 'user' => 10, 'contenu' => 'Fascinant ! L\'IA appliquée à la génomique est un des domaines les plus prometteurs. AlphaFold a tout changé.'],
            ['post' => 17, 'user' => 7,  'contenu' => 'Les statistiques bayésiennes sont aussi très utilisées en génomique non ? On devrait co-organiser une session.'],
            ['post' => 17, 'user' => 0,  'contenu' => 'Je travaille aussi sur du ML pour la génomique. On devrait collaborer ! Je t\'envoie un message privé.'],

            // Post 18 — Blockchain (Thomas)
            ['post' => 18, 'user' => 13, 'contenu' => 'D\'un point de vue juridique, les smart contracts soulèvent des questions fascinantes sur la responsabilité. Hâte de ta session !'],
            ['post' => 18, 'user' => 3,  'contenu' => 'Je suis en finance, la blockchain m\'intéresse beaucoup. Est-ce qu\'on parle aussi de DeFi ?'],
            ['post' => 18, 'user' => 19, 'contenu' => 'Bien sûr ! DeFi, NFT, Web3... Je vais essayer de démystifier tout ça sans jargon marketing.'],

            // Post 19 — Pandas (Alice)
            ['post' => 19, 'user' => 10, 'contenu' => 'ydata-profiling (ancien pandas-profiling) c\'est excellent ! J\'ajoute aussi sweetviz pour comparer des datasets.'],
            ['post' => 19, 'user' => 18, 'contenu' => 'En bioinformatique on l\'utilise beaucoup pour l\'analyse exploratoire des données de séquençage. Très pratique !'],
            ['post' => 19, 'user' => 3,  'contenu' => 'C\'est le genre d\'outil qui me ferait commencer Python ! Pas besoin de coder toute l\'analyse à la main.'],
        ];

        foreach ($commentsData as $cd) {
            $comment = new Comment();
            $comment->setPost($posts[$cd['post']])
                    ->setUser($users[$cd['user']])
                    ->setContenu($cd['contenu']);
            $manager->persist($comment);
        }
    }

    private function createSessions(ObjectManager $manager, array $users): void
    {
        $sessionsData = [
            // Sessions originales
            ['tuteur' => 0,  'apprenant' => 3,  'competence' => 'Python',         'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -7,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Alice explique super bien ! Les list comprehensions sont enfin claires pour moi.']],

            ['tuteur' => 0,  'apprenant' => 6,  'competence' => 'Python',         'type' => 'atelier',      'statut' => 'completee', 'days' => -14, 'duree' => 90,
             'review' => ['note' => 5, 'commentaire' => 'Excellente session sur les décorateurs. Beaucoup d\'exercices pratiques !']],

            ['tuteur' => 1,  'apprenant' => 4,  'competence' => 'Figma',          'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 3,   'duree' => 60],
            ['tuteur' => 5,  'apprenant' => 3,  'competence' => 'React',          'type' => 'atelier',      'statut' => 'proposee',  'days' => 5,   'duree' => 120],
            ['tuteur' => 4,  'apprenant' => 9,  'competence' => 'Anglais',        'type' => 'club',         'statut' => 'confirmee', 'days' => 7,   'duree' => 60],

            ['tuteur' => 2,  'apprenant' => 5,  'competence' => 'Docker',         'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -3,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Clara est une super tutrice. Très patiente et pédagogue.']],

            ['tuteur' => 7,  'apprenant' => 6,  'competence' => 'Mathématiques',  'type' => 'cours_rapide', 'statut' => 'annulee',   'days' => -10, 'duree' => 60],

            ['tuteur' => 8,  'apprenant' => 0,  'competence' => 'Cybersécurité',  'type' => 'atelier',      'statut' => 'proposee',  'days' => 10,  'duree' => 120],

            // Nouvelles sessions
            ['tuteur' => 10, 'apprenant' => 0,  'competence' => 'TensorFlow',     'type' => 'atelier',      'statut' => 'completee', 'days' => -5,  'duree' => 120,
             'review' => ['note' => 5, 'commentaire' => 'Karim maîtrise parfaitement TensorFlow. Le TP sur les CNN était très bien structuré.']],

            ['tuteur' => 0,  'apprenant' => 19, 'competence' => 'Python',         'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -2,  'duree' => 60,
             'review' => ['note' => 4, 'commentaire' => 'Bonne session d\'introduction. Alice adapte bien son niveau au débutant.']],

            ['tuteur' => 13, 'apprenant' => 5,  'competence' => 'RGPD',           'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 2,   'duree' => 60],
            ['tuteur' => 4,  'apprenant' => 16, 'competence' => 'Anglais',        'type' => 'club',         'statut' => 'confirmee', 'days' => 4,   'duree' => 90],
            ['tuteur' => 7,  'apprenant' => 10, 'competence' => 'Statistiques',   'type' => 'atelier',      'statut' => 'proposee',  'days' => 8,   'duree' => 90],
            ['tuteur' => 17, 'apprenant' => 9,  'competence' => 'Arabe',          'type' => 'cours_rapide', 'statut' => 'proposee',  'days' => 6,   'duree' => 60],
            ['tuteur' => 15, 'apprenant' => 11, 'competence' => 'SEO',            'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -8,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Pauline est une vraie experte SEO. En 1h j\'ai appris plus qu\'en 3 mois de YouTube.']],

            ['tuteur' => 2,  'apprenant' => 14, 'competence' => 'Linux',          'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -4,  'duree' => 60,
             'review' => ['note' => 4, 'commentaire' => 'Bonne intro à Linux. Je suis enfin à l\'aise avec le terminal.']],

            ['tuteur' => 18, 'apprenant' => 10, 'competence' => 'R',              'type' => 'atelier',      'statut' => 'confirmee', 'days' => 9,   'duree' => 120],
            ['tuteur' => 8,  'apprenant' => 14, 'competence' => 'Kali Linux',     'type' => 'atelier',      'statut' => 'proposee',  'days' => 12,  'duree' => 120],
            ['tuteur' => 12, 'apprenant' => 19, 'competence' => 'Prise de parole','type' => 'cours_rapide', 'statut' => 'completee', 'days' => -6,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Marc a une pédagogie incroyable. Ma présentation de vendredi s\'est très bien passée.']],

            ['tuteur' => 1,  'apprenant' => 11, 'competence' => 'Figma',          'type' => 'atelier',      'statut' => 'completee', 'days' => -9,  'duree' => 90,
             'review' => ['note' => 4, 'commentaire' => 'Super atelier sur les composants Figma. J\'aurais aimé plus de temps sur les variables.']],

            ['tuteur' => 5,  'apprenant' => 7,  'competence' => 'Node.js',        'type' => 'cours_rapide', 'statut' => 'proposee',  'days' => 15,  'duree' => 60],
            ['tuteur' => 16, 'apprenant' => 14, 'competence' => 'Unity',          'type' => 'atelier',      'statut' => 'confirmee', 'days' => 11,  'duree' => 90],
            ['tuteur' => 3,  'apprenant' => 6,  'competence' => 'Excel',          'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -12, 'duree' => 60,
             'review' => ['note' => 3, 'commentaire' => 'Session correcte mais manquait de pratique. Les formules avancées n\'ont pas été abordées.']],
        ];

        foreach ($sessionsData as $sd) {
            $session = new Session();
            $isComplete = $sd['statut'] === 'completee';

            $session->setTuteur($users[$sd['tuteur']])
                    ->setApprenant($users[$sd['apprenant']])
                    ->setCompetence($sd['competence'])
                    ->setType($sd['type'])
                    ->setStatut($sd['statut'])
                    ->setDate((new \DateTime())->modify("{$sd['days']} days"))
                    ->setDureeMinutes($sd['duree'])
                    ->setLieuOuLien($isComplete ? 'Salle informatique B214' : 'https://meet.google.com/abc-def-ghi');

            if ($isComplete) {
                $session->setTuteurCompleted(true)->setApprenantCompleted(true);

                if (isset($sd['review'])) {
                    $review = new Review();
                    $review->setSession($session)
                           ->setAuteur($users[$sd['apprenant']])
                           ->setNote($sd['review']['note'])
                           ->setCommentaire($sd['review']['commentaire']);
                    $manager->persist($review);
                }
            }

            $manager->persist($session);
        }
    }

    private function assignBadges(ObjectManager $manager, array $users, array $badges): void
    {
        // Badge Fondateur pour tous les utilisateurs
        foreach ($users as $user) {
            $ub = new UserBadge();
            $ub->setUser($user)->setBadge($badges[2]);
            $manager->persist($ub);
        }

        // Badge Premier pas — utilisateurs ayant au moins une session complétée
        foreach ([0, 1, 2, 3, 4, 5, 7, 8, 10, 12, 15, 16, 17, 18, 19] as $idx) {
            if (!isset($users[$idx])) continue;
            $ub = new UserBadge();
            $ub->setUser($users[$idx])->setBadge($badges[0]);
            $manager->persist($ub);
        }

        // Badge Mentor confirmé — tuteurs avec 5+ sessions (Alice, Clara)
        foreach ([0, 2] as $idx) {
            $ub = new UserBadge();
            $ub->setUser($users[$idx])->setBadge($badges[1]);
            $manager->persist($ub);
        }

        // Badge Expert partage — 10+ sessions en tant que tuteur (Clara)
        $ub = new UserBadge();
        $ub->setUser($users[2])->setBadge($badges[3]);
        $manager->persist($ub);

        // Badge Fidèle — Clara et Karim (très actifs)
        foreach ([2, 10] as $idx) {
            $ub = new UserBadge();
            $ub->setUser($users[$idx])->setBadge($badges[4]);
            $manager->persist($ub);
        }

        // Badge Curieux — utilisateurs ayant participé à plusieurs types de sessions
        foreach ([0, 4, 5, 8] as $idx) {
            if (!isset($users[$idx])) continue;
            $ub = new UserBadge();
            $ub->setUser($users[$idx])->setBadge($badges[5]);
            $manager->persist($ub);
        }

        // Badge Influenceur — posts très likés (Alice, Clara, Inès)
        foreach ([0, 2, 8] as $idx) {
            $ub = new UserBadge();
            $ub->setUser($users[$idx])->setBadge($badges[6]);
            $manager->persist($ub);
        }

        // Badge Top Évaluateur — Karim et Sophie (reviews détaillées)
        foreach ([10, 18] as $idx) {
            if (!isset($users[$idx])) continue;
            $ub = new UserBadge();
            $ub->setUser($users[$idx])->setBadge($badges[7]);
            $manager->persist($ub);
        }
    }
}