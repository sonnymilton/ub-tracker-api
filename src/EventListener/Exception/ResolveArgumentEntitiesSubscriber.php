<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener\Exception;

use App\Request\HasResolvableEntitiesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResolveArgumentEntitiesSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * TestListener constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments',
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent $event
     */
    public function onControllerArguments(ControllerArgumentsEvent $event)
    {
        foreach ($event->getArguments() as $argument) {
            if ($argument instanceof HasResolvableEntitiesInterface) {
                $argument->resolve($this->em);
            }
        }
    }
}
