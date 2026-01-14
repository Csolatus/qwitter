# QWITTER - Projet Réseau Social

Ce document détaille l'architecture des données et les entités nécessaires au bon fonctionnement de l'application Qwitter.

## 📚 Modélisation des Données (MCD)

Voici la liste des entités à créer avec leurs propriétés et relations.

### 1. User
Représente un utilisateur de la plateforme.

| Champ | Type | Description |
| :--- | :--- | :--- |
| `id` | Integer | Clé primaire |
| `email` | String | Unique, email de connexion |
| `roles` | JSON | Rôles utilisateur (`ROLE_USER`, `ROLE_ADMIN`) |
| `password` | String | Mot de passe haché |
| `pseudo` | String | Unique, nom d'affichage |
| `bio` | Text | (Nullable) Biographie de l'utilisateur |
| `avatar` | String | (Nullable) Chemin/Nom du fichier image |
| `google_id` | String | (Nullable) ID pour OAuth Google |
| `slug` | String | Unique, URL-friendly user identifier |
| `is_verified` | Boolean | (Suggéré) Statut du compte (défaut : false) |
| `created_at` | DateTimeImmutable | (Suggéré) Date d'inscription |
| `updated_at` | DateTime | Date de dernière modification |

**Relations :**
*   **Posts** : One-to-Many vers `Post` (Un utilisateur écrit plusieurs posts).
*   **Comments** : One-to-Many vers `Comment` (Un utilisateur écrit plusieurs commentaires).
*   **Likes** : One-to-Many vers `Like` (Un utilisateur aime plusieurs posts).
*   **MessagesSent** : One-to-Many vers `Message` (Expéditeur).
*   **MessagesReceived** : One-to-Many vers `Message` (Destinataire).
*   **Notifications** : One-to-Many vers `Notification`.
*   **Followers** : Many-to-Many (Self-referencing) vers `User`.
*   **Following** : Many-to-Many (Self-referencing) vers `User`.

---

### 2. Post
Représente une publication (texte + média).

| Champ | Type | Description |
| :--- | :--- | :--- |
| `id` | Integer | Clé primaire |
| `content` | Text | Contenu du post |
| `image_filename` | String | (Nullable) Nom du fichier image |
| `created_at` | DateTimeImmutable | Date de création |
| `updated_at` | DateTime | Date de modification |
| `media_type` | String | Type de média (`image`, `video`, `none`) |

**Relations :**
*   **Author** : Many-to-One vers `User` (Créateur du post).
*   **Comments** : One-to-Many vers `Comment`.
*   **Likes** : One-to-Many vers `Like`.
*   **Hashtags** : Many-to-Many vers `Hashtag` (Suggéré).

---

### 3. Comment
Un commentaire sous un post.

| Champ | Type | Description |
| :--- | :--- | :--- |
| `id` | Integer | Clé primaire |
| `content` | Text | Contenu du commentaire |
| `created_at` | DateTimeImmutable | Date de création |
| `updated_at` | DateTime | Date de modification |

**Relations :**
*   **Author** : Many-to-One vers `User`.
*   **Post** : Many-to-One vers `Post`.

---

### 4. Like
Matérialise le "J'aime" d'un utilisateur sur un post.

| Champ | Type | Description |
| :--- | :--- | :--- |
| `id` | Integer | Clé primaire |
| `created_at` | DateTimeImmutable | Date du like |

**Relations :**
*   **User** : Many-to-One vers `User`.
*   **Post** : Many-to-One vers `Post`.

> **Note** : Ajouter une contrainte d'unicité sur le couple (`user_id`, `post_id`) pour empêcher le double like.

---

### 5. Follow (Logique)
Géré via une relation Many-to-Many sur l'entité `User` (souvent une table de jointure `user_user` ou `followers`).

---

### 6. Message
Messagerie privée entre utilisateurs.

| Champ | Type | Description |
| :--- | :--- | :--- |
| `id` | Integer | Clé primaire |
| `content` | Text | Contenu du message |
| `created_at` | DateTimeImmutable | Date d'envoi |
| `is_read` | Boolean | Statut de lecture (défaut : false) |

**Relations :**
*   **Sender** : Many-to-One vers `User`.
*   **Receiver** : Many-to-One vers `User`.

---

### 7. Notification
Alerte pour l'utilisateur.

| Champ | Type | Description |
| :--- | :--- | :--- |
| `id` | Integer | Clé primaire |
| `type` | String | Type (`LIKE`, `COMMENT`, `FOLLOW`, `MESSAGE`) |
| `is_read` | Boolean | Statut de lecture |
| `message` | String | (Optionnel) Texte court de la notif |
| `related_id` | Integer | (Nullable) ID de l'objet concerné (Post, User...) |

**Relations :**
*   **User** : Many-to-One vers `User` (Celui qui reçoit la notif).
*   **RelatedUser** : Many-to-One vers `User` (Celui qui a déclenché l'action - Suggéré).

> **Suggestion** : Plutôt qu'un seul champ `related_id` générique, il est parfois plus propre en SQL strict d'avoir des colonnes nullable `related_post_id`, `related_user_id` pour bénéficier des contraintes de clés étrangères (Foreign Keys).

---

## 💡 Suggestions & Améliorations

### 1. Entité Hashtag (Bonus)
Pour faciliter la recherche par sujets.

| Champ | Type | Relationship |
| :--- | :--- | :--- |
| `name` | String | Unique (ex: "symfony") |
| `posts` | Many-to-Many | Relation vers `Post` |

### 2. Gestion des Médias
Si vous prévoyez d'ajouter la vidéo, pensez à un champ `media_type` (enum ou string) dans `Post` pour savoir comment rendre le média (balise `<img>` ou `<video>`).

### 3. Date de modification
Ajouter `updated_at` sur les Posts et Commentaires est une bonne pratique pour afficher "Modifié le...".

### 4. Slugs
Pour les URLs des profils, utiliser le `pseudo` est bien, mais assurez-vous qu'il est "URL-friendly" (pas d'espaces, caractères spéciaux gérés proprements). Sinon, ajoutez un champ `slug`.
