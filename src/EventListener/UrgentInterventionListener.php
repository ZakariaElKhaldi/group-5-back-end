<?php

namespace App\EventListener;

use App\Entity\Intervention;
use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Intervention::class)]
class UrgentInterventionListener
{
    public function postPersist(Intervention $intervention, PostPersistEventArgs $event): void
    {
        if ($intervention->getPriorite() === 'Urgente') {
            $entityManager = $event->getObjectManager();

            $notification = new Notification();
            $notification->setTitre('Nouvelle Intervention Urgente');
            $notification->setMessage(sprintf(
                'Une nouvelle intervention urgente (#%d) a été créée pour la machine %s. Description: %s',
                $intervention->getId(),
                $intervention->getMachine()->getModele(),
                substr($intervention->getDescription(), 0, 50) . '...'
            ));
            $notification->setType('urgent');

            $entityManager->persist($notification);
            $entityManager->flush();
        }
    }
}
