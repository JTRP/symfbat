<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, [

                'label'                 => 'Message',

                'constraints' => [

                    new NotBlank([
                        'message'           => 'Le champ ne doit pas être vide'
                    ]),


                    new Length([

                        'min'               => 2,
                        'max'               => 2_000,

                        'minMessage'        => 'Le message doit avoir au minimum {{ limit }} de caractères',
                        'maxMessage'        => 'Le message doit avoir au maximum {{ limit }} de caractères'

                    ])

                ],
            ])

            ->add('save', SubmitType::class, [
                'label'                 => 'Publier',
                'attr' => [
                    'class'             => 'btn btn-outline-primary w-100'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
