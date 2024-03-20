# Guide d'Installation du Projet Symfony

Ce guide vous fournira les étapes nécessaires pour installer et configurer le projet Symfony.

## Installation

Suivez les étapes ci-dessous pour installer le projet :

1. **Installer les Dépendances PHP avec Composer**  
<code>composer install</code>

2. **Compiler les Fichiers SASS**  
<code>php bin/console sass:build</code>

3. **Installer les Dépendances JavaScript avec npm**  
<code>npm i</code>

4. **Compiler les Ressources JavaScript et CSS**  
<code>npm run build</code>

5. **Générer une Migration Doctrine**  
<code>php bin/console make:migration</code>

6. **Exécuter les Migrations Doctrine**  
<code>php bin/console doctrine:migrations:migrate</code>
