<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * @param array $authorIds
     * @return Post[]
     */
    /**
     * @param array $authorIds
     * @return Post[]
     */
    public function findByAuthors(array $authorIds): array
    {
        if (empty($authorIds)) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->andWhere('p.author IN (:authorIds)')
            ->setParameter('authorIds', $authorIds)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les posts pour le fil "Pour vous" (exclut les comptes privés non suivis)
     */
    public function findForYou(?\App\Entity\User $user): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'u')
            ->addSelect('u')
            ->orderBy('p.created_at', 'DESC');

        if ($user) {
            // Si connecté :
            // 1. Auteurs non privés
            // 2. OU Auteurs que je suis (même privés)
            // 3. OU Moi-même
            $qb->leftJoin('u.followers', 'f')
                ->where('u.isPrivate = false')
                ->orWhere('f.id = :userId')
                ->orWhere('u.id = :userId')
                ->setParameter('userId', $user->getId());
        } else {
            // Si non connecté : seulement les comptes publics
            $qb->where('u.isPrivate = false');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les posts pour le fil "Abonnements"
     */
    public function findFollowingFeed(\App\Entity\User $user): array
    {
        $following = $user->getFollowing()->toArray();
        $authorIds = array_map(fn($u) => $u->getId(), $following);
        $authorIds[] = $user->getId(); // Inclure ses propres posts

        return $this->findByAuthors($authorIds);
    }
}
