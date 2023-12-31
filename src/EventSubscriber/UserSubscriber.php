<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Compte;
use App\Entity\TypeUser;
use App\Entity\UserPlateform;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserSubscriber extends AbstractController implements EventSubscriberInterface
{

    private $em;
    public $doctrine;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['operationAfterCreateAMember', EventPriorities::POST_WRITE]
        ];
    }

    public function operationAfterCreateAMember(ViewEvent $event): void
    {
        $User   = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        if ($User instanceof UserPlateform && Request::METHOD_POST === $method) {
            $otherNumber = [];

            for ($i = 0; $i < 4; $i++) {
                try {
                    $otherNumber[] = random_int(0, 9);
                } catch (\Exception $e) {
                    echo $e;
                }
            }

            $keySecret = password_hash(($User->getPhone() . '' . $User->getPassword() . '' . (new \DateTime())->format('Y-m-d H:i:s') . '' . implode("", $otherNumber)), PASSWORD_DEFAULT);

            if (strlen($keySecret) > 100) {
                $keySecret = substr($keySecret, 0, 99);
            }


            $typeUser = $this->em->getRepository(TypeUser::class)->findOneBy(['id' => 3]);
            $User->setTypeUser($typeUser);





            // $User->setCodeParrain($codeParrain);
            $this->em->persist($User);
            $this->em->flush(); # code...
        }
    }
}
