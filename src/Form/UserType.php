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
    // Build the form with the specified fields and their options
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Email field
            ->add('email', EmailType::class, [
                'label_attr' => [
                    'class' => 'inputEmail',    // CSS class for the label
                    'for' => 'email',           // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the input
                    'id' => 'email',            // HTML 'id' attribute for the input
                ],
            ])
            // Roles field
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',            // Choice value and label
                    'User' => 'ROLE_USER',              // Choice value and label
                ],
                'label_attr' => [
                        'class' => 'form-check-label',  // CSS class for the label
                        'for' => 'checkbox',            // HTML 'for' attribute for the label
                    ],
                'attr' => [
                    'class' => 'form-check-input',      // CSS class for the input
                    'id' => 'checkbox',                 // HTML 'id' attribute for the input
                ],
                'expanded' => true,                     // Render as checkboxes
                'multiple' => true,                     // Allow multiple selections
            ])
            // Password field
            ->add('password', PasswordType::class, [
                'required' => false,                    // Field is optional
                'mapped' => false,                      // Not mapped to the entity
                'attr' => [
                    'autocomplete' => 'new-password',   // Disable autocomplete
                    'class' => 'form-control',          // CSS class for the input
                    'id' => 'password',                 // HTML 'id' attribute for the input
                ],
                'label_attr' => [
                    'class' => 'inputPassword',         // CSS class for the label
                    'for' => 'password',                // HTML 'for' attribute for the label
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 6,                     // Minimum length of the password
                        // Custom error message
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 15,                    // Maximum length of the password
                        // Custom error message
                        'maxMessage' => 'Your password cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            // First name field
            ->add('firstname', TextType::class, [
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'firstname',       // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the input
                    'id' => 'firstname',        // HTML 'id' attribute for the input
                ],
            ])
            // Last name field
            ->add('lastname', TextType::class, [
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'lastname',        // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the input
                    'id' => 'lastname',         // HTML 'id' attribute for the input
                ],
            ]);
    }

    // Configure options for this form type
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,    // Associate form with the User entity
        ]);
    }
}
