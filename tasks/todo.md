# Todo

<!-- Utiliser ce fichier pour planifier chaque tâche avant implémentation. -->
<!-- Format : [ ] = à faire, [x] = terminé -->

## Tâche en cours

- [x] Auditer les derniers écarts visuels entre Stitch et le projet
- [x] Lisser les écrans Stitch encore légèrement divergents
- [x] Intégrer les vues validées dans les templates Twig du projet
- [x] Vérifier l'application après intégration
- [x] Rédiger le bilan final

## Résultats / Review

- Générations Stitch lancées et retournées avec succès pour les 7 écrans manquants du projet `projects/2801059837339653648`.
- Écrans membre générés : `Mes inscriptions aux événements`, `Déclarer un objet trouvé`, `Proposer une séance du dimanche`.
- Écrans admin générés : `Gestion des événements`, `Créer un événement (Admin)`, `Inscriptions à l'événement`, `Modifier la séance`.
- Les sorties Stitch retournent des écrans en statut `COMPLETE` avec design system cohérent avec les vues déjà existantes.
- Audit d'homogénéité réalisé sur les écrans Stitch : la cohérence globale est bonne côté membre et sur la majorité des vues admin.
- Deux écrans admin générés présentaient une dérive visuelle et éditoriale (`Créer un événement (Admin)` et `Modifier la séance`) avec vocabulaire anglais et modules hors périmètre.
- Ces deux écrans ont été réécrits dans Stitch pour réutiliser le shell admin BoisguiBad, la navigation française et la palette bleue du projet.
- Les retours Stitch confirment la création des versions harmonisées `Créer un événement (Admin) - Révisé` et `Modifier la séance (Admin) - Harmonisé`.
- Intégration Twig effectuée pour les vues `Créer un événement (Admin)`, `Modifier la séance`, `Proposer une séance du dimanche` et `Déclarer un objet trouvé`.
- Les nouvelles mises en page reprennent les principes validés dans Stitch sans ajouter de dépendance fonctionnelle ni de nouveaux champs métier.
- Validation post-intégration réussie : PHPUnit vert (`28 tests, 56 assertions`), rendu navigateur contrôlé sur les pages membre et admin ciblées.
- Les assets ont été recompilés pour publier la version finale du front et supprimer le log de démonstration restant dans la console navigateur.
