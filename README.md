# Qwitter

Qwitter est une application de réseau social simplifiée, inspirée de Twitter, développée avec le framework Symfony.

## 🛠 Stack Technique

Ce projet utilise une stack moderne basée sur Symfony 8 et PHP 8.2+.

### Backend
*   **Framework** : [Symfony 8.0](https://symfony.com/)
*   **Langage** : PHP 8.2+
*   **ORM** : Doctrine
*   **Base de données** : PostgreSQL 15

### Frontend
*   **Gestionnaire d'assets** : Symfony AssetMapper (pas de Node.js/Webpack requis)
*   **JavaScript** : [Stimulus](https://stimulus.hotwired.dev/) & [Turbo](https://turbo.hotwired.dev/)
*   **CSS** : TailwindCSS (via CDN ou intégré)

### Infrastructure & Outils
*   **Conteneurisation** : Docker & Docker Compose
*   **Administration BDD** : pgAdmin 4
*   **Tests** : PHPUnit

## 🚀 Installation et Démarrage

Suivez ces étapes pour installer et lancer le projet localement.

### Prérequis
*   [Docker Desktop](https://www.docker.com/products/docker-desktop) installé et lancé.
*   Git pour cloner le projet.

### Étapes d'installation

1.  **Cloner le dépôt**
    ```bash
    git clone <votre_url_repo>
    cd qwitter
    ```

2.  **Lancer les conteneurs Docker**
    Construisez et démarrez les services (PHP, PostgreSQL, pgAdmin) :
    ```bash
    docker-compose up -d --build
    ```