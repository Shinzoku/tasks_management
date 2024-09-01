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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', SymfonyEmailType::class, [
                'label' => 'To :',
                'data' => $options['email'], // Pre-filled email
                'label_attr' => [
                    'class' => 'inputEmail',
                    'for' => 'email',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'email',
                    'readonly' => true,
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject :',
                'data' => 'Subject of the email', // Default object
                'label_attr' => [
                    'class' => 'form-label',
                    'for' => 'subject',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'subject',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message :',
                'data' => 'Bonjour ' . $options['username'] . ', ...', // Pre-filled message
                'label_attr' => [
                    'class' => 'form-label',
                    'for' => 'message',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'message',
                ],
            ])
            ->add('emailConfirmation', CheckboxType::class, [
                'required' => false,
                'label'    => 'Email confirmation ?',
                'label_attr' => [
                    'class' => 'form-check-label',
                    'for' => 'flexSwitchCheck',
                ],
                'attr' => [
                    'class' => 'form-check-input',
                    'id' => 'flexSwitchCheck',
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Send',
                'attr' => [
                    'class' => 'btn btn-warning',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'email' => null,
            'username' => null,
        ]);
    }
}