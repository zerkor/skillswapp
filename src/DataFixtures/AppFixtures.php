<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Availability;
use App\Entity\Badge;
use App\Entity\Education;
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
 * Fixtures v1.1 — données de démo cohérentes.
 *
 * Corrections v1.1 :
 *  - Séparation compétences (savoir-faire) / formations (diplômes)
 *  - Ajout pseudonymes sur tous les utilisateurs
 *  - Badges avec condition_label pour les tooltips
 *  - Suppression des faux diplômes dans les compétences
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
            [
                'nom'            => 'Premier pas',
                'description'    => 'Vous avez réalisé votre première session !',
                'conditionLabel' => 'Compléter 1 session (tuteur ou apprenant)',
                'icone'          => '🎯',
                'conditionType'  => 'first_session',
                'conditionValue' => 1,
            ],
            [
                'nom'            => 'Mentor Bronze',
                'description'    => 'Vous avez aidé 5 étudiants avec succès.',
                'conditionLabel' => 'Compléter 5 sessions en tant que tuteur',
                'icone'          => '🏅',
                'conditionType'  => 'sessions_completed_tutor',
                'conditionValue' => 5,
            ],
            [
                'nom'            => 'Mentor Argent',
                'description'    => 'Vous avez aidé 25 étudiants.',
                'conditionLabel' => 'Compléter 25 sessions en tant que tuteur',
                'icone'          => '🥈',
                'conditionType'  => 'sessions_completed_tutor',
                'conditionValue' => 25,
            ],
            [
                'nom'            => 'Mentor Or',
                'description'    => 'Vous avez aidé 50 étudiants.',
                'conditionLabel' => 'Compléter 50 sessions en tant que tuteur',
                'icone'          => '🥇',
                'conditionType'  => 'sessions_completed_tutor',
                'conditionValue' => 50,
            ],
            [
                'nom'            => 'Expert partage',
                'description'    => 'Vous partagez activement vos savoirs.',
                'conditionLabel' => 'Enseigner 10 compétences différentes',
                'icone'          => '🌟',
                'conditionType'  => 'sessions_completed_tutor',
                'conditionValue' => 10,
            ],
            [
                'nom'            => 'Fondateur',
                'description'    => 'Vous faites partie des fondateurs de SkillSwap.',
                'conditionLabel' => 'Inscription parmi les 50 premiers membres',
                'icone'          => '🚀',
                'conditionType'  => 'registered_early',
                'conditionValue' => 1,
            ],
            [
                'nom'            => 'Profil complet',
                'description'    => 'Votre profil est complet et attrayant.',
                'conditionLabel' => 'Renseigner bio, formation, photo et au moins 2 compétences',
                'icone'          => '✅',
                'conditionType'  => 'profile_completed',
                'conditionValue' => 1,
            ],
        ];

        $badges = [];
        foreach ($data as $d) {
            $badge = new Badge();
            $badge->setNom($d['nom'])
                  ->setDescription($d['description'])
                  ->setConditionLabel($d['conditionLabel'])
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
        /*
         * CORRECTION v1.1 : compétences = savoir-faire concrets uniquement.
         * Les diplômes/formations sont dans la section Education séparée.
         */
        $usersData = [
            [
                'pseudo' => 'AliceM',
                'prenom' => 'Alice', 'nom' => 'Martin',
                'email' => 'alice@skillswap.fr',
                'bio'   => 'Passionnée de Python et machine learning. Je propose des cours adaptés à tous niveaux.',
                'score' => 850, 'niveau' => 'expert',
                'formation' => 'Master Informatique', 'promotion' => '2025',
                'educations' => [
                    ['diplome' => 'Master Informatique — spé. IA', 'etablissement' => 'Université Lyon 1', 'niveau' => 'bac+5', 'annee' => '2025'],
                ],
                'skills' => [
                    ['nom' => 'Python',          'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'],
                    ['nom' => 'Machine Learning', 'type' => 'teach', 'niveau' => 3, 'cat' => 'IA / Data'],
                    ['nom' => 'SQL',              'type' => 'teach', 'niveau' => 3, 'cat' => 'Bases de données'],
                    ['nom' => 'React',            'type' => 'learn', 'niveau' => 1, 'cat' => 'Web'],
                ],
                'avails' => [['lun', '09:00', '11:00'], ['mer', '14:00', '17:00'], ['sam', '10:00', '12:00']],
            ],
            [
                'pseudo' => 'BaptDsgn',
                'prenom' => 'Baptiste', 'nom' => 'Durand',
                'email' => 'baptiste@skillswap.fr',
                'bio'   => 'Designer UI/UX depuis 3 ans. Figma et Adobe sont mes outils du quotidien.',
                'score' => 320, 'niveau' => 'mentor',
                'formation' => 'Licence Design', 'promotion' => '2026',
                'educations' => [
                    ['diplome' => 'Licence Design graphique', 'etablissement' => 'École Boulle', 'niveau' => 'bac+3', 'annee' => '2026'],
                ],
                'skills' => [
                    ['nom' => 'Figma',       'type' => 'teach', 'niveau' => 4, 'cat' => 'Design'],
                    ['nom' => 'Photoshop',   'type' => 'teach', 'niveau' => 3, 'cat' => 'Design'],
                    ['nom' => 'Illustrator', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Design'],
                    ['nom' => 'Python',      'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'],
                ],
                'avails' => [['mar', '13:00', '15:00'], ['jeu', '10:00', '12:00']],
            ],
            [
                'pseudo' => 'ClaraNet',
                'prenom' => 'Clara', 'nom' => 'Lefebvre',
                'email' => 'clara@skillswap.fr',
                'bio'   => 'Admin sys et réseau. Certifiée Cisco CCNA. Adepte du libre.',
                'score' => 1650, 'niveau' => 'legende',
                'formation' => 'DUT Réseaux', 'promotion' => '2025',
                'educations' => [
                    ['diplome' => 'BUT Réseaux et Télécommunications', 'etablissement' => 'IUT Grenoble', 'niveau' => 'bac+3', 'annee' => '2025'],
                    ['diplome' => 'Certification Cisco CCNA', 'etablissement' => 'Cisco', 'niveau' => null, 'annee' => '2024'],
                ],
                'skills' => [
                    ['nom' => 'Linux',          'type' => 'teach', 'niveau' => 4, 'cat' => 'Systèmes'],
                    ['nom' => 'Réseaux Cisco',   'type' => 'teach', 'niveau' => 4, 'cat' => 'Réseaux'],
                    ['nom' => 'Docker',          'type' => 'teach', 'niveau' => 3, 'cat' => 'DevOps'],
                    ['nom' => 'Bash / Shell',    'type' => 'teach', 'niveau' => 4, 'cat' => 'Systèmes'],
                    ['nom' => 'Kubernetes',      'type' => 'learn', 'niveau' => 2, 'cat' => 'DevOps'],
                ],
                'avails' => [['lun', '18:00', '20:00'], ['mer', '18:00', '20:00'], ['ven', '14:00', '16:00']],
            ],
            [
                'pseudo' => 'DavidFin',
                'prenom' => 'David', 'nom' => 'Bernard',
                'email' => 'david@skillswap.fr',
                'bio'   => 'Passionné d\'économie et de finance. Je cherche à renforcer mes compétences tech.',
                'score' => 145, 'niveau' => 'apprenti',
                'formation' => 'Master Finance', 'promotion' => '2025',
                'educations' => [
                    ['diplome' => 'Master Finance d\'entreprise', 'etablissement' => 'Paris Dauphine', 'niveau' => 'bac+5', 'annee' => '2025'],
                ],
                'skills' => [
                    ['nom' => 'Excel avancé',      'type' => 'teach', 'niveau' => 4, 'cat' => 'Bureautique'],
                    ['nom' => 'Comptabilité',       'type' => 'teach', 'niveau' => 3, 'cat' => 'Finance'],
                    ['nom' => 'Python',             'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'],
                    ['nom' => 'SQL',                'type' => 'learn', 'niveau' => 2, 'cat' => 'Bases de données'],
                ],
                'avails' => [['mar', '12:00', '14:00'], ['jeu', '16:00', '18:00']],
            ],
            [
                'pseudo' => 'EmmaLang',
                'prenom' => 'Emma', 'nom' => 'Petit',
                'email' => 'emma@skillswap.fr',
                'bio'   => 'Bilingue anglais-français. Cours de conversation et rédaction académique.',
                'score' => 210, 'niveau' => 'mentor',
                'formation' => 'Licence Anglais', 'promotion' => '2026',
                'educations' => [
                    ['diplome' => 'Licence LEA Anglais-Espagnol', 'etablissement' => 'Université Bordeaux', 'niveau' => 'bac+3', 'annee' => '2026'],
                ],
                'skills' => [
                    ['nom' => 'Anglais B2/C1',    'type' => 'teach', 'niveau' => 4, 'cat' => 'Langues'],
                    ['nom' => 'Espagnol B1',       'type' => 'teach', 'niveau' => 2, 'cat' => 'Langues'],
                    ['nom' => 'Rédaction académique', 'type' => 'teach', 'niveau' => 3, 'cat' => 'Communication'],
                    ['nom' => 'Photoshop',         'type' => 'learn', 'niveau' => 1, 'cat' => 'Design'],
                ],
                'avails' => [['lun', '14:00', '16:00'], ['mer', '09:00', '11:00'], ['sam', '14:00', '16:00']],
            ],
            [
                'pseudo' => 'FlorianJS',
                'prenom' => 'Florian', 'nom' => 'Thomas',
                'email' => 'florian@skillswap.fr',
                'bio'   => 'Full-stack JS. Je maîtrise React, Node.js, et les bases SQL/NoSQL.',
                'score' => 560, 'niveau' => 'mentor',
                'formation' => 'Master Dev Web', 'promotion' => '2025',
                'educations' => [
                    ['diplome' => 'Master Développement Web Full-Stack', 'etablissement' => 'INSA Lyon', 'niveau' => 'bac+5', 'annee' => '2025'],
                ],
                'skills' => [
                    ['nom' => 'React',       'type' => 'teach', 'niveau' => 4, 'cat' => 'Web'],
                    ['nom' => 'Node.js',     'type' => 'teach', 'niveau' => 3, 'cat' => 'Web'],
                    ['nom' => 'JavaScript',  'type' => 'teach', 'niveau' => 4, 'cat' => 'Programmation'],
                    ['nom' => 'Docker',      'type' => 'learn', 'niveau' => 2, 'cat' => 'DevOps'],
                ],
                'avails' => [['lun', '19:00', '21:00'], ['ven', '10:00', '12:00']],
            ],
            [
                'pseudo' => 'Gaelle26',
                'prenom' => 'Gaëlle', 'nom' => 'Roux',
                'email' => 'gaelle@skillswap.fr',
                'bio'   => 'Débutante en informatique. Je veux apprendre à coder !',
                'score' => 45, 'niveau' => 'novice',
                'formation' => 'BTS Comptabilité', 'promotion' => '2026',
                'educations' => [
                    ['diplome' => 'BTS Comptabilité et Gestion', 'etablissement' => 'Lycée Jean Moulin', 'niveau' => 'bac+2', 'annee' => '2026'],
                ],
                'skills' => [
                    ['nom' => 'Comptabilité',     'type' => 'teach', 'niveau' => 3, 'cat' => 'Finance'],
                    ['nom' => 'Gestion financière', 'type' => 'teach', 'niveau' => 2, 'cat' => 'Finance'],
                    ['nom' => 'Python',            'type' => 'learn', 'niveau' => 1, 'cat' => 'Programmation'],
                    ['nom' => 'Excel avancé',      'type' => 'learn', 'niveau' => 2, 'cat' => 'Bureautique'],
                ],
                'avails' => [['mar', '17:00', '19:00'], ['dim', '10:00', '12:00']],
            ],
            [
                'pseudo' => 'HugoMath',
                'prenom' => 'Hugo', 'nom' => 'Moreau',
                'email' => 'hugo@skillswap.fr',
                'bio'   => 'Matheux passionné. Spécialiste en algèbre linéaire et statistiques.',
                'score' => 400, 'niveau' => 'mentor',
                'formation' => 'Licence Maths', 'promotion' => '2025',
                'educations' => [
                    ['diplome' => 'Licence Mathématiques', 'etablissement' => 'ENS Paris', 'niveau' => 'bac+3', 'annee' => '2025'],
                ],
                'skills' => [
                    ['nom' => 'Mathématiques',  'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'],
                    ['nom' => 'Statistiques',   'type' => 'teach', 'niveau' => 4, 'cat' => 'Sciences'],
                    ['nom' => 'R (langage)',     'type' => 'teach', 'niveau' => 3, 'cat' => 'Programmation'],
                    ['nom' => 'React',           'type' => 'learn', 'niveau' => 1, 'cat' => 'Web'],
                ],
                'avails' => [['mer', '10:00', '12:00'], ['sam', '09:00', '11:00']],
            ],
            [
                'pseudo' => 'InesSec',
                'prenom' => 'Inès', 'nom' => 'Simon',
                'email' => 'ines@skillswap.fr',
                'bio'   => 'Passionnée de sécu, CTF et pentesting. Certifiée CEH.',
                'score' => 720, 'niveau' => 'expert',
                'formation' => 'Master Cybersécurité', 'promotion' => '2025',
                'educations' => [
                    ['diplome' => 'Master Cybersécurité des Systèmes', 'etablissement' => 'IMT Mines Alès', 'niveau' => 'bac+5', 'annee' => '2025'],
                    ['diplome' => 'Certification CEH (Certified Ethical Hacker)', 'etablissement' => 'EC-Council', 'niveau' => null, 'annee' => '2024'],
                ],
                'skills' => [
                    ['nom' => 'Cybersécurité',    'type' => 'teach', 'niveau' => 4, 'cat' => 'Sécurité'],
                    ['nom' => 'Kali Linux',        'type' => 'teach', 'niveau' => 3, 'cat' => 'Sécurité'],
                    ['nom' => 'SQL / Injection',   'type' => 'teach', 'niveau' => 3, 'cat' => 'Sécurité'],
                    ['nom' => 'CTF / Pentesting',  'type' => 'teach', 'niveau' => 4, 'cat' => 'Sécurité'],
                ],
                'avails' => [['jeu', '20:00', '22:00'], ['sam', '15:00', '17:00']],
            ],
            [
                'pseudo' => 'JulienGC',
                'prenom' => 'Julien', 'nom' => 'Lemaire',
                'email' => 'julien@skillswap.fr',
                'bio'   => 'Futur ingénieur. Cherche à améliorer mon anglais et mes compétences en gestion de projet.',
                'score' => 80, 'niveau' => 'novice',
                'formation' => 'DUT Génie Civil', 'promotion' => '2026',
                'educations' => [
                    ['diplome' => 'BUT Génie Civil — Construction Durable', 'etablissement' => 'IUT Rennes', 'niveau' => 'bac+3', 'annee' => '2026'],
                ],
                'skills' => [
                    ['nom' => 'AutoCAD',            'type' => 'teach', 'niveau' => 3, 'cat' => 'Ingénierie'],
                    ['nom' => 'Gestion de projet',  'type' => 'teach', 'niveau' => 2, 'cat' => 'Management'],
                    ['nom' => 'Anglais B2/C1',       'type' => 'learn', 'niveau' => 2, 'cat' => 'Langues'],
                    ['nom' => 'Méthodes Agile',      'type' => 'learn', 'niveau' => 1, 'cat' => 'Management'],
                ],
                'avails' => [['lun', '12:00', '14:00'], ['mer', '12:00', '14:00']],
            ],
        ];

        $users = [];
        $dummyUser = new User(); // pour le hashage initial
        foreach ($usersData as $data) {
            $user = new User();
            $user->setEmail($data['email'])
                 ->setNom($data['nom'])
                 ->setPrenom($data['prenom'])
                 ->setPseudo($data['pseudo'])
                 ->setFormation($data['formation'])
                 ->setPromotion($data['promotion'])
                 ->setScore($data['score'])
                 ->setNiveau($data['niveau'])
                 ->setBio($data['bio'])
                 ->setIsVerified(true)
                 ->setPassword($this->passwordHasher->hashPassword($dummyUser, 'Password123!'));

            // Formations (parcours académique)
            foreach ($data['educations'] as $ed) {
                $edu = new Education();
                $edu->setDiplome($ed['diplome'])
                    ->setEtablissement($ed['etablissement'])
                    ->setNiveau($ed['niveau'])
                    ->setAnnee($ed['annee'])
                    ->setUser($user);
                $manager->persist($edu);
            }

            // Compétences (savoir-faire concrets uniquement — v1.1)
            foreach ($data['skills'] as $sd) {
                $skill = new Skill();
                $skill->setNom($sd['nom'])
                      ->setType($sd['type'])
                      ->setNiveau($sd['niveau'])
                      ->setCategorie($sd['cat'])
                      ->setUser($user);
                $manager->persist($skill);
            }

            // Disponibilités
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
            ['tuteur' => 0, 'apprenant' => 3, 'competence' => 'Python',          'type' => 'cours_rapide', 'statut' => 'completee',  'days' => -7],
            ['tuteur' => 0, 'apprenant' => 6, 'competence' => 'Python',          'type' => 'atelier',      'statut' => 'completee',  'days' => -14],
            ['tuteur' => 1, 'apprenant' => 4, 'competence' => 'Figma',           'type' => 'cours_rapide', 'statut' => 'confirmee',  'days' => 3],
            ['tuteur' => 5, 'apprenant' => 3, 'competence' => 'React',           'type' => 'atelier',      'statut' => 'proposee',   'days' => 5],
            ['tuteur' => 4, 'apprenant' => 9, 'competence' => 'Anglais B2/C1',   'type' => 'club',         'statut' => 'confirmee',  'days' => 7],
            ['tuteur' => 2, 'apprenant' => 5, 'competence' => 'Docker',          'type' => 'cours_rapide', 'statut' => 'completee',  'days' => -3],
            ['tuteur' => 7, 'apprenant' => 6, 'competence' => 'Mathématiques',   'type' => 'cours_rapide', 'statut' => 'annulee',    'days' => -10],
            ['tuteur' => 8, 'apprenant' => 0, 'competence' => 'Cybersécurité',   'type' => 'atelier',      'statut' => 'proposee',   'days' => 10],
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
                    ->setLieuOuLien(
                        $sd['statut'] === 'completee'
                            ? 'Salle informatique B214'
                            : 'https://meet.google.com/abc-def-ghi'
                    );

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
            ['user' => 0, 'tag' => 'Python',       'contenu' => "Venez de finir mon cours Python sur les décorateurs. Résultat : mon code est maintenant 3× plus lisible ! 🐍 Qui veut que je lui explique le concept ?"],
            ['user' => 2, 'tag' => 'Docker',        'contenu' => "Docker + Compose = la combo parfaite pour isoler vos environnements de dev. Fini les \"ça marche chez moi\" ! J'organise un atelier la semaine prochaine."],
            ['user' => 4, 'tag' => 'Anglais B2/C1', 'contenu' => "Petite astuce pour améliorer son accent anglais : écoutez des podcasts en demi-vitesse sur Spotify. Ça change tout ! 🎧"],
            ['user' => 5, 'tag' => 'React',         'contenu' => "React 19 est sorti ! Les Server Components changent vraiment la donne. Quelqu'un veut co-apprendre les nouvelles features ensemble ?"],
            ['user' => 7, 'tag' => 'Mathématiques', 'contenu' => "Tip maths : pour visualiser les matrices, pensez à elles comme des transformations dans l'espace. Ça rend l'algèbre linéaire tellement plus intuitive !"],
            ['user' => 8, 'tag' => 'Cybersécurité', 'contenu' => "CTF résolu ! La faille était une injection SQL pas très bien cachée... Rappel : TOUJOURS utiliser des requêtes préparées 🔒"],
            ['user' => 1, 'tag' => 'Figma',         'contenu' => "Les variables Figma sont enfin disponibles pour tous ! Le design system devient tellement plus maintenable."],
            ['user' => 3, 'tag' => 'Python',        'contenu' => "Première session SkillSwap terminée avec @AliceM sur Python. En 1h j'ai compris les list comprehensions. Merci la plateforme ! 🚀"],
            ['user' => 6, 'tag' => null,            'contenu' => "Question : vous utilisez quoi comme éditeur ? VSCode ici, mais curieuse de découvrir autre chose."],
            ['user' => 9, 'tag' => 'AutoCAD',       'contenu' => "AutoCAD tip : les blocs dynamiques permettent de créer des composants réutilisables. Indispensable pour les plans d'architecture !"],
        ];

        foreach ($postsData as $pd) {
            $post = new Post();
            $post->setUser($users[$pd['user']])
                 ->setContenu($pd['contenu'])
                 ->setCompetenceTag($pd['tag']);

            $likers = array_rand($users, rand(1, 4));
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
        // Badge Fondateur (idx 5) pour tous les utilisateurs fixtures
        foreach ($users as $user) {
            $ub = new UserBadge();
            $ub->setUser($user)->setBadge($badges[5]);
            $manager->persist($ub);
        }

        // Badge Premier pas (idx 0) pour ceux avec des sessions complétées
        foreach ([0, 1, 2, 4, 5, 7, 8] as $idx) {
            if (!isset($users[$idx])) continue;
            $ub = new UserBadge();
            $ub->setUser($users[$idx])->setBadge($badges[0]);
            $manager->persist($ub);
        }

        // Badge Mentor Bronze (idx 1) pour Alice
        $ub = new UserBadge();
        $ub->setUser($users[0])->setBadge($badges[1]);
        $manager->persist($ub);

        // Badge Profil complet (idx 6) pour Clara (légende)
        $ub = new UserBadge();
        $ub->setUser($users[2])->setBadge($badges[6]);
        $manager->persist($ub);

        // Badge Expert partage (idx 4) pour Inès
        $ub = new UserBadge();
        $ub->setUser($users[8])->setBadge($badges[4]);
        $manager->persist($ub);
    }
}
