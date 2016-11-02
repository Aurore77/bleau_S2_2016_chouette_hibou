<?php

namespace ChouettesBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CategorieRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategorieRepository extends EntityRepository
{
    public function getDoudouByCateg($categ){
        $sql = 'SELECT * FROM categorie JOIN modele ON categorie.id = modele.categorie_id JOIN image ON image.id = modele.image_id WHERE nom = :categ';
        $params = array(
            'categ' => $categ,
        );

        return $this->getEntityManager()->getConnection()->executeQuery($sql, $params)->fetchAll();

    }
}

