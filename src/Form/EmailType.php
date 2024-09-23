<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType as SymfonyEmailType;

class EmailType extends AbstractType
{
    // Build the form with the specified fields and their options
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Add the email field, pre-filled with the provided email
            ->add('email', SymfonyEmailType::class, [
                'label' => 'To :',              // Label for the email field
                'data' => $options['email'],    // Pre-filled email address
                'label_attr' => [
                    'class' => 'inputEmail',    // CSS class for the label
                    'for' => 'email',           // HTML 'for' attribute for label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the input
                    'id' => 'email',            // HTML 'id' attribute for input
                    'readonly' => true,         // Make the input read-only
                ],
            ])
            // Add the subject field with a default value
            ->add('subject', TextType::class, [
                'label' => 'Subject :',             // Label for the subject field
                'data' => 'Subject of the email',   // Default subject text
                'label_attr' => [
                    'class' => 'form-label',        // CSS class for the label
                    'for' => 'subject',             // HTML 'for' attribute for label
                ],
                'attr' => [
                    'class' => 'form-control',      // CSS class for the input
                    'id' => 'subject',              // HTML 'id' attribute for input
                ],
            ])
            // Add the message field with a pre-filled message
            ->add('message', TextareaType::class, [
                'label' => 'Message :',                                 // Label for the message field
                'data' => 'Bonjour ' . $options['username'] . ', ...',  // Pre-filled message text
                'label_attr' => [
                    'class' => 'form-label',                            // CSS class for the label
                    'for' => 'message',                                 // HTML 'for' attribute for label
                ],
                'attr' => [
                    'class' => 'form-control',                          // CSS class for the textarea
                    'id' => 'message',                                  // HTML 'id' attribute for textarea
                ],
            ])
            // Add a checkbox for email confirmation
            ->add('emailConfirmation', CheckboxType::class, [
                'required' => false,                    // Checkbox is not required
                'label'    => 'Email confirmation ?',   // Label for the checkbox
                'label_attr' => [
                    'class' => 'form-check-label',      // CSS class for the label
                    'for' => 'flexSwitchCheck',         // HTML 'for' attribute for label
                ],
                'attr' => [
                    'class' => 'form-check-input',      // CSS class for the checkbox
                    'id' => 'flexSwitchCheck',          // HTML 'id' attribute for checkbox
                ],
            ])
            // Add a submit button
            ->add('send', SubmitType::class, [
                'label' => 'Send',                  // Label for the submit button
                'attr' => [
                    'class' => 'btn btn-warning',   // CSS class for the button
                ],
            ]);
    }

    // Configure options for this form type
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'email' => null,    // Default value for email option
            'username' => null, // Default value for username option
        ]);
    }
}