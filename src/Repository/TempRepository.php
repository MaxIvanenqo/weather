<?php

namespace App\Repository;

use App\Entity\Temp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Temp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Temp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Temp[]    findAll()
 * @method Temp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TempRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Temp::class);
    }

    public function insertWeatherStats($consolidated_weather, $city){
        if (!isset($city) || !isset($consolidated_weather)) return;
        $em = $this->getDoctrine()->getManager();
        foreach ($consolidated_weather as $item){
            $tempExist = $this->findBy([
                'value'=>$item->the_temp,
                'city'=>$city,
                'date'=>$item->created
            ]);
            if (!!$tempExist) continue;
            $temp = new Temp();
            $temp->setDate($item->created);
            $temp->setValue($item->the_temp);
            $temp->setCity($city);
            $em->persist($temp);
        }
        $em->flush();
    }

}
