<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
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
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                ],
                'label_attr' => [
                        'class' => 'form-check-label',
                        'for' => 'checkbox'
                    ],
                'attr' => [
                    'class' => 'form-check-input',
                    'id' => 'checkbox'
                ],
                'required' => false,
                'expanded' => true,  // Pour afficher comme une liste de cases à cocher
                'multiple' => true,  // Pour permettre la sélection multiple
            ])
            ->add('password', PasswordType::class, [
                'required' => false,  // Le mot de passe est facultatif
                'mapped' => false,    // Ne lie pas ce champ directement à l'entité User
                'attr' => [
                    'autocomplete' => 'new-password',
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
