# Copilot Instructions — BoisguiBad Club Manager

## Vue d'ensemble du projet

**BoisguiBad Club Manager** est une application web Symfony 7.4 / PHP 8.2 pour gérer un club de badminton français. Elle gère la planification des séances, l'inscription aux événements, la gestion des membres et les objets trouvés. L'application est entièrement en **français**.

## Stack technique

| Composant | Technologie |
|---|---|
| Langage | PHP >= 8.2 |
| Framework | Symfony 7.4 |
| ORM | Doctrine ORM 3.6 + Migrations 3.7 |
| Templates | Twig 3.x |
| Base de données | MySQL 8.0 (schéma : `boisguibad`) |
| Authentification | Symfony Security (formulaire + remember-me, 7 jours) |
| Messagerie | Symfony Messenger (asynchrone, transport Doctrine) |
| Mailer | Symfony Mailer (transport null en dev) |
| Tests | PHPUnit 12.5 |

## Structure du projet

```
src/
├── Command/          # Commandes CLI (generate-sessions, send-reminder)
├── Controller/       # Contrôleurs HTTP
│   ├── Admin*        # Contrôleurs admin uniquement (ROLE_ADMIN)
│   └── *Controller   # Contrôleurs membres (ROLE_MEMBER)
├── DataFixtures/     # Données de démonstration pour le développement
├── Entity/           # Entités Doctrine ORM (6 entités)
├── Enum/             # Enums PHP 8.1 typés (4 enums)
├── Form/             # Types de formulaires Symfony (5 formulaires)
├── Message/          # DTOs de messages asynchrones (2 messages)
├── MessageHandler/   # Handlers asynchrones (1 handler)
├── Repository/       # Repositories Doctrine (6 repos, étendent ServiceEntityRepository)
├── Security/         # Authentificateur personnalisé
├── Service/          # Logique métier (3 services + 3 interfaces)
└── Trait/            # TimestampableTrait (createdAt, updatedAt)
tasks/
├── todo.md           # Plan et progression de la tâche en cours
└── lessons.md        # Leçons apprises suite aux corrections
tests/
└── Service/          # Tests unitaires pour les 3 services
templates/            # Templates Twig organisés par fonctionnalité
config/               # Configuration Symfony (YAML)
```

## Modèle de domaine

### Entités

- **User** — Membre du club avec `MemberType` (LOISIR ou COMPETITEUR) et rôles (`ROLE_MEMBER`, `ROLE_ADMIN`).
- **Session** — Séance de gymnase avec `SessionType` (LUNDI / MERCREDI / JEUDI / DIMANCHE), date, lieu, notes et un responsable des clés optionnel (`responsableCles: User`).
- **SessionRegistration** — Table de jointure User ↔ Session avec un booléen `present`.
- **Event** — Événement/tournoi du club avec date limite d'inscription et nombre maximum de participants.
- **EventRegistration** — Table de jointure User ↔ Event avec `EventRegistrationStatus` (INSCRIT / DESISTE).
- **LostItem** — Objet trouvé avec `LostItemStatus` (TROUVE / RENDU).

### Règles d'accès (critique métier)

L'accès aux séances est différencié par type de membre — appliqué dans `SessionService::canAccess()` :
- **LOISIR** → LUNDI, JEUDI, DIMANCHE
- **COMPETITEUR** → toutes les séances (LUNDI, MERCREDI, JEUDI, DIMANCHE)

**Ne jamais contourner cette logique lors de la modification de l'inscription aux séances.**

### Enums

Tous les enums sont des `BackedEnum` PHP 8.1 (string) :
- `SessionType` : `LUNDI`, `MERCREDI`, `JEUDI`, `DIMANCHE`
- `MemberType` : `LOISIR`, `COMPETITEUR`
- `EventRegistrationStatus` : `INSCRIT`, `DESISTE`
- `LostItemStatus` : `TROUVE`, `RENDU`

## Conventions de code

- **Langue** : Toutes les chaînes utilisateur, commentaires, docblocks et noms de variables sont en **français**.
- **Services** : Toujours implémenter une interface correspondante (`*ServiceInterface`). Injecter l'interface, pas la classe concrète.
- **Repositories** : Étendre `ServiceEntityRepository`. Les méthodes de requête personnalisées appartiennent ici, pas dans les services ou contrôleurs.
- **Entités** : Utiliser `TimestampableTrait` sur toute nouvelle entité nécessitant `createdAt`/`updatedAt`.
- **Formulaires** : Utiliser des classes `*Type` dédiées ; ne jamais construire de formulaires directement dans les contrôleurs.
- **Enums** : Utiliser des backed enums pour tout ensemble fini de valeurs string/int.
- **PSR-4** : `App\` → `src/`, `App\Tests\` → `tests/`.
- **Autowiring** : Tous les services sont autowirés et autoconfigurés via `config/services.yaml`.

## Tests

```bash
# Lancer tous les tests
php bin/phpunit

# Lancer une classe de test spécifique
php bin/phpunit tests/Service/SessionServiceTest.php
```

- Les tests sont des **tests unitaires** utilisant les mocks PHPUnit — aucune base de données requise.
- Mocker `EntityManagerInterface` et les repositories ; ne jamais utiliser de vraies connexions DB dans les tests unitaires.
- Lors de l'ajout d'un nouveau service, ajouter une classe de test correspondante dans `tests/Service/`.
- PHPUnit échoue sur les dépréciations, notices et warnings — garder les tests propres.

## Commandes CLI

```bash
# Générer les séances récurrentes pour les N prochaines semaines (défaut : 4)
php bin/console app:generate-sessions --weeks=4

# Envoyer les rappels aux responsables des clés 24h avant leur séance
php bin/console app:send-session-reminder
```

## Sécurité et contrôle d'accès

Défini dans `config/packages/security.yaml` :
- `^/admin` → requiert `ROLE_ADMIN`
- Toutes les autres routes → requièrent `ROLE_MEMBER`
- Formulaire de connexion : `/connexion`, déconnexion, cookie remember-me (7 jours)

**Ne jamais exposer des routes admin sans la vérification `ROLE_ADMIN`.**

## Base de données

- Connexion : `DATABASE_URL` dans `.env` (MySQL 8.0, schéma `boisguibad`)
- Utiliser les Migrations Doctrine pour les changements de schéma — ne jamais modifier la DB directement.
- Charger les fixtures pour le dev : `php bin/console doctrine:fixtures:load`

## Messagerie asynchrone

- Transport : Doctrine (stocké en DB), configuré dans `config/packages/messenger.yaml`
- File d'échec avec 3 tentatives (multiplicateur ×2)
- Classes de messages : `SendEmailMessage`, `SendReminderMessage`
- Handler : `SendEmailMessageHandler`

## Fichiers clés

| Fichier | Rôle |
|---|---|
| `src/Service/SessionService.php` | Logique métier des séances + contrôle d'accès |
| `src/Service/SessionSchedulerService.php` | Génération des séances récurrentes |
| `src/Security/AppAuthenticator.php` | Authentificateur formulaire personnalisé |
| `config/packages/security.yaml` | Règles d'auth et de contrôle d'accès |
| `config/packages/messenger.yaml` | Transport asynchrone + routage |
| `config/services.yaml` | Configuration de l'autowiring des services |
| `phpunit.dist.xml` | Configuration PHPUnit |
| `tasks/todo.md` | Plan et progression de la tâche en cours |
| `tasks/lessons.md` | Leçons apprises suite aux corrections de l'agent |
