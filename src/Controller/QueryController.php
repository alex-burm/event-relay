<?php

namespace App\Controller;

use App\Entity\Query;
use App\Repository\QueryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/query')]
class QueryController extends AbstractController
{
    private const PER_PAGE = 50;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'query_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        /** @var QueryRepository $repo */
        $repo = $this->entityManager->getRepository(Query::class);

        $page = max(1, $request->query->getInt('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $queries = $repo->findLatest(self::PER_PAGE + 1, $offset);

        $hasNext = count($queries) > self::PER_PAGE;
        if ($hasNext) {
            array_pop($queries);
        }

        return $this->render('query/index.html.twig', [
            'queries' => $queries,
            'page' => $page,
            'hasNext' => $hasNext,
        ]);
    }

    #[Route('/{id}', name: 'query_show', methods: ['GET'])]
    public function show(Query $query): Response
    {
        return $this->render('query/show.html.twig', [
            'query' => $query,
        ]);
    }
}
