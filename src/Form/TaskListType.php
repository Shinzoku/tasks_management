<?php

namespace App\Form;

use App\Entity\TaskList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskListType extends AbstractType
{
    // Build the form with the specified fields and their options
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Add name field
            ->add('name', TextType::class, [
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
            ])
            // Add description field
            ->add('description', TextareaType::class, [
                'required' => true,             // Field is mandatory
                'empty_data' => '',             // Default value if the field is empty
                'label_attr' => [
                    'class' => 'form-label',    // CSS class for the label
                    'for' => 'description',     // HTML 'for' attribute for the label
                ],
                'attr' => [
                    'class' => 'form-control',  // CSS class for the textarea
                    'id' => 'description',      // HTML 'id' attribute for the textarea
                ],
            ]);
    }

    // Configure options for this form type
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskList::class,    // Associate form with the TaskList entity
        ]);
    }
}
