<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\TaskList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskType extends AbstractType
{
    // Build the form with the specified fields and their options
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Add title field
            ->add('title', TextType::class, [
                'required' => true,             // Field is mandatory
                'empty_data' => '',             // Default value if the field is empty
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'name',            // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the input
                    'id' => 'name',             // HTML 'id' attribute for the input
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        // Custom error message if field is empty
                        'message' => 'Please enter a title',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,             // Field is mandatory
                'empty_data' => '',             // Default value if the field is empty
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'description',     // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the input
                    'id' => 'description',      // HTML 'id' attribute for the input
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        // Custom error message if field is empty
                        'message' => 'please enter a description',
                    ]),
                ],
            ])
            // Add due_date field
            ->add('due_date', DateType::class, [
                'widget' => 'choice',           // Render date as dropdowns for day, month, and year
                'placeholder' => '',            // Placeholder text for the dropdowns
                'required' => false,            // Field is optional
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'due_date',        // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'id' => 'due_date',         // HTML 'id' attribute for the input
                ],
            ])
            // Add progress field
            ->add('progress', RangeType::class, [
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'progress',        // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-range',    // CSS class for the range input
                    'id' => 'progress',         // HTML 'id' attribute for the range input
                    'min' => 0,                 // Minimum value for the range
                    'max' => 100,               // Maximum value for the range
                    'step' => 1,                // Step value for the range
                    // JavaScript event handler for updating range value
                    'oninput' => 'updateRangeValue(this.value)',
                ],
            ])
            // Add user field
            ->add('user', EntityType::class, [
                'class' => User::class,         // Entity class to use for the choices
                'choice_label' => function (User $user) {
                    // Display user names
                    return $user->getFirstname() . ' ' . $user->getLastname();
                },
                'placeholder' => '',            // Placeholder text for the select input
                'required' => false,            // Field is optional
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'user',            // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'id' => 'user',             // HTML 'id' attribute for the select input
                ],
            ])
            // Add chooseTask field
            ->add('chooseTask', CheckboxType::class, [
                'mapped' => false,                  // Field is not mapped to the entity
                'required' => false,                // Field is optional
                'label'    => 'Choose the task ?',  // Label text
                'label_attr' => [
                    'class' => 'form-check-label',  // CSS class for the label
                    'for' => 'flexSwitchCheck',     // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-check-input',  // CSS class for the checkbox
                    'id' => 'flexSwitchCheck',      // HTML 'id' attribute for the checkbox
                ]
            ]);
    }

    // Configure options for this form type
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,    // Associate form with the Task entity
        ]);
    }
}
