<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Entity\Query;
use App\Messenger\TaskMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return new Response('OK.');
    }

    #[Route(path: '/key/{rule}', name: 'relay_id', methods: ['POST', 'GET'])]
    public function relay(Request $request, Rule $rule): Response
    {
        $query = new Query();
        $query->setRule($rule);
        $query->setBody($request->getContent());
        $query->setContentType($request->getContentTypeFormat());
        $query->setRequestId($request->headers->get('X-Request-Id'));
        $query->setIp($request->getClientIp() ?? '');
        $query->setUserAgent($request->headers->get('User-Agent'));
        $query->setMethod($request->getMethod());

        $this->entityManager->persist($query);
        $this->entityManager->flush();

        $this->bus->dispatch(new TaskMessage($query->getId()));

        return $this->json([
            'status' => 'accepted',
            'id' => $query->getId(),
        ], Response::HTTP_ACCEPTED);
    }
}
