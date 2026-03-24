# Lessons Learned

<!-- Mettre à jour ce fichier après chaque correction de l'utilisateur. -->
<!-- Format : date, contexte, règle à retenir. -->

## Règles actives

- 2026-03-24, contrôle d'accès admin, un membre non admin ne doit pas voir une page d'exception Symfony brute ; préférer une redirection claire avec message flash et un test fonctionnel dédié.
- 2026-03-24, génération Stitch admin, verrouiller explicitement le shell de navigation, la langue et les modules autorisés dans les prompts sinon Stitch peut dériver vers un back-office générique hors contexte produit.
