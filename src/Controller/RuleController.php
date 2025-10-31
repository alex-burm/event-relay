<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Message\IncomingRequestMessage;
use App\Entity\Query;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/rule')]
class RuleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/create', 'rule_create')]
    public function create(Request $request): Response
    {
        $rule = new Rule();
        $rule->setName($request->query->get('name'));
        $rule->setUri($request->query->get('uri'));

        $this->entityManager->persist($rule);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'created',
        ], Response::HTTP_CREATED);
    }
}
