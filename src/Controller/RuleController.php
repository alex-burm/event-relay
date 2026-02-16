<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Form\RuleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/rule')]
class RuleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'rule_index', methods: ['GET'])]
    public function index(): Response
    {
        $rules = $this->entityManager->getRepository(Rule::class)->findAll();

        return $this->render('rule/index.html.twig', [
            'rules' => $rules,
        ]);
    }

    #[Route('/create', name: 'rule_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $rule = new Rule();
        $form = $this->createForm(RuleType::class, $rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($rule);
            $this->entityManager->flush();

            return $this->redirectToRoute('rule_index');
        }

        return $this->render('rule/form.html.twig', [
            'form' => $form,
            'title' => 'Create Rule',
        ]);
    }

    #[Route('/{id}/edit', name: 'rule_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rule $rule): Response
    {
        $form = $this->createForm(RuleType::class, $rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('rule_index');
        }

        return $this->render('rule/form.html.twig', [
            'form' => $form,
            'title' => 'Edit Rule',
        ]);
    }

    #[Route('/{id}/clone', name: 'rule_clone', methods: ['GET'])]
    public function clone(Rule $rule): Response
    {
        $clone = new Rule();
        $clone->setName($rule->getName() . ' (copy)');
        $clone->setUri(uniqid());
        $clone->setCallbackUrl($rule->getCallbackUrl());
        $clone->setMethod($rule->getMethod());
        $clone->setContentType($rule->getContentType());
        $clone->setHeaders($rule->getHeaders());
        $clone->setVariables($rule->getVariables());
        $clone->setQuery($rule->getQuery());

        $this->entityManager->persist($clone);
        $this->entityManager->flush();

        return $this->redirectToRoute('rule_edit', ['id' => $clone->getId()]);
    }

    #[Route('/{id}/delete', name: 'rule_delete', methods: ['POST'])]
    public function delete(Request $request, Rule $rule): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rule->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($rule);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('rule_index');
    }
}
