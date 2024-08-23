<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\TaskList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'label_attr' => [
                    'class' => 'form-label',
                    'for' => 'name',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'name',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'empty_data' => '',
                'label_attr' => [
                    'class' => 'form-label',
                    'for' => 'description',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'description',
                ],
            ])
            ->add('due_date', DateType::class, [
                'widget' => 'choice',
                'placeholder' => '', // Le choix par défaut qui correspond à null
                'required' => false, // Rendre ce champ optionnel
                'label_attr' => [
                    'class' => 'form-label',
                    'for' => 'due_date',
                ],
                'attr' => [
                    'id' => 'due_date',
                ],
            ])
            ->add('progress', RangeType::class, [
                'label_attr' => [
                    'class' => 'form-label',
                    'for' => 'progress',
                ],
                'attr' => [
                    'class' => 'form-range',
                    'id' => 'progress',
                    'min' => 0,
                    'max' => 100,
                    'oninput' => 'updateRangeValue(this.value)', // Utilisé pour mettre à jour la valeur en temps réel
                ],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFirstname() . ' ' . $user->getLastname();
                },
                'placeholder' => '', // Le choix par défaut qui correspond à null
                'required' => false, // Rendre ce champ optionnel
                'label_attr' => [
                    'class' => 'form-label',
                    'for' => 'user',
                ],
                'attr' => [
                    'id' => 'user',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
