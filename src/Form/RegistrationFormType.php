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
    // Build the form with the specified fields and their options
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Add email field with validation
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\Email([
                        // Error message for invalid email
                        'message' => 'Please enter a valid email',
                    ]),
                ],
                'label_attr' => [
                    'class' => 'inputEmail',    // CSS class for the label
                    'for' => 'email',           // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the input
                    'id' => 'email',            // HTML 'id' attribute for the input
                ],
            ])
            // Add password field with validation
            ->add('password', PasswordType::class, [
                'mapped' => false,              // The field is not mapped to the User entity
                'attr' => [
                'class' => 'form-control',      // CSS class for the input
                    'id' => 'password',         // HTML 'id' attribute for the input
                ],
                'label_attr' => [
                    'class' => 'inputPassword', // CSS class for the label
                    'for' => 'password',        // HTML 'for' attribute for the label
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        // Error message if password is blank
                        'message' => 'Please enter a password',
                    ]),
                    new Assert\Length([
                        'min' => 6,             // Minimum length of the password
                        // Error message if password is too short
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 15,            // Maximum length of the password
                        // Error message if password is too long
                        'maxMessage' => 'Your password cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            // Add first name field
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
            // Add last name field
            ->add('lastname', TextType::class, [
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'lastname',        // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the input
                    'id' => 'lastname',         // HTML 'id' attribute for the input
                ],
            ])
            // Add checkbox for agreeing to terms
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,                  // The field is not mapped to the User entity
                'constraints' => [
                    new Assert\IsTrue([
                        // Error message if terms are not agreed to
                        'message' => 'You should agree to our terms',
                    ]),
                ],
                'label_attr' => [
                    'class' => 'form-check-label',  // CSS class for the label
                    'for' => 'checkbox',            // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-check-input',  // CSS class for the checkbox
                    'id' => 'checkbox',             // HTML 'id' attribute for the checkbox
                ]
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
