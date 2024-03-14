<?php

namespace App\Form;

use App\Entity\Articles;
use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ArticlesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('date')
            ->add('description')
            ->add('content')
            ->add('categories', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'id',
                'multiple' => true,
            ]);
        // ->add('cover_image', FileType::class, [
        //     'label' => "Importez l'image de couverture de l'article",
        //     'mapped' => false,
        //     'required' => false,
        //     'constraints' => [
        //         new File([
        //             'maxSize' => '1024k',
        //             'mimeTypes' => [
        //                 'image/jpeg',
        //                 'image/png',
        //             ],
        //             'mimeTypesMessage' => 'Please upload a valid document',
        //         ])
        //     ],
        // ])
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Articles::class,
        ]);
    }
}
