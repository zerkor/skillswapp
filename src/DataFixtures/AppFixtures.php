<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Availability;
use App\Entity\Badge;
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
 * Fixtures de démonstration — 10 utilisateurs, sessions, posts, badges.
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
        $this->createSessions($manager, $users);
        $this->createPosts($manager, $users);
        $this->assignBadges($manager, $users, $badges);

        $manager->flush();
    }

    /** @return Badge[] */
    private function createBadges(ObjectManager $manager): array
    {
        $data = [
            ['nom' => 'Premier pas',      'description' => 'Complétez votre première session',          'icone' => '👣', 'conditionType' => 'first_session',            'conditionValue' => 1],
            ['nom' => 'Mentor confirmé',  'description' => '5 sessions en tant que tuteur',             'icone' => '🎓', 'conditionType' => 'sessions_completed_tutor',  'conditionValue' => 5],
            ['nom' => 'Fondateur',        'description' => 'Parmi les 50 premiers inscrits',            'icone' => '🏛️', 'conditionType' => 'registered_early',           'conditionValue' => 1],
            ['nom' => 'Expert partage',   'description' => '10 sessions d\'enseignement complétées',   'icone' => '🔬', 'conditionType' => 'sessions_completed_tutor',  'conditionValue' => 10],
            ['nom' => 'Fidèle',           'description' => '30 jours d\'activité sur la plateforme',   'icone' => '🌟', 'conditionType' => 'registered_early',           'conditionValue' => 1],
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
            ['prenom' => 'Alice',    'nom' => 'Martin',    'email' => 'alice@skillswap.fr',    'formation' => 'Master Informatique', 'promotion' => '2025', 'score' => 850, 'niveau' => 'expert',
             'bio' => 'Passionnée de Python et machine learning. Je propose des cours adaptés à tous niveaux.',
             'skills' => [['nom' => 'Python', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'], ['nom' => 'Machine Learning', 'type' => 'teach', 'niveau' => 3, 'cat' => 'IA'], ['nom' => 'React', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['lun', '09:00', '11:00'], ['mer', '14:00', '17:00'], ['sam', '10:00', '12:00']]],

            ['prenom' => 'Baptiste', 'nom' => 'Durand',   'email' => 'baptiste@skillswap.fr', 'formation' => 'Licence Design', 'promotion' => '2026', 'score' => 320, 'niveau' => 'mentor',
             'bio' => 'Designer UI/UX depuis 3 ans. Figma et Adobe sont mes outils du quotidien.',
             'skills' => [['nom' => 'Figma', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Design'], ['nom' => 'Photoshop', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation']],
             'avails' => [['mar', '13:00', '15:00'], ['jeu', '10:00', '12:00']]],

            ['prenom' => 'Clara',    'nom' => 'Lefebvre',  'email' => 'clara@skillswap.fr',    'formation' => 'DUT Réseaux', 'promotion' => '2025', 'score' => 1650, 'niveau' => 'legende',
             'bio' => 'Admin sys et réseau. Certifiée Cisco CCNA. Adepte du libre.',
             'skills' => [['nom' => 'Linux', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Systèmes'], ['nom' => 'Cisco', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Réseaux'], ['nom' => 'Docker', 'type' => 'teach', 'niveau' => 3, 'cat' => 'DevOps'], ['nom' => 'Kubernetes', 'type' => 'learn', 'niveau' => 2, 'cat' => 'DevOps']],
             'avails' => [['lun', '18:00', '20:00'], ['mer', '18:00', '20:00'], ['ven', '14:00', '16:00']]],

            ['prenom' => 'David',    'nom' => 'Bernard',   'email' => 'david@skillswap.fr',    'formation' => 'Master Finance', 'promotion' => '2025', 'score' => 145, 'niveau' => 'apprenti',
             'bio' => 'Passionné d\'économie et de finance. Je cherche à renforcer mes compétences tech.',
             'skills' => [['nom' => 'Excel', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Bureautique'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'SQL', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Base de données']],
             'avails' => [['mar', '12:00', '14:00'], ['jeu', '16:00', '18:00']]],

            ['prenom' => 'Emma',     'nom' => 'Petit',     'email' => 'emma@skillswap.fr',     'formation' => 'Licence Anglais', 'promotion' => '2026', 'score' => 210, 'niveau' => 'mentor',
             'bio' => 'Bilingue anglais-français. Cours de conversation et rédaction académique.',
             'skills' => [['nom' => 'Anglais', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'], ['nom' => 'Espagnol', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Langues'], ['nom' => 'Photoshop', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Design']],
             'avails' => [['lun', '14:00', '16:00'], ['mer', '09:00', '11:00'], ['sam', '14:00', '16:00']]],

            ['prenom' => 'Florian',  'nom' => 'Thomas',    'email' => 'florian@skillswap.fr',  'formation' => 'Master Dev Web', 'promotion' => '2025', 'score' => 560, 'niveau' => 'mentor',
             'bio' => 'Full-stack JS. Je maîtrise React, Node.js, et les bases SQL/NoSQL.',
             'skills' => [['nom' => 'React', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Web'], ['nom' => 'Node.js', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Web'], ['nom' => 'Docker', 'type' => 'learn', 'niveau' => 2, 'cat' => 'DevOps']],
             'avails' => [['lun', '19:00', '21:00'], ['ven', '10:00', '12:00']]],

            ['prenom' => 'Gaëlle',   'nom' => 'Roux',      'email' => 'gaelle@skillswap.fr',   'formation' => 'BTS Comptabilité', 'promotion' => '2026', 'score' => 45, 'niveau' => 'novice',
             'bio' => 'Débutante en informatique. Je veux apprendre à coder !',
             'skills' => [['nom' => 'Comptabilité', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Gestion'], ['nom' => 'Python', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'], ['nom' => 'Excel', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Bureautique']],
             'avails' => [['mar', '17:00', '19:00'], ['dim', '10:00', '12:00']]],

            ['prenom' => 'Hugo',     'nom' => 'Moreau',    'email' => 'hugo@skillswap.fr',     'formation' => 'Licence Maths', 'promotion' => '2025', 'score' => 400, 'niveau' => 'mentor',
             'bio' => 'Matheux passionné. Spécialiste en algèbre linéaire et statistiques.',
             'skills' => [['nom' => 'Mathématiques', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'Statistiques', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'], ['nom' => 'R', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Programmation'], ['nom' => 'React', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Web']],
             'avails' => [['mer', '10:00', '12:00'], ['sam', '09:00', '11:00']]],

            ['prenom' => 'Inès',     'nom' => 'Simon',     'email' => 'ines@skillswap.fr',     'formation' => 'Master Cybersécurité', 'promotion' => '2025', 'score' => 720, 'niveau' => 'expert',
             'bio' => 'Passionnée de sécu, CTF et pentesting. Certifiée CEH.',
             'skills' => [['nom' => 'Cybersécurité', 'type' => 'teach', 'niveau' => 4, 'cat' => 'Sécurité'], ['nom' => 'Kali Linux', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Sécurité'], ['nom' => 'SQL', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Base de données']],
             'avails' => [['jeu', '20:00', '22:00'], ['sam', '15:00', '17:00']]],

            ['prenom' => 'Julien',   'nom' => 'Lemaire',   'email' => 'julien@skillswap.fr',   'formation' => 'DUT Génie Civil', 'promotion' => '2026', 'score' => 80, 'niveau' => 'novice',
             'bio' => 'Futur ingénieur. Cherche à améliorer mon anglais et mes compétences en gestion de projet.',
             'skills' => [['nom' => 'AutoCAD', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Ingénierie'], ['nom' => 'Anglais', 'type' => 'learn', 'niveau' => 2, 'cat' => 'Langues'], ['nom' => 'Gestion de projet', 'type' => 'learn', 'niveau' => 1, 'cat' => 'Management']],
             'avails' => [['lun', '12:00', '14:00'], ['mer', '12:00', '14:00']]],
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

    private function createSessions(ObjectManager $manager, array $users): void
    {
        $sessionsData = [
            ['tuteur' => 0, 'apprenant' => 3, 'competence' => 'Python', 'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -7],
            ['tuteur' => 0, 'apprenant' => 6, 'competence' => 'Python', 'type' => 'atelier',      'statut' => 'completee', 'days' => -14],
            ['tuteur' => 1, 'apprenant' => 4, 'competence' => 'Figma',  'type' => 'cours_rapide', 'statut' => 'confirmee', 'days' => 3],
            ['tuteur' => 5, 'apprenant' => 3, 'competence' => 'React',  'type' => 'atelier',      'statut' => 'proposee',  'days' => 5],
            ['tuteur' => 4, 'apprenant' => 9, 'competence' => 'Anglais','type' => 'club',         'statut' => 'confirmee', 'days' => 7],
            ['tuteur' => 2, 'apprenant' => 5, 'competence' => 'Docker', 'type' => 'cours_rapide', 'statut' => 'completee', 'days' => -3],
            ['tuteur' => 7, 'apprenant' => 6, 'competence' => 'Maths',  'type' => 'cours_rapide', 'statut' => 'annulee',   'days' => -10],
            ['tuteur' => 8, 'apprenant' => 0, 'competence' => 'Cybersécurité', 'type' => 'atelier', 'statut' => 'proposee', 'days' => 10],
        ];

        foreach ($sessionsData as $sd) {
            $session = new Session();
            $session->setTuteur($users[$sd['tuteur']])
                    ->setApprenant($users[$sd['apprenant']])
                    ->setCompetence($sd['competence'])
                    ->setType($sd['type'])
                    ->setStatut($sd['statut'])
                    ->setDate((new \DateTime())->modify("{$sd['days']} days"))
                    ->setDureeMinutes(60)
                    ->setLieuOuLien($sd['statut'] === 'completee' ? 'Salle informatique B214' : 'https://meet.google.com/abc-def-ghi');

            if ($sd['statut'] === 'completee') {
                $session->setTuteurCompleted(true)->setApprenantCompleted(true);

                $review = new Review();
                $review->setSession($session)
                       ->setAuteur($users[$sd['apprenant']])
                       ->setNote(rand(4, 5))
                       ->setCommentaire('Super session, très pédagogue !');
                $manager->persist($review);
            }

            $manager->persist($session);
        }
    }

    private function createPosts(ObjectManager $manager, array $users): void
    {
        $postsData = [
            ['user' => 0, 'contenu' => "Venez de finir mon cours Python sur les décorateurs. Résultat : mon code est maintenant 3x plus lisible ! 🐍 Qui veut que je lui explique le concept ?", 'tag' => 'Python'],
            ['user' => 2, 'contenu' => "Docker + Compose = la combo parfaite pour isoler vos environnements de dev. Fini les \"ça marche chez moi\" ! J'organise un atelier la semaine prochaine.", 'tag' => 'Docker'],
            ['user' => 4, 'contenu' => "Petite astuce anglais : pour améliorer son accent, écoutez des podcasts en demi-vitesse sur Spotify. Ça change tout ! 🎧", 'tag' => 'Anglais'],
            ['user' => 5, 'contenu' => "React 19 est sorti ! Les Server Components changent vraiment la donne. Quelqu'un veut co-apprendre les nouvelles features ensemble ?", 'tag' => 'React'],
            ['user' => 7, 'contenu' => "Tip maths : pour visualiser les matrices, pensez à elles comme des transformations dans l'espace. Ça rend l'algèbre linéaire tellement plus intuitive !", 'tag' => 'Mathématiques'],
            ['user' => 8, 'contenu' => "CTF de la semaine résolu ! La faille était une injection SQL pas très bien cachée... Rappel : TOUJOURS utiliser des requêtes préparées 🔒", 'tag' => 'Cybersécurité'],
            ['user' => 1, 'contenu' => "Nouvelle ressource Figma : les variables de design sont enfin disponibles pour tous ! Le design system devient tellement plus maintenable.", 'tag' => 'Figma'],
            ['user' => 3, 'contenu' => "Première session SkillSwap terminée avec Alice sur Python. En 1h j'ai compris les list comprehensions. Merci la plateforme ! 🚀", 'tag' => 'Python'],
            ['user' => 6, 'contenu' => "Question pour les codeurs : vous utilisez quoi comme éditeur ? VSCode avec Vim keybindings ici, mais curieuse de découvrir autre chose.", 'tag' => null],
            ['user' => 9, 'contenu' => "AutoCAD tip : les blocs dynamiques permettent de créer des composants réutilisables. Indispensable pour les plans d'architecture !", 'tag' => 'AutoCAD'],
        ];

        foreach ($postsData as $pd) {
            $post = new Post();
            $post->setUser($users[$pd['user']])
                 ->setContenu($pd['contenu'])
                 ->setCompetenceTag($pd['tag']);

            // Quelques likes aléatoires
            $likers = array_rand($users, rand(1, 5));
            if (!is_array($likers)) $likers = [$likers];
            foreach ($likers as $li) {
                if ($li !== $pd['user']) {
                    $post->addLike($users[$li]);
                }
            }

            $manager->persist($post);
        }
    }

    private function assignBadges(ObjectManager $manager, array $users, array $badges): void
    {
        // Badge Fondateur pour les 10 premiers (tous nos fixtures)
        foreach ($users as $user) {
            $ub = new UserBadge();
            $ub->setUser($user)->setBadge($badges[2]); // Fondateur
            $manager->persist($ub);
        }

        // Badge Premier pas pour ceux avec des sessions complétées
        foreach ([0, 1, 2, 4, 5, 7, 8] as $idx) {
            if (!isset($users[$idx])) continue;
            $ub = new UserBadge();
            $ub->setUser($users[$idx])->setBadge($badges[0]);
            $manager->persist($ub);
        }

        // Badge Mentor confirmé pour Alice (5 sessions tuteur simulées)
        $ub = new UserBadge();
        $ub->setUser($users[0])->setBadge($badges[1]);
        $manager->persist($ub);

        // Badge Légende (Fidèle) pour Clara
        $ub = new UserBadge();
        $ub->setUser($users[2])->setBadge($badges[4]);
        $manager->persist($ub);
    }
}
