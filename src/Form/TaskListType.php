<?php

namespace App\Form;

// use App\Entity\User;
use App\Entity\TaskList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
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
            ]);

        // if ($options['include_password']) {
        //     $builder->add('user', EntityType::class, [
        //                 'class' => User::class,
        //                 'choice_label' => function (User $user) {
        //                     return $user->getFirstname() . ' ' . $user->getLastname();
        //                 },
        //             ]);
        // }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskList::class,
            // 'include_password' => false, // Par défaut, le champ password n'est pas inclus
        ]);
    }
}
