<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Please enter a email',
                ]),
                new Assert\Email([
                    'message' => 'Please enter a valid email',
                ]),
            ],
            'label_attr' => [
                'class' => 'inputEmail',
                'for' => 'email',
            ],
            'attr' => [
                'class' => 'form-control',
                'id' => 'email',
            ],
            ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                'class' => 'form-control',
                    'id' => 'password'
                ],
                'label_attr' => [
                    'class' => 'inputPassword',
                    'for' => 'password',
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        // 'max' => 4096,
                        'max' => 15,
                        'maxMessage' => 'Your password cannot be longer than {{ limit }} characters',
                    ]),
                ],
                ])
                ->add('firstname', TextType::class, [
                    'label_attr' => [
                        'class' => 'form-label',
                        'for' => 'firstname',
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'firstname',
                    ],
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'Please enter a firstname',
                        ]),
                    ],
                ])
                ->add('lastname', TextType::class, [
                    'label_attr' => [
                        'class' => 'form-label',
                        'for' => 'lastname',
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'lastname',
                    ],
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'Please enter a lastname',
                        ]),
                    ],
                ])
                ->add('agreeTerms', CheckboxType::class, [
                    'mapped' => false,
                    'constraints' => [
                        new Assert\IsTrue([
                            'message' => 'You should agree to our terms',
                        ]),
                    ],
                    'label_attr' => [
                        'class' => 'form-check-label',
                        'for' => 'checkbox'
                    ],
                    'attr' => [
                        'class' => 'form-check-input',
                        'id' => 'checkbox'
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
