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
            ['nom' => 'Premier pas',     'description' => 'Complétez votre première session',             'icone' => '👣', 'conditionType' => 'first_session',           'conditionValue' => 1],
            ['nom' => 'Mentor confirmé', 'description' => '5 sessions en tant que tuteur',                'icone' => '🎓', 'conditionType' => 'sessions_completed_tutor', 'conditionValue' => 5],
            ['nom' => 'Fondateur',       'description' => 'Parmi les 50 premiers inscrits',               'icone' => '🏛️', 'conditionType' => 'registered_early',          'conditionValue' => 1],
            ['nom' => 'Expert partage',  'description' => '10 sessions d\'enseignement complétées',      'icone' => '🔬', 'conditionType' => 'sessions_completed_tutor', 'conditionValue' => 10],
            ['nom' => 'Fidèle',          'description' => '30 jours d\'activité sur la plateforme',      'icone' => '🌟', 'conditionType' => 'registered_early',          'conditionValue' => 1],
            ['nom' => 'Curieux',         'description' => 'Participer à 3 types de sessions différents', 'icone' => '🔍', 'conditionType' => 'first_session',             'conditionValue' => 3],
            ['nom' => 'Influenceur',     'description' => 'Obtenir 50 likes sur vos posts',              'icone' => '🚀', 'conditionType' => 'registered_early',          'conditionValue' => 1],
            ['nom' => 'Top Évaluateur', 'description' => 'Rédiger 10 avis détaillés',                   'icone' => '⭐', 'conditionType' => 'first_session',             'conditionValue' => 10],
        ];

        $badges = [];
        foreach ($data as $d) {
            $badge = (new Badge())
                ->setNom($d['nom'])
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
            // 0
            ['prenom' => 'Alice',    'nom' => 'Martin',     'email' => 'alice@skillswap.fr',     'formation' => 'Master Informatique',    'promotion' => '2025', 'score' => 850,  'niveau' => 'expert',
             'bio' => 'Passionnée de Python et machine learning. Je propose des cours adaptés à tous niveaux.',
             'skills' => [['nom' => 'Python', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'], ['nom' => 'Machine Learning', 'type' => 'teach', 'niveau' => 3, 'cat' => 'IA'], ['nom' => 'React', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['lun', '09:00', '11:00'], ['mer', '14:00', '17:00'], ['sam', '10:00', '12:00']]],
            // 1
            ['prenom' => 'Baptiste', 'nom' => 'Durand',    'email' => 'baptiste@skillswap.fr',  'formation' => 'Licence Design',         'promotion' => '2026', 'score' => 320,  'niveau' => 'mentor',
             'bio' => 'Designer UI/UX depuis 3 ans. Figma et Adobe sont mes outils du quotidien.',
             'skills' => [['nom' => 'Figma', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Design'], ['nom' => 'Photoshop', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mar', '13:00', '15:00'], ['jeu', '10:00', '12:00']]],
            // 2
            ['prenom' => 'Clara',    'nom' => 'Lefebvre',  'email' => 'clara@skillswap.fr',     'formation' => 'DUT Réseaux',            'promotion' => '2025', 'score' => 1650, 'niveau' => 'legende',
             'bio' => 'Admin sys et réseau. Certifiée Cisco CCNA. Adepte du libre.',
             'skills' => [['nom' => 'Linux', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Systèmes'], ['nom' => 'Cisco', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Réseaux'], ['nom' => 'Docker', 'type' => 'teach', 'niveau' => 3, 'cat' => 'DevOps'], ['nom' => 'Kubernetes', 'type' => 'learn', 'niveau' => 2, 'cat' => 'DevOps']],
             'avails' => [['lun', '18:00', '20:00'], ['mer', '18:00', '20:00'], ['ven', '14:00', '16:00']]],
            // 3
            ['prenom' => 'David',    'nom' => 'Bernard',   'email' => 'david@skillswap.fr',     'formation' => 'Master Finance',         'promotion' => '2025', 'score' => 145,  'niveau' => 'apprenti',
             'bio' => 'Passionné d\'économie et de finance. Je cherche à renforcer mes compétences tech.',
             'skills' => [['nom' => 'Excel', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Bureautique'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'SQL', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Base de données']],
             'avails' => [['mar', '12:00', '14:00'], ['jeu', '16:00', '18:00']]],
            // 4
            ['prenom' => 'Emma',     'nom' => 'Petit',     'email' => 'emma@skillswap.fr',      'formation' => 'Licence Anglais',        'promotion' => '2026', 'score' => 210,  'niveau' => 'mentor',
             'bio' => 'Bilingue anglais-français. Cours de conversation et rédaction académique.',
             'skills' => [['nom' => 'Anglais', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Espagnol', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Langues'], ['nom' => 'Photoshop', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Design']],
             'avails' => [['lun', '14:00', '16:00'], ['mer', '09:00', '11:00'], ['sam', '14:00', '16:00']]],
            // 5
            ['prenom' => 'Florian',  'nom' => 'Thomas',    'email' => 'florian@skillswap.fr',   'formation' => 'Master Dev Web',         'promotion' => '2025', 'score' => 560,  'niveau' => 'mentor',
             'bio' => 'Full-stack JS. Je maîtrise React, Node.js, et les bases SQL/NoSQL.',
             'skills' => [['nom' => 'React', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Web'], ['nom' => 'Node.js', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Web'], ['nom' => 'Docker', 'type' => 'learn', 'niveau' => 2, 'cat' => 'DevOps']],
             'avails' => [['lun', '19:00', '21:00'], ['ven', '10:00', '12:00']]],
            // 6
            ['prenom' => 'Gaëlle',   'nom' => 'Roux',      'email' => 'gaelle@skillswap.fr',    'formation' => 'BTS Comptabilité',       'promotion' => '2026', 'score' => 45,   'niveau' => 'novice',
             'bio' => 'Débutante en informatique. Je veux apprendre à coder !',
             'skills' => [['nom' => 'Comptabilité', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Gestion'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mar', '17:00', '19:00'], ['dim', '10:00', '12:00']]],
            // 7
            ['prenom' => 'Hugo',     'nom' => 'Moreau',    'email' => 'hugo@skillswap.fr',      'formation' => 'Licence Maths',          'promotion' => '2025', 'score' => 400,  'niveau' => 'mentor',
             'bio' => 'Matheux passionné. Spécialiste en algèbre linéaire et statistiques.',
             'skills' => [['nom' => 'Mathématiques', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'Statistiques', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'R', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Programmation'], ['nom' => 'React', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['mer', '10:00', '12:00'], ['sam', '09:00', '11:00']]],
            // 8
            ['prenom' => 'Inès',     'nom' => 'Simon',     'email' => 'ines@skillswap.fr',      'formation' => 'Master Cybersécurité',   'promotion' => '2025', 'score' => 720,  'niveau' => 'expert',
             'bio' => 'Passionnée de sécu, CTF et pentesting. Certifiée CEH.',
             'skills' => [['nom' => 'Cybersécurité', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sécurité'], ['nom' => 'Kali Linux', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Sécurité'], ['nom' => 'SQL', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Base de données']],
             'avails' => [['jeu', '20:00', '22:00'], ['sam', '15:00', '17:00']]],
            // 9
            ['prenom' => 'Julien',   'nom' => 'Lemaire',   'email' => 'julien@skillswap.fr',    'formation' => 'DUT Génie Civil',        'promotion' => '2026', 'score' => 80,   'niveau' => 'novice',
             'bio' => 'Futur ingénieur. Cherche à améliorer mon anglais et mes compétences en gestion de projet.',
             'skills' => [['nom' => 'AutoCAD', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Ingénierie'], ['nom' => 'Anglais', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Langues']],
             'avails' => [['lun', '12:00', '14:00'], ['mer', '12:00', '14:00']]],
            // 10
            ['prenom' => 'Karim',    'nom' => 'Benali',    'email' => 'karim@skillswap.fr',     'formation' => 'Master IA',              'promotion' => '2025', 'score' => 930,  'niveau' => 'expert',
             'bio' => 'Data scientist chez une startup. Je travaille sur des modèles NLP et vision par ordinateur.',
             'skills' => [['nom' => 'TensorFlow', 'type' => 'teach', 'niveau' => 4, 'cat' => 'IA'], ['nom' => 'PyTorch', 'type' => 'teach', 'niveau' => 4, 'cat' => 'IA'], ['nom' => 'SQL', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Base de données'], ['nom' => 'Kubernetes', 'type' => 'learn', 'niveau' => 1, 'cat' => 'DevOps']],
             'avails' => [['lun', '20:00', '22:00'], ['sam', '09:00', '11:00']]],
            // 11
            ['prenom' => 'Léa',      'nom' => 'Fontaine',  'email' => 'lea@skillswap.fr',       'formation' => 'BTS Communication',      'promotion' => '2026', 'score' => 175,  'niveau' => 'apprenti',
             'bio' => 'Passionnée de marketing digital et réseaux sociaux. J\'apprends le HTML/CSS.',
             'skills' => [['nom' => 'Marketing digital', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Marketing'], ['nom' => 'Canva', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design'], ['nom' => 'HTML/CSS', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['mar', '10:00', '12:00'], ['jeu', '14:00', '16:00']]],
            // 12
            ['prenom' => 'Marc',     'nom' => 'Vasseur',   'email' => 'marc@skillswap.fr',      'formation' => 'Licence Philosophie',    'promotion' => '2025', 'score' => 290,  'niveau' => 'mentor',
             'bio' => 'Prof de philo reconverti dans la data. J\'adore vulgariser les concepts complexes.',
             'skills' => [['nom' => 'Prise de parole', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Communication'], ['nom' => 'Rédaction', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Communication'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Programmation']],
             'avails' => [['mer', '15:00', '17:00'], ['ven', '09:00', '11:00']]],
            // 13
            ['prenom' => 'Nadia',    'nom' => 'Cherif',    'email' => 'nadia@skillswap.fr',     'formation' => 'Master Droit',           'promotion' => '2025', 'score' => 480,  'niveau' => 'mentor',
             'bio' => 'Juriste spécialisée en droit du numérique. RGPD et propriété intellectuelle.',
             'skills' => [['nom' => 'Droit numérique', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Droit'], ['nom' => 'RGPD', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Droit'], ['nom' => 'Excel', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Bureautique']],
             'avails' => [['lun', '13:00', '15:00'], ['jeu', '18:00', '20:00']]],
            // 14
            ['prenom' => 'Oscar',    'nom' => 'Girard',    'email' => 'oscar@skillswap.fr',     'formation' => 'DUT Informatique',       'promotion' => '2026', 'score' => 60,   'niveau' => 'novice',
             'bio' => 'Étudiant en informatique, premier semestre. Je cherche de l\'aide sur les bases.',
             'skills' => [['nom' => 'HTML/CSS', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Web'], ['nom' => 'Java', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mar', '08:00', '10:00'], ['ven', '14:00', '16:00']]],
            // 15
            ['prenom' => 'Pauline',  'nom' => 'Marchand',  'email' => 'pauline@skillswap.fr',   'formation' => 'Master Marketing',       'promotion' => '2025', 'score' => 615,  'niveau' => 'expert',
             'bio' => 'Growth hacker et experte SEO. J\'aide les startups à scaler leur acquisition.',
             'skills' => [['nom' => 'SEO', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Marketing'], ['nom' => 'Google Analytics', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Marketing'], ['nom' => 'SQL', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Base de données']],
             'avails' => [['lun', '10:00', '12:00'], ['mer', '10:00', '12:00']]],
            // 16
            ['prenom' => 'Quentin',  'nom' => 'Lacombe',   'email' => 'quentin@skillswap.fr',   'formation' => 'Bachelor Game Design',   'promotion' => '2026', 'score' => 135,  'niveau' => 'apprenti',
             'bio' => 'Passionné de jeux vidéo et de programmation. J\'apprends Unity et C#.',
             'skills' => [['nom' => 'Unity', 'type' => 'teach', 'niveau' => 2, 'cat' => 'GameDev'], ['nom' => 'Blender', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design 3D'], ['nom' => 'C#', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Programmation']],
             'avails' => [['mar', '20:00', '22:00'], ['dim', '15:00', '17:00']]],
            // 17
            ['prenom' => 'Rania',    'nom' => 'El Amrani', 'email' => 'rania@skillswap.fr',     'formation' => 'Master Traduction',      'promotion' => '2025', 'score' => 345,  'niveau' => 'mentor',
             'bio' => 'Traductrice français-arabe-anglais. Je propose des cours d\'arabe littéraire.',
             'skills' => [['nom' => 'Arabe', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Anglais', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mer', '16:00', '18:00'], ['sam', '13:00', '15:00']]],
            // 18
            ['prenom' => 'Sophie',   'nom' => 'Renard',    'email' => 'sophie@skillswap.fr',    'formation' => 'Master Biologie',        'promotion' => '2025', 'score' => 510,  'niveau' => 'expert',
             'bio' => 'Chercheuse en bioinformatique. Je travaille sur l\'analyse de séquences ADN.',
             'skills' => [['nom' => 'Bioinformatique', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'Python', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Programmation'], ['nom' => 'R', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation']],
             'avails' => [['lun', '17:00', '19:00'], ['jeu', '09:00', '11:00']]],
            // 19
            ['prenom' => 'Thomas',   'nom' => 'Barbier',   'email' => 'thomas@skillswap.fr',    'formation' => 'Licence Économie',       'promotion' => '2026', 'score' => 190,  'niveau' => 'apprenti',
             'bio' => 'Étudiant en éco, fan de crypto et blockchain.',
             'skills' => [['nom' => 'Blockchain', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Finance'], ['nom' => 'Économie', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Finance'], ['nom' => 'JavaScript', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['mar', '15:00', '17:00'], ['ven', '12:00', '14:00']]],

            // --- 20 nouveaux utilisateurs ---
            // 20
            ['prenom' => 'Alexis',   'nom' => 'Dupont',    'email' => 'alexis@skillswap.fr',    'formation' => 'Master DevOps',          'promotion' => '2025', 'score' => 780,  'niveau' => 'expert',
             'bio' => 'Ingénieur DevOps chez une scale-up. CI/CD, Terraform et monitoring sont mon quotidien.',
             'skills' => [['nom' => 'Terraform', 'type' => 'teach', 'niveau' => 4, 'cat' => 'DevOps'], ['nom' => 'Kubernetes', 'type' => 'teach', 'niveau' => 4, 'cat' => 'DevOps'], ['nom' => 'Prometheus', 'type' => 'teach', 'niveau' => 3, 'cat' => 'DevOps'], ['nom' => 'Rust', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['lun', '19:00', '21:00'], ['jeu', '19:00', '21:00']]],
            // 21
            ['prenom' => 'Béatrice', 'nom' => 'Vidal',     'email' => 'beatrice@skillswap.fr',  'formation' => 'Master Psychologie',     'promotion' => '2025', 'score' => 350,  'niveau' => 'mentor',
             'bio' => 'Psychologue et coach. J\'aide à mieux gérer le stress des examens et à apprendre plus efficacement.',
             'skills' => [['nom' => 'Gestion du stress', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Bien-être'], ['nom' => 'Méthodes de travail', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Pédagogie'], ['nom' => 'Excel', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Bureautique']],
             'avails' => [['mar', '09:00', '11:00'], ['sam', '10:00', '12:00']]],
            // 22
            ['prenom' => 'Charles',  'nom' => 'Morin',     'email' => 'charles@skillswap.fr',   'formation' => 'DUT Informatique',       'promotion' => '2026', 'score' => 120,  'niveau' => 'apprenti',
             'bio' => 'Curieux de tout, je débute dans le dev web et cherche des mentors motivants.',
             'skills' => [['nom' => 'HTML/CSS', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Web'], ['nom' => 'JavaScript', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web'], ['nom' => 'Git', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Outils']],
             'avails' => [['mer', '14:00', '16:00'], ['ven', '10:00', '12:00']]],
            // 23
            ['prenom' => 'Diane',    'nom' => 'Laurent',   'email' => 'diane@skillswap.fr',     'formation' => 'Master Data Science',   'promotion' => '2025', 'score' => 890,  'niveau' => 'expert',
             'bio' => 'Data engineer passionnée. Spécialiste Spark, Kafka et pipelines de données temps réel.',
             'skills' => [['nom' => 'Apache Spark', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Big Data'], ['nom' => 'Kafka', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Big Data'], ['nom' => 'Python', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'], ['nom' => 'Scala', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Programmation']],
             'avails' => [['lun', '08:00', '10:00'], ['mer', '08:00', '10:00'], ['ven', '08:00', '10:00']]],
            // 24
            ['prenom' => 'Étienne',  'nom' => 'Bonnet',    'email' => 'etienne@skillswap.fr',   'formation' => 'BTS Électronique',       'promotion' => '2026', 'score' => 95,   'niveau' => 'novice',
             'bio' => 'Passionné d\'Arduino et d\'électronique embarquée. J\'aimerais apprendre la programmation C++.',
             'skills' => [['nom' => 'Arduino', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Électronique'], ['nom' => 'C++', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mar', '18:00', '20:00'], ['dim', '14:00', '16:00']]],
            // 25
            ['prenom' => 'Fatima',   'nom' => 'Ouali',     'email' => 'fatima@skillswap.fr',    'formation' => 'Master Marketing',       'promotion' => '2025', 'score' => 440,  'niveau' => 'mentor',
             'bio' => 'Spécialiste en stratégie de contenu et community management. Je forme les débutants.',
             'skills' => [['nom' => 'Community management', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Marketing'], ['nom' => 'Rédaction web', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Communication'], ['nom' => 'Canva', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design']],
             'avails' => [['lun', '11:00', '13:00'], ['jeu', '11:00', '13:00'], ['sam', '09:00', '11:00']]],
            // 26
            ['prenom' => 'Gabriel',  'nom' => 'Picard',    'email' => 'gabriel@skillswap.fr',   'formation' => 'Licence Histoire',       'promotion' => '2026', 'score' => 55,   'niveau' => 'novice',
             'bio' => 'Historien reconverti. Je veux apprendre à coder pour faire de l\'histoire numérique.',
             'skills' => [['nom' => 'Recherche documentaire', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Académique'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'HTML/CSS', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['mer', '13:00', '15:00'], ['dim', '10:00', '12:00']]],
            // 27
            ['prenom' => 'Héloïse',  'nom' => 'Garnier',   'email' => 'heloise@skillswap.fr',   'formation' => 'Master Chimie',          'promotion' => '2025', 'score' => 670,  'niveau' => 'expert',
             'bio' => 'Chercheuse en chimie computationnelle. J\'utilise Python pour modéliser des molécules.',
             'skills' => [['nom' => 'Chimie', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'Python', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Programmation'], ['nom' => 'LaTeX', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Outils'], ['nom' => 'Machine Learning', 'type' => 'learn', 'niveau' => 2, 'cat' => 'IA']],
             'avails' => [['mar', '14:00', '16:00'], ['ven', '14:00', '16:00']]],
            // 28
            ['prenom' => 'Ibrahim',  'nom' => 'Touré',     'email' => 'ibrahim@skillswap.fr',   'formation' => 'DUT Gestion',            'promotion' => '2026', 'score' => 165,  'niveau' => 'apprenti',
             'bio' => 'Étudiant en gestion, je cherche à maîtriser Excel avancé et la comptabilité analytique.',
             'skills' => [['nom' => 'Gestion de projet', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Management'], ['nom' => 'Excel', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Bureautique'], ['nom' => 'SQL', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Base de données']],
             'avails' => [['lun', '16:00', '18:00'], ['jeu', '12:00', '14:00']]],
            // 29
            ['prenom' => 'Julia',    'nom' => 'Blanc',     'email' => 'julia@skillswap.fr',     'formation' => 'Bachelor Photographie',  'promotion' => '2026', 'score' => 310,  'niveau' => 'mentor',
             'bio' => 'Photographe professionnelle. Je propose des cours de retouche Lightroom et composition.',
             'skills' => [['nom' => 'Photographie', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Art'], ['nom' => 'Lightroom', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Design'], ['nom' => 'Photoshop', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design'], ['nom' => 'Vidéo', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Art']],
             'avails' => [['mar', '11:00', '13:00'], ['sam', '14:00', '16:00']]],
            // 30
            ['prenom' => 'Kevin',    'nom' => 'Mallet',    'email' => 'kevin@skillswap.fr',     'formation' => 'Master Cybersécurité',   'promotion' => '2025', 'score' => 810,  'niveau' => 'expert',
             'bio' => 'Expert en sécurité offensive. Bug bounty hunter et formateur OSCP.',
             'skills' => [['nom' => 'Pentesting', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sécurité'], ['nom' => 'Reverse Engineering', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Sécurité'], ['nom' => 'Python', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Programmation']],
             'avails' => [['mer', '20:00', '22:00'], ['sam', '10:00', '12:00']]],
            // 31
            ['prenom' => 'Laura',    'nom' => 'Simon',     'email' => 'laura@skillswap.fr',     'formation' => 'Licence STAPS',          'promotion' => '2026', 'score' => 40,   'niveau' => 'novice',
             'bio' => 'Étudiante en sport. Je souhaite apprendre à créer un site web pour mon association sportive.',
             'skills' => [['nom' => 'Coaching sportif', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Sport'], ['nom' => 'HTML/CSS', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['lun', '07:00', '09:00'], ['ven', '18:00', '20:00']]],
            // 32
            ['prenom' => 'Maxime',   'nom' => 'Perrin',    'email' => 'maxime@skillswap.fr',    'formation' => 'Master Architecture',    'promotion' => '2025', 'score' => 495,  'niveau' => 'mentor',
             'bio' => 'Architecte et passionné de BIM. J\'enseigne Revit et les outils de conception paramétrique.',
             'skills' => [['nom' => 'Revit', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Architecture'], ['nom' => 'AutoCAD', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Architecture'], ['nom' => 'Rhino3D', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design 3D']],
             'avails' => [['mer', '17:00', '19:00'], ['sam', '09:00', '11:00']]],
            // 33
            ['prenom' => 'Noémie',   'nom' => 'Rousseau',  'email' => 'noemie@skillswap.fr',    'formation' => 'BTS Tourisme',           'promotion' => '2026', 'score' => 110,  'niveau' => 'apprenti',
             'bio' => 'Passionnée de voyages et de langues. J\'apprends l\'espagnol et le portugais en parallèle.',
             'skills' => [['nom' => 'Français', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Espagnol', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Langues'], ['nom' => 'Portugais', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Langues']],
             'avails' => [['jeu', '15:00', '17:00'], ['dim', '11:00', '13:00']]],
            // 34
            ['prenom' => 'Olivier',  'nom' => 'Leroy',     'email' => 'olivier@skillswap.fr',   'formation' => 'Master Informatique',   'promotion' => '2024', 'score' => 1420, 'niveau' => 'legende',
             'bio' => 'Ingénieur logiciel senior. 8 ans d\'expérience en architecture microservices et cloud.',
             'skills' => [['nom' => 'Java', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'], ['nom' => 'Spring Boot', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Web'], ['nom' => 'AWS', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Cloud'], ['nom' => 'Rust', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Programmation']],
             'avails' => [['lun', '12:00', '14:00'], ['mer', '12:00', '14:00'], ['ven', '12:00', '14:00']]],
            // 35
            ['prenom' => 'Priya',    'nom' => 'Sharma',    'email' => 'priya@skillswap.fr',     'formation' => 'Master IA',              'promotion' => '2025', 'score' => 760,  'niveau' => 'expert',
             'bio' => 'ML Engineer spécialisée en NLP et LLM. Je contribue à des projets open source.',
             'skills' => [['nom' => 'NLP', 'type' => 'teach', 'niveau' => 4, 'cat' => 'IA'], ['nom' => 'Hugging Face', 'type' => 'teach', 'niveau' => 4, 'cat' => 'IA'], ['nom' => 'Python', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation']],
             'avails' => [['mar', '19:00', '21:00'], ['jeu', '19:00', '21:00']]],
            // 36
            ['prenom' => 'Raphaël',  'nom' => 'Clément',   'email' => 'raphael@skillswap.fr',   'formation' => 'Bachelor Musique',       'promotion' => '2026', 'score' => 280,  'niveau' => 'mentor',
             'bio' => 'Musicien et producteur. J\'enseigne la MAO, le piano et la théorie musicale.',
             'skills' => [['nom' => 'Piano', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Musique'], ['nom' => 'MAO', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Musique'], ['nom' => 'Ableton Live', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Musique']],
             'avails' => [['mar', '16:00', '18:00'], ['sam', '11:00', '13:00'], ['dim', '15:00', '17:00']]],
            // 37
            ['prenom' => 'Samira',   'nom' => 'Khalil',    'email' => 'samira@skillswap.fr',    'formation' => 'Master Traduction',      'promotion' => '2025', 'score' => 390,  'niveau' => 'mentor',
             'bio' => 'Traductrice et interprète. Spécialisée anglais-français-espagnol pour des textes techniques.',
             'skills' => [['nom' => 'Espagnol', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Anglais', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Traduction technique', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues']],
             'avails' => [['lun', '10:00', '12:00'], ['jeu', '10:00', '12:00']]],
            // 38
            ['prenom' => 'Théo',     'nom' => 'Faure',     'email' => 'theo@skillswap.fr',      'formation' => 'DUT Génie Mécanique',   'promotion' => '2026', 'score' => 145,  'niveau' => 'apprenti',
             'bio' => 'Futur ingénieur mécanicien. Je cherche à apprendre SolidWorks et la simulation numérique.',
             'skills' => [['nom' => 'SolidWorks', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Ingénierie'], ['nom' => 'MATLAB', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mer', '16:00', '18:00'], ['ven', '16:00', '18:00']]],
            // 39
            ['prenom' => 'Victor',   'nom' => 'Renaud',    'email' => 'victor@skillswap.fr',    'formation' => 'Master Finance Quant',  'promotion' => '2025', 'score' => 695,  'niveau' => 'expert',
             'bio' => 'Quant analyst. Je modélise des stratégies d\'investissement avec Python et R.',
             'skills' => [['nom' => 'Python', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'], ['nom' => 'R', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'], ['nom' => 'Finance quantitative', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Finance'], ['nom' => 'C++', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Programmation']],
             'avails' => [['lun', '07:00', '09:00'], ['mer', '07:00', '09:00']]],
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
                 ->setPassword($this->passwordHasher->hashPassword($user, 'Azerty@123456'));

            foreach ($data['skills'] as $sd) {
                $skill = (new Skill())
                    ->setNom($sd['nom'])
                    ->setType($sd['type'])
                    ->setNiveau($sd['niveau'])
                    ->setCategorie($sd['cat'])
                    ->setUser($user);
                $manager->persist($skill);
            }

            foreach ($data['avails'] as [$jour, $debut, $fin]) {
                $avail = (new Availability())
                    ->setJourSemaine($jour)
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
            ['user' => 0,  'contenu' => "Venez de finir mon cours Python sur les décorateurs. Résultat : mon code est 3x plus lisible ! Qui veut que je lui explique le concept ?", 'tag' => 'Python'],
            ['user' => 2,  'contenu' => "Docker + Compose = la combo parfaite pour isoler vos environnements de dev. Fini les \"ça marche chez moi\" ! J'organise un atelier la semaine prochaine.", 'tag' => 'Docker'],
            ['user' => 4,  'contenu' => "Petite astuce anglais : pour améliorer son accent, écoutez des podcasts en demi-vitesse sur Spotify. Ça change tout !", 'tag' => 'Anglais'],
            ['user' => 5,  'contenu' => "React 19 est sorti ! Les Server Components changent vraiment la donne. Quelqu'un veut co-apprendre les nouvelles features ensemble ?", 'tag' => 'React'],
            ['user' => 7,  'contenu' => "Tip maths : pour visualiser les matrices, pensez à elles comme des transformations dans l'espace. Ça rend l'algèbre linéaire tellement plus intuitive !", 'tag' => 'Mathématiques'],
            ['user' => 8,  'contenu' => "CTF de la semaine résolu ! La faille était une injection SQL pas très bien cachée... Rappel : TOUJOURS utiliser des requêtes préparées.", 'tag' => 'Cybersécurité'],
            ['user' => 1,  'contenu' => "Nouvelle ressource Figma : les variables de design sont enfin disponibles pour tous ! Le design system devient tellement plus maintenable.", 'tag' => 'Figma'],
            ['user' => 3,  'contenu' => "Première session SkillSwap terminée avec Alice sur Python. En 1h j'ai compris les list comprehensions. Merci la plateforme !", 'tag' => 'Python'],
            ['user' => 6,  'contenu' => "Question pour les codeurs : vous utilisez quoi comme éditeur ? VSCode avec Vim keybindings ici, mais curieuse de découvrir autre chose.", 'tag' => null],
            ['user' => 9,  'contenu' => "AutoCAD tip : les blocs dynamiques permettent de créer des composants réutilisables. Indispensable pour les plans d'architecture !", 'tag' => 'AutoCAD'],
            ['user' => 10, 'contenu' => "PyTorch 2.0 c'est un game changer pour la perf des modèles. La compilation avec torch.compile() réduit le temps d'entraînement de 30% sur mes benchmarks.", 'tag' => 'Machine Learning'],
            ['user' => 11, 'contenu' => "Si vous ne connaissez pas Canva Pro, vous ratez quelque chose ! Créer des visuels pro en 10 minutes, c'est possible.", 'tag' => 'Canva'],
            ['user' => 12, 'contenu' => "La vulgarisation c'est un art. Expliquer le gradient descent comme une randonnée en montagne les yeux bandés : on descend à tâtons jusqu'au fond de la vallée.", 'tag' => 'Machine Learning'],
            ['user' => 13, 'contenu' => "Le RGPD fête ses 6 ans ! Et encore beaucoup d'entreprises ne sont pas conformes. Je propose un audit gratuit de 30 min.", 'tag' => 'RGPD'],
            ['user' => 15, 'contenu' => "Core Web Vitals mis à jour par Google. Le LCP et le CLS impactent maintenant fortement le référencement. Mon guide complet pour optimiser votre score.", 'tag' => 'SEO'],
            ['user' => 16, 'contenu' => "Unity 2024 LTS est incroyable. Le nouveau système de particules GPU permet des effets visuels dingues.", 'tag' => 'Unity'],
            ['user' => 17, 'contenu' => "Cours d'arabe littéraire disponible ! L'arabe est la 5e langue la plus parlée au monde. Commencez avec l'alphabet en 3 sessions.", 'tag' => 'Arabe'],
            ['user' => 18, 'contenu' => "BLAST+ pour l'alignement de séquences protéiques, BioPython pour l'automatisation : mon pipeline d'analyse génomique tourne en 20min au lieu de 3h.", 'tag' => 'Bioinformatique'],
            ['user' => 19, 'contenu' => "Débat du jour : la blockchain va-t-elle vraiment révolutionner la finance ? Je prépare une session d'introduction pour démystifier le sujet.", 'tag' => 'Blockchain'],
            ['user' => 0,  'contenu' => "Nouveau tutoriel disponible : pandas profiling pour l'EDA automatique. En 3 lignes de code, générez un rapport complet sur vos données !", 'tag' => 'Python'],

            // 20 nouveaux posts
            ['user' => 20, 'contenu' => "Terraform + GitHub Actions = déploiement infra 100% automatisé. Mon pipeline déploie en prod en moins de 5 minutes. Qui veut un retour d'expérience ?", 'tag' => 'Terraform'],
            ['user' => 21, 'contenu' => "La technique Pomodoro ne fonctionne pas pour tout le monde ! En tant que psychologue, je vous propose 3 alternatives selon votre profil cognitif.", 'tag' => null],
            ['user' => 23, 'contenu' => "Apache Kafka pour les nuls : un topic c'est comme une file de supermarché. Les producteurs y déposent, les consommateurs y piochent. Simple non ?", 'tag' => 'Kafka'],
            ['user' => 24, 'contenu' => "Mon projet Arduino du weekend : une station météo qui tweete automatiquement la température toutes les heures. Code open source disponible !", 'tag' => 'Arduino'],
            ['user' => 25, 'contenu' => "Tip community management : répondez toujours aux commentaires négatifs en public, mais résolvez les problèmes en privé. Votre réputation en dépend.", 'tag' => null],
            ['user' => 27, 'contenu' => "LaTeX vs Word pour une thèse : il n'y a même pas de débat. La numérotation automatique, les références croisées, les formules chimiques... LaTeX gagne à tous les niveaux.", 'tag' => null],
            ['user' => 29, 'contenu' => "Règle d'or de la photographie : la lumière naturelle du matin (golden hour) rend n'importe quel sujet magnifique. Pas besoin d'équipement hors de prix !", 'tag' => 'Photographie'],
            ['user' => 30, 'contenu' => "Résumé de la conférence DEF CON : les attaques sur les appareils IoT sont en hausse de 400%. Si vous avez une caméra connectée, changez le mot de passe par défaut MAINTENANT.", 'tag' => 'Cybersécurité'],
            ['user' => 32, 'contenu' => "Revit 2025 supporte enfin les nuages de points nativement ! Plus besoin de plugins tiers pour travailler sur les rénovations de bâtiments existants.", 'tag' => 'Revit'],
            ['user' => 34, 'contenu' => "Architecte microservices depuis 5 ans, voici ma leçon principale : commencez TOUJOURS par un monolithe. Découpez en services seulement quand la douleur est réelle.", 'tag' => null],
            ['user' => 35, 'contenu' => "Les LLM open source rattrapent GPT-4 sur beaucoup de benchmarks. Llama 3, Mistral, Mixtral... Le futur est open source et local !", 'tag' => 'NLP'],
            ['user' => 36, 'contenu' => "Vous voulez apprendre la musique mais pensez ne pas avoir l'oreille ? Mauvaise nouvelle : l'oreille musicale s'apprend. Bonne nouvelle : je peux vous aider !", 'tag' => 'Musique'],
            ['user' => 37, 'contenu' => "Tip traduction : ne traduisez jamais mot à mot. La fidélité au sens prime sur la fidélité aux mots. C'est la différence entre une traduction et une bonne traduction.", 'tag' => null],
            ['user' => 39, 'contenu' => "Stratégie momentum en Python : 15 lignes de code, un backtest sur 10 ans de données. La finance quantitative n'est pas si inaccessible !", 'tag' => 'Finance quantitative'],
            ['user' => 20, 'contenu' => "Kubernetes vs Docker Swarm en 2024 : Kubernetes a gagné la guerre de l'orchestration. Mais Swarm reste parfait pour les petites équipes sans DevOps dédié.", 'tag' => 'Kubernetes'],
            ['user' => 34, 'contenu' => "AWS Lambda fonctionne, mais attention aux cold starts. Pour du critique en production, pré-chauffez vos fonctions avec des scheduled events.", 'tag' => 'AWS'],
            ['user' => 10, 'contenu' => "Fine-tuning de LLM avec LoRA : j'ai adapté Llama 3 à mon domaine métier en 2h sur une seule GPU A100. Le résultat est bluffant.", 'tag' => 'Machine Learning'],
            ['user' => 23, 'contenu' => "Delta Lake + Spark Structured Streaming = data lakehouse en temps réel. J'ai mis en place cette archi pour 10TB de données/jour. AMA !", 'tag' => 'Apache Spark'],
            ['user' => 35, 'contenu' => "RAG (Retrieval Augmented Generation) expliqué simplement : vous donnez une mémoire externe à votre LLM. Il cherche dans vos docs avant de répondre.", 'tag' => 'NLP'],
            ['user' => 8,  'contenu' => "Rappel sécurité : utilisez un gestionnaire de mots de passe. Bitwarden est open source et gratuit. Votre futur vous remerciera quand le prochain leak arrivera.", 'tag' => 'Cybersécurité'],
        ];

        $posts = [];
        foreach ($postsData as $pd) {
            $post = (new Post())
                ->setUser($users[$pd['user']])
                ->setContenu($pd['contenu'])
                ->setCompetenceTag($pd['tag']);

            $likers = array_rand($users, rand(2, min(8, count($users))));
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
            // Post 0 — Python décorateurs
            ['post' => 0, 'user' => 3,  'contenu' => 'Super ! J\'ai justement un projet où j\'aurais besoin d\'un décorateur de cache. Tu peux m\'aider ?'],
            ['post' => 0, 'user' => 6,  'contenu' => 'J\'ai essayé les décorateurs hier soir, le concept de wrapper de fonction est vraiment élégant.'],
            ['post' => 0, 'user' => 19, 'contenu' => 'Ça ressemble au pattern Decorator en POO finalement ?'],
            ['post' => 0, 'user' => 10, 'contenu' => 'Oui exactement ! C\'est la même idée appliquée à Python de façon pythonique.'],
            // Post 1 — Docker
            ['post' => 1, 'user' => 5,  'contenu' => 'J\'utilise Docker depuis 2 ans et je ne retournerais plus en arrière. Compose c\'est la vie !'],
            ['post' => 1, 'user' => 14, 'contenu' => 'Tu peux faire un atelier sur docker swarm aussi ? Je galère avec le clustering.'],
            ['post' => 1, 'user' => 7,  'contenu' => 'La différence Docker/Kubernetes m\'a toujours semblé floue, ce serait cool d\'avoir une session là-dessus.'],
            // Post 2 — Anglais
            ['post' => 2, 'user' => 9,  'contenu' => 'Tip génial ! J\'avais jamais pensé à ralentir la vitesse des podcasts. Je teste ce soir.'],
            ['post' => 2, 'user' => 17, 'contenu' => 'La même technique marche pour apprendre l\'arabe avec des émissions Al Jazeera !'],
            ['post' => 2, 'user' => 37, 'contenu' => 'Pour l\'espagnol c\'est pareil ! Les podcasts Radio Ambulante en demi-vitesse sont parfaits.'],
            // Post 3 — React
            ['post' => 3, 'user' => 0,  'contenu' => 'Oui ! Je cherche justement à apprendre React. On organise un groupe d\'étude ?'],
            ['post' => 3, 'user' => 7,  'contenu' => 'Je suis partant pour co-apprendre. J\'ai les bases JS mais jamais touché un framework.'],
            ['post' => 3, 'user' => 22, 'contenu' => 'Moi aussi je débute en JS, je serais partant pour un groupe d\'entraide !'],
            // Post 4 — Maths
            ['post' => 4, 'user' => 0,  'contenu' => 'C\'est exactement la métaphore que j\'utilise pour expliquer les transformations linéaires !'],
            ['post' => 4, 'user' => 18, 'contenu' => 'En bioinformatique on utilise beaucoup l\'algèbre linéaire pour l\'ACP. Tu fais des sessions dessus ?'],
            ['post' => 4, 'user' => 39, 'contenu' => 'La décomposition SVD est aussi centrale en finance quantitative. Bonne vulgarisation !'],
            // Post 5 — Cybersécurité
            ['post' => 5, 'user' => 2,  'contenu' => 'Les injections SQL c\'est encore trop fréquent dans les audits. Tu partages tes writeups ?'],
            ['post' => 5, 'user' => 13, 'contenu' => 'D\'un point de vue RGPD, une fuite due à une SQLi peut coûter jusqu\'à 4% du CA mondial.'],
            ['post' => 5, 'user' => 30, 'contenu' => 'Pensez aussi aux ORM avec le mode strict. SQLAlchemy et Doctrine protègent bien si bien configurés.'],
            // Post 6 — Figma
            ['post' => 6, 'user' => 11, 'contenu' => 'Les variables Figma c\'est révolutionnaire pour gérer les thèmes dark/light !'],
            ['post' => 6, 'user' => 4,  'contenu' => 'Je commence Figma cette semaine. Des ressources pour débutantes que tu recommandes ?'],
            // Post 7 — Python David
            ['post' => 7, 'user' => 0,  'contenu' => 'Avec plaisir David ! Tu es motivé, ça se voit. N\'hésite pas à réserver une deuxième session.'],
            ['post' => 7, 'user' => 23, 'contenu' => 'La plateforme est géniale pour ça. J\'ai aussi trouvé quelqu\'un pour m\'aider sur Spark !'],
            // Post 8 — Éditeur
            ['post' => 8, 'user' => 0,  'contenu' => 'VSCode avec Python extension, Pylance, et Black formatter. Impossible de revenir en arrière !'],
            ['post' => 8, 'user' => 2,  'contenu' => 'Neovim all the way. Une fois configuré c\'est imbattable.'],
            ['post' => 8, 'user' => 34, 'contenu' => 'IntelliJ IDEA pour Java/Kotlin. JetBrains a pas son pareil pour les gros projets.'],
            // Post 9 — AutoCAD
            ['post' => 9, 'user' => 32, 'contenu' => 'J\'utilise les blocs dynamiques dans Revit aussi ! La logique est similaire. Tu as testé Revit ?'],
            ['post' => 9, 'user' => 2,  'contenu' => 'Tu as des équivalents libres à proposer ? Je pense à FreeCAD pour les étudiants sans licences.'],
            // Post 10 — PyTorch
            ['post' => 10, 'user' => 0,  'contenu' => 'Tu as testé avec des modèles de vision ? Je travaille sur de la détection d\'objets.'],
            ['post' => 10, 'user' => 35, 'contenu' => 'torch.compile() est impressionnant sur les transformers. Tu as essayé avec FlashAttention 2 ?'],
            ['post' => 10, 'user' => 23, 'contenu' => 'Et pour le déploiement en production vous utilisez quoi ? TorchServe ou ONNX ?'],
            // Post 11 — Canva
            ['post' => 11, 'user' => 1,  'contenu' => 'Canva c\'est bien pour débuter mais pour les projets sérieux Figma ou Illustrator sont plus flexibles.'],
            ['post' => 11, 'user' => 25, 'contenu' => 'Pour le marketing de contenu Canva est imbattable. Je l\'utilise pour tous mes contenus réseaux !'],
            // Post 12 — Vulgarisation
            ['post' => 12, 'user' => 10, 'contenu' => 'La métaphore est parfaite ! J\'utilise aussi des analogies géographiques pour l\'espace de paramètres.'],
            ['post' => 12, 'user' => 21, 'contenu' => 'La pédagogie par l\'analogie est validée par les sciences cognitives. Tu fais des sessions ?'],
            ['post' => 12, 'user' => 35, 'contenu' => 'Je cherche quelqu\'un comme toi pour expliquer l\'IA à mes étudiants non-techniciens !'],
            // Post 13 — RGPD
            ['post' => 13, 'user' => 5,  'contenu' => 'Notre startup a justement besoin de cet audit. Tu peux aussi revoir nos CGU ?'],
            ['post' => 13, 'user' => 8,  'contenu' => 'RGPD + sécurité = combo gagnant. On devrait collaborer sur une session commune !'],
            // Post 14 — SEO
            ['post' => 14, 'user' => 11, 'contenu' => 'Est-ce que le CLS s\'améliore si on pré-dimensionne les images en CSS ?'],
            ['post' => 14, 'user' => 5,  'contenu' => 'Pour le LCP j\'ai passé du SSR avec Next.js et ça a tout changé.'],
            // Post 15 — Unity
            ['post' => 15, 'user' => 16, 'contenu' => 'J\'adorerais voir le projet ! Je débute Unity et les particules me font encore peur.'],
            ['post' => 15, 'user' => 5,  'contenu' => 'Le lien entre Unity et le web me fascine. Tu as regardé WebGL export ?'],
            // Post 16 — Arabe
            ['post' => 16, 'user' => 9,  'contenu' => 'Je veux apprendre l\'arabe pour mon stage au Maroc. Tu peux adapter pour débutant absolu ?'],
            ['post' => 16, 'user' => 4,  'contenu' => 'Je parle le darija mais pas l\'arabe classique. C\'est très différent ?'],
            // Post 17 — Bioinformatique
            ['post' => 17, 'user' => 10, 'contenu' => 'Fascinant ! L\'IA appliquée à la génomique est un des domaines les plus prometteurs. AlphaFold a tout changé.'],
            ['post' => 17, 'user' => 27, 'contenu' => 'En chimie computationnelle on fait pareil pour les protéines. On devrait co-organiser une session !'],
            // Post 18 — Blockchain
            ['post' => 18, 'user' => 13, 'contenu' => 'D\'un point de vue juridique, les smart contracts soulèvent des questions fascinantes sur la responsabilité.'],
            ['post' => 18, 'user' => 39, 'contenu' => 'La DeFi est intéressante mais les risques réglementaires sont énormes. Prudence pour les investisseurs.'],
            // Post 19 — Pandas
            ['post' => 19, 'user' => 10, 'contenu' => 'ydata-profiling (ancien pandas-profiling) c\'est excellent ! J\'ajoute aussi sweetviz pour comparer des datasets.'],
            ['post' => 19, 'user' => 23, 'contenu' => 'Pour des datasets vraiment gros, Polars est beaucoup plus rapide que pandas. Tu as testé ?'],
            // Post 20 — Terraform
            ['post' => 20, 'user' => 2,  'contenu' => 'Terraform c\'est la vie ! Tu utilises des modules réutilisables ou tout en monolithique ?'],
            ['post' => 20, 'user' => 34, 'contenu' => 'Attention aux state files en équipe. Remote state sur S3 + DynamoDB pour le locking c\'est indispensable.'],
            ['post' => 20, 'user' => 5,  'contenu' => 'Question : Terraform Cloud vs self-hosted GitLab CI pour le plan/apply ?'],
            // Post 21 — Pomodoro
            ['post' => 21, 'user' => 7,  'contenu' => 'Très intéressant ! Je suis dans le flow quand je code, le Pomodoro casse tout. Tu recommandes quoi ?'],
            ['post' => 21, 'user' => 12, 'contenu' => 'La philosophie du "deep work" de Cal Newport est complémentaire. Tu connais ?'],
            // Post 22 — Kafka
            ['post' => 22, 'user' => 20, 'contenu' => 'Bonne métaphore ! J\'ajouterais : les consumer groups = plusieurs équipes de caissiers travaillant en parallèle.'],
            ['post' => 22, 'user' => 34, 'contenu' => 'On utilise Kafka pour notre pipeline de logs. 50M events/jour sans problème. Quel broker tu recommandes pour débuter ?'],
            // Post 23 — Arduino
            ['post' => 23, 'user' => 24, 'contenu' => 'Je partage le code ce weekend ! Il utilise l\'API OpenWeatherMap comme fallback quand le capteur BME280 déraille.'],
            ['post' => 23, 'user' => 38, 'contenu' => 'Tu as pensé à utiliser un ESP32 plutôt qu\'Arduino ? Le Wi-Fi intégré simplifie tout pour les projets IoT.'],
            // Post 24 — Community management
            ['post' => 24, 'user' => 11, 'contenu' => 'Tellement vrai ! J\'ai vu des marques se planter en répondant agressivement en public. Merci pour le rappel.'],
            ['post' => 24, 'user' => 15, 'contenu' => 'Et surtout répondez vite ! Passé 24h, l\'image de marque prend un gros coup.'],
            // Post 25 — LaTeX
            ['post' => 25, 'user' => 18, 'contenu' => 'Tellement d\'accord ! Ma thèse en LaTeX avec biblatex et siunitx, c\'était une autre dimension vs Word.'],
            ['post' => 25, 'user' => 7,  'contenu' => 'Pour les articles de maths, LaTeX n\'a aucun concurrent. La mise en forme des équations est parfaite.'],
            // Post 26 — Photographie
            ['post' => 26, 'user' => 1,  'contenu' => 'La golden hour c\'est magique mais la blue hour juste après est encore plus mystérieuse. Tu fais des sessions terrain ?'],
            ['post' => 26, 'user' => 11, 'contenu' => 'Je cherche justement à améliorer mes photos pour mes contenus réseaux. Je réserve une session !'],
            // Post 27 — DEF CON / IoT
            ['post' => 27, 'user' => 8,  'contenu' => 'Pire encore : beaucoup de caméras ont des backdoors firmware non documentées. La sécurité IoT est catastrophique.'],
            ['post' => 27, 'user' => 20, 'contenu' => 'On devrait segmenter les réseaux IoT du réseau principal. VLAN dédié + règles firewall strictes.'],
            // Post 28 — Revit
            ['post' => 28, 'user' => 9,  'contenu' => 'Super info ! On utilise encore des plugins tiers au bureau. Je vais tester nativement sur notre prochain projet.'],
            ['post' => 28, 'user' => 32, 'contenu' => 'Et la collaboration cloud BIM 360 / Autodesk Construction Cloud, vous utilisez ? Le travail en équipe est fluide.'],
            // Post 29 — Monolith first
            ['post' => 29, 'user' => 20, 'contenu' => 'Tellement d\'accord. On a fait l\'erreur du micro-service dès le départ. 6 mois perdu en complexité accidentelle.'],
            ['post' => 29, 'user' => 5,  'contenu' => '"Make it work, make it right, make it fast." Ça s\'applique aussi à l\'architecture.'],
            // Post 30 — LLM open source
            ['post' => 30, 'user' => 10, 'contenu' => 'Mistral-7B est impressionnant pour sa taille. Les quantized models permettent de tourner ça sur un laptop correct.'],
            ['post' => 30, 'user' => 0,  'contenu' => 'Ollama pour faire tourner ça en local, c\'est vraiment simple à installer. Je recommande pour débuter.'],
            // Post 31 — Musique
            ['post' => 31, 'user' => 3,  'contenu' => 'Je veux apprendre la guitare mais je pense vraiment ne pas avoir l\'oreille... Tu me donnerais espoir ?'],
            ['post' => 31, 'user' => 7,  'contenu' => 'Les mathématiques et la musique sont intimement liées. J\'adore faire le lien en cours de maths !'],
            // Post 32 — Traduction
            ['post' => 32, 'user' => 17, 'contenu' => 'Complètement d\'accord ! En arabe c\'est encore plus vrai, les structures de phrases sont si différentes du français.'],
            ['post' => 32, 'user' => 4,  'contenu' => 'Je traduis souvent des textes académiques en anglais et c\'est exactement le piège dans lequel je tombe.'],
            // Post 33 — Finance quant
            ['post' => 33, 'user' => 3,  'contenu' => 'Impressionnant ! Tu utilises quels indicateurs pour ton signal momentum ? RSI, MACD ?'],
            ['post' => 33, 'user' => 19, 'contenu' => 'La finance quantitative me fascine. Je cherche justement quelqu\'un pour m\'initier aux bases.'],
            // Post 34 — Kubernetes
            ['post' => 34, 'user' => 2,  'contenu' => 'Pour les petites équipes K3s est un bon compromis. Toute la puissance de K8s sans la complexité ops.'],
            ['post' => 34, 'user' => 34, 'contenu' => 'EKS sur AWS si vous êtes déjà dans l\'écosystème Amazon. L\'intégration IAM est native.'],
            // Post 35 — AWS Lambda
            ['post' => 35, 'user' => 20, 'contenu' => 'Provisioned Concurrency règle le cold start mais ça coûte cher. À utiliser avec discernement.'],
            ['post' => 35, 'user' => 2,  'contenu' => 'Pour les fonctions critiques on préfère les conteneurs ECS Fargate. Plus prévisible en latence.'],
            // Post 36 — Fine-tuning LLM
            ['post' => 36, 'user' => 35, 'contenu' => 'QLoRA est encore plus efficace en mémoire que LoRA standard. Tu as comparé les deux ?'],
            ['post' => 36, 'user' => 23, 'contenu' => 'Le stockage et la gestion des datasets d\'entraînement c\'est le vrai défi. Tu utilises Hugging Face Datasets ?'],
            // Post 37 — Delta Lake
            ['post' => 37, 'user' => 20, 'contenu' => 'Iceberg vs Delta Lake : tu as une préférence ? On évalue les deux pour notre lakehouse.'],
            ['post' => 37, 'user' => 10, 'contenu' => 'Pour du ML avec Feature Store, tu utilises quoi en complément ? Feast ou Tecton ?'],
            // Post 38 — RAG
            ['post' => 38, 'user' => 10, 'contenu' => 'LlamaIndex ou LangChain pour l\'orchestration RAG ? Les deux ont leurs forces selon les cas d\'usage.'],
            ['post' => 38, 'user' => 34, 'contenu' => 'On utilise RAG sur nos docs internes depuis 3 mois. Les hallucinations ont chuté de 80%.'],
            // Post 39 — Bitwarden
            ['post' => 39, 'user' => 13, 'contenu' => 'D\'un point de vue légal, utiliser "123456" comme mot de passe pro peut engager la responsabilité de l\'entreprise.'],
            ['post' => 39, 'user' => 2,  'contenu' => 'Pour les entreprises, Vaultwarden en self-hosted sur votre infra. Open source et compatible avec les clients Bitwarden.'],
        ];

        foreach ($commentsData as $cd) {
            $comment = (new Comment())
                ->setPost($posts[$cd['post']])
                ->setUser($users[$cd['user']])
                ->setContenu($cd['contenu']);
            $manager->persist($comment);
        }
    }

    private function createSessions(ObjectManager $manager, array $users): void
    {
        $sessionsData = [
            ['tuteur' => 0,  'apprenant' => 3,  'competence' => 'Python',              'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -7,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Alice explique super bien ! Les list comprehensions sont enfin claires pour moi.']],
            ['tuteur' => 0,  'apprenant' => 6,  'competence' => 'Python',              'type' => 'atelier',      'statut' => 'completee', 'days' => -14, 'duree' => 90,
             'review' => ['note' => 5, 'commentaire' => 'Excellente session sur les décorateurs. Beaucoup d\'exercices pratiques !']],
            ['tuteur' => 1,  'apprenant' => 4,  'competence' => 'Figma',               'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 3,   'duree' => 60],
            ['tuteur' => 5,  'apprenant' => 3,  'competence' => 'React',               'type' => 'atelier',      'statut' => 'proposee',  'days' => 5,   'duree' => 120],
            ['tuteur' => 4,  'apprenant' => 9,  'competence' => 'Anglais',             'type' => 'club',         'statut' => 'confirmee', 'days' => 7,   'duree' => 60],
            ['tuteur' => 2,  'apprenant' => 5,  'competence' => 'Docker',              'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -3,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Clara est une super tutrice. Très patiente et pédagogue.']],
            ['tuteur' => 7,  'apprenant' => 6,  'competence' => 'Mathématiques',       'type' => 'cours_rapide', 'statut' => 'annulee',   'days' => -10, 'duree' => 60],
            ['tuteur' => 8,  'apprenant' => 0,  'competence' => 'Cybersécurité',       'type' => 'atelier',      'statut' => 'proposee',  'days' => 10,  'duree' => 120],
            ['tuteur' => 10, 'apprenant' => 0,  'competence' => 'TensorFlow',          'type' => 'atelier',      'statut' => 'completee', 'days' => -5,  'duree' => 120,
             'review' => ['note' => 5, 'commentaire' => 'Karim maîtrise parfaitement TensorFlow. Le TP sur les CNN était très bien structuré.']],
            ['tuteur' => 0,  'apprenant' => 19, 'competence' => 'Python',              'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -2,  'duree' => 60,
             'review' => ['note' => 4, 'commentaire' => 'Bonne session d\'introduction. Alice adapte bien son niveau au débutant.']],
            ['tuteur' => 13, 'apprenant' => 5,  'competence' => 'RGPD',               'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 2,   'duree' => 60],
            ['tuteur' => 4,  'apprenant' => 16, 'competence' => 'Anglais',             'type' => 'club',         'statut' => 'confirmee', 'days' => 4,   'duree' => 90],
            ['tuteur' => 7,  'apprenant' => 10, 'competence' => 'Statistiques',        'type' => 'atelier',      'statut' => 'proposee',  'days' => 8,   'duree' => 90],
            ['tuteur' => 17, 'apprenant' => 9,  'competence' => 'Arabe',               'type' => 'cours_rapide', 'statut' => 'proposee',  'days' => 6,   'duree' => 60],
            ['tuteur' => 15, 'apprenant' => 11, 'competence' => 'SEO',                 'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -8,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Pauline est une vraie experte SEO. En 1h j\'ai appris plus qu\'en 3 mois de YouTube.']],
            ['tuteur' => 2,  'apprenant' => 14, 'competence' => 'Linux',               'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -4,  'duree' => 60,
             'review' => ['note' => 4, 'commentaire' => 'Bonne intro à Linux. Je suis enfin à l\'aise avec le terminal.']],
            ['tuteur' => 18, 'apprenant' => 10, 'competence' => 'R',                   'type' => 'atelier',      'statut' => 'confirmee', 'days' => 9,   'duree' => 120],
            ['tuteur' => 8,  'apprenant' => 14, 'competence' => 'Kali Linux',          'type' => 'atelier',      'statut' => 'proposee',  'days' => 12,  'duree' => 120],
            ['tuteur' => 12, 'apprenant' => 19, 'competence' => 'Prise de parole',     'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -6,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Marc a une pédagogie incroyable. Ma présentation de vendredi s\'est très bien passée.']],
            ['tuteur' => 1,  'apprenant' => 11, 'competence' => 'Figma',               'type' => 'atelier',      'statut' => 'completee', 'days' => -9,  'duree' => 90,
             'review' => ['note' => 4, 'commentaire' => 'Super atelier sur les composants Figma. J\'aurais aimé plus de temps sur les variables.']],
            ['tuteur' => 5,  'apprenant' => 7,  'competence' => 'Node.js',             'type' => 'cours_rapide', 'statut' => 'proposee',  'days' => 15,  'duree' => 60],
            ['tuteur' => 16, 'apprenant' => 14, 'competence' => 'Unity',               'type' => 'atelier',      'statut' => 'confirmee', 'days' => 11,  'duree' => 90],
            ['tuteur' => 3,  'apprenant' => 6,  'competence' => 'Excel',               'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -12, 'duree' => 60,
             'review' => ['note' => 3, 'commentaire' => 'Session correcte mais manquait de pratique. Les formules avancées n\'ont pas été abordées.']],

            // Nouvelles sessions
            ['tuteur' => 20, 'apprenant' => 2,  'competence' => 'Kubernetes',          'type' => 'atelier',      'statut' => 'completee', 'days' => -3,  'duree' => 120,
             'review' => ['note' => 5, 'commentaire' => 'Alexis connaît Kubernetes sur le bout des doigts. La session sur les Helm charts était parfaite.']],
            ['tuteur' => 23, 'apprenant' => 10, 'competence' => 'Apache Spark',        'type' => 'atelier',      'statut' => 'completee', 'days' => -6,  'duree' => 120,
             'review' => ['note' => 5, 'commentaire' => 'Diane m\'a expliqué Spark Streaming avec une clarté incroyable. Je comprends enfin les micro-batches !']],
            ['tuteur' => 21, 'apprenant' => 7,  'competence' => 'Méthodes de travail', 'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -4,  'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Béatrice m\'a donné des techniques concrètes pour mieux gérer mon temps de révision. Très utile !']],
            ['tuteur' => 29, 'apprenant' => 11, 'competence' => 'Photographie',        'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -5,  'duree' => 60,
             'review' => ['note' => 4, 'commentaire' => 'Julia m\'a appris à utiliser la lumière naturelle. Mes photos Instagram s\'en sont nettement améliorées.']],
            ['tuteur' => 30, 'apprenant' => 8,  'competence' => 'Pentesting',          'type' => 'atelier',      'statut' => 'completee', 'days' => -8,  'duree' => 120,
             'review' => ['note' => 5, 'commentaire' => 'Kevin est un expert impressionnant. La session sur les techniques de post-exploitation était enrichissante.']],
            ['tuteur' => 34, 'apprenant' => 5,  'competence' => 'Java',                'type' => 'atelier',      'statut' => 'completee', 'days' => -10, 'duree' => 90,
             'review' => ['note' => 5, 'commentaire' => 'Olivier a une expérience folle. En 1h30 j\'ai compris les patterns que je ratais depuis des mois.']],
            ['tuteur' => 35, 'apprenant' => 0,  'competence' => 'NLP',                 'type' => 'atelier',      'statut' => 'completee', 'days' => -2,  'duree' => 90,
             'review' => ['note' => 5, 'commentaire' => 'Priya m\'a initié aux transformers avec une pédagogie remarquable. Je peux maintenant fine-tuner mes propres modèles.']],
            ['tuteur' => 36, 'apprenant' => 3,  'competence' => 'Piano',               'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -7,  'duree' => 60,
             'review' => ['note' => 4, 'commentaire' => 'Raphaël est pédagogue et patient. Je joue mes premières mélodies après seulement 2 sessions !']],
            ['tuteur' => 39, 'apprenant' => 19, 'competence' => 'Finance quantitative', 'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -9, 'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Victor a rendu la finance quant accessible avec des exemples concrets en Python. Très bonne session.']],
            ['tuteur' => 27, 'apprenant' => 18, 'competence' => 'LaTeX',               'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -11, 'duree' => 60,
             'review' => ['note' => 5, 'commentaire' => 'Héloïse m\'a sauvé la vie pour ma thèse. La mise en forme automatique de biblatex est enfin maîtrisée.']],
            ['tuteur' => 20, 'apprenant' => 14, 'competence' => 'Terraform',           'type' => 'cours_rapide', 'statut' => 'proposee',  'days' => 4,   'duree' => 60],
            ['tuteur' => 37, 'apprenant' => 4,  'competence' => 'Espagnol',            'type' => 'club',         'statut' => 'confirmee', 'days' => 6,   'duree' => 90],
            ['tuteur' => 32, 'apprenant' => 9,  'competence' => 'Revit',               'type' => 'atelier',      'statut' => 'proposee',  'days' => 8,   'duree' => 120],
            ['tuteur' => 35, 'apprenant' => 12, 'competence' => 'NLP',                 'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 3,   'duree' => 60],
            ['tuteur' => 23, 'apprenant' => 5,  'competence' => 'Kafka',               'type' => 'atelier',      'statut' => 'proposee',  'days' => 7,   'duree' => 90],
            ['tuteur' => 30, 'apprenant' => 22, 'competence' => 'Cybersécurité',       'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 5,   'duree' => 60],
            ['tuteur' => 29, 'apprenant' => 25, 'competence' => 'Lightroom',           'type' => 'cours_rapide', 'statut' => 'proposee',  'days' => 9,   'duree' => 60],
            ['tuteur' => 39, 'apprenant' => 28, 'competence' => 'Python',              'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 2,   'duree' => 60],
            ['tuteur' => 17, 'apprenant' => 26, 'competence' => 'Arabe',               'type' => 'cours_rapide', 'statut' => 'proposee',  'days' => 10,  'duree' => 60],
            ['tuteur' => 21, 'apprenant' => 38, 'competence' => 'Gestion du stress',   'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 4,   'duree' => 60],
            ['tuteur' => 36, 'apprenant' => 31, 'competence' => 'MAO',                 'type' => 'atelier',      'statut' => 'proposee',  'days' => 13,  'duree' => 90],
            ['tuteur' => 34, 'apprenant' => 22, 'competence' => 'Java',                'type' => 'cours_rapide', 'statut' => 'proposee',  'days' => 11,  'duree' => 60],
            ['tuteur' => 25, 'apprenant' => 33, 'competence' => 'Community management','type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 6,   'duree' => 60],
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
                    $review = (new Review())
                        ->setSession($session)
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
        // Badge Fondateur pour tous
        foreach ($users as $user) {
            $manager->persist((new UserBadge())->setUser($user)->setBadge($badges[2]));
        }

        // Badge Premier pas
        foreach ([0, 1, 2, 3, 4, 5, 7, 8, 10, 12, 15, 16, 17, 18, 19, 20, 21, 23, 27, 29, 30, 34, 35, 36, 39] as $idx) {
            if (!isset($users[$idx])) continue;
            $manager->persist((new UserBadge())->setUser($users[$idx])->setBadge($badges[0]));
        }

        // Badge Mentor confirmé
        foreach ([0, 2, 20, 34] as $idx) {
            if (!isset($users[$idx])) continue;
            $manager->persist((new UserBadge())->setUser($users[$idx])->setBadge($badges[1]));
        }

        // Badge Expert partage
        foreach ([2, 34] as $idx) {
            if (!isset($users[$idx])) continue;
            $manager->persist((new UserBadge())->setUser($users[$idx])->setBadge($badges[3]));
        }

        // Badge Fidèle
        foreach ([2, 10, 20, 23, 34, 35] as $idx) {
            if (!isset($users[$idx])) continue;
            $manager->persist((new UserBadge())->setUser($users[$idx])->setBadge($badges[4]));
        }

        // Badge Curieux
        foreach ([0, 4, 5, 8, 17, 29, 37] as $idx) {
            if (!isset($users[$idx])) continue;
            $manager->persist((new UserBadge())->setUser($users[$idx])->setBadge($badges[5]));
        }

        // Badge Influenceur
        foreach ([0, 2, 8, 10, 23, 35] as $idx) {
            if (!isset($users[$idx])) continue;
            $manager->persist((new UserBadge())->setUser($users[$idx])->setBadge($badges[6]));
        }

        // Badge Top Évaluateur
        foreach ([10, 18, 20, 34, 35] as $idx) {
            if (!isset($users[$idx])) continue;
            $manager->persist((new UserBadge())->setUser($users[$idx])->setBadge($badges[7]));
        }
    }
}