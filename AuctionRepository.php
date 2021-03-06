<?php

namespace AppBundle\Repository;
use AppBundle\Entity\Auction;
use AppBundle\Entity\User;

/**
 * AuctionRepository
 *
 * 
 *Ta klasa została wygenerowana przez Doctrine ORM. Dodaj swój własny zwyczaj
 * poniżej metody repozytorium.
 */
class AuctionRepository extends \Doctrine\ORM\EntityRepository // odwołanie się do findAactiveOrder()
{
    /**
     * @return array
     */
    public function findActiveOrdered() // umożliwi na to zawansowane pobieranie z MySql
    {
        return $this->createQueryBuilder("a")
            ->where("a.status = :active") 
            ->setParameter("active", Auction::STATUS_ACTIVE) 
            ->andWhere("a.expiresAt > :now")
            ->setParameter("now", new \DateTime())
            ->orderBy("a.expiresAt", "ASC")
            ->getQuery()
            ->getResult();
        //wyszukujemy tylko aktywne aukcje oraz sortujemu od tych , które sie niedługo kończą
    }

    /**
     * @param User $owner
     *
     * @return array
     */
    public function findMyOrdered(User $owner) // zawansowane pobieranie z bazy danych za pomocą DQL
    {
        return $this
            ->getEntityManager()
            ->createQuery(
                "SELECT a // pobieramy wszystko z bazy danych 
                FROM AppBundle:Auction a // do jakiej tabeli się odwołujemy
                WHERE a.owner = :owner // gdzie właściciel aukcji = jego aukcji
                ORDER BY a.expiresAt ASC" // sortuj aukcje 
            )
            ->setParameter("owner", $owner)
            ->getResult(); 
    }

    /**
     * @return array
     */
    public function findActiveExpired()
    {
        return $this->createQueryBuilder("a")
            ->where("a.status = :status")
            ->setParameter("status", Auction::STATUS_ACTIVE)
            ->andWhere("a.expiresAt < :now")
            ->setParameter("now", new \DateTime)
            ->getQuery()
            ->getResult();
    }
}
