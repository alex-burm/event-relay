<?php

namespace App\Form;

use App\Entity\Rule;
use App\Form\DataTransformer\JsonToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('uri', TextType::class)
            ->add('callbackUrl', UrlType::class)
            ->add('method', ChoiceType::class, [
                'choices' => [
                    'GET' => 'GET',
                    'POST' => 'POST',
                    'PUT' => 'PUT',
                    'PATCH' => 'PATCH',
                    'DELETE' => 'DELETE',
                ],
            ])
            ->add('contentType', TextType::class, [
                'required' => false,
            ])
            ->add('headers', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('variables', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('query', TextareaType::class, [
                'attr' => ['rows' => 8],
            ]);

        $jsonTransformer = new JsonToArrayTransformer();
        $builder->get('headers')->addModelTransformer($jsonTransformer);
        $builder->get('variables')->addModelTransformer(new JsonToArrayTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rule::class,
        ]);
    }
}
