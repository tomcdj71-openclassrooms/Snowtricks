<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Dropzone\Form\DropzoneType;

class TrickFormType extends AbstractType
{
    private \Symfony\Contracts\Translation\TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Awesome Title'),
                ]])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description'),
                'attr' => [
                    'class' => 'block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500',
                    'rows' => 6,
                    'placeholder' => $this->translator->trans('Write your article here...'),
                ]]
            )
            ->add('featuredImage', FileType::class, [
                'label' => $this->translator->trans('Featured Image'),
                'mapped' => false,
                'required' => true,
            ])
            ->add('group', EntityType::class, [
                'label' => $this->translator->trans('Group'),
                'class' => Group::class,
                'choice_label' => 'name',
            ])
            ->add('images', DropzoneType::class, [
                'label' => 'Images',
                'attr' => [
                    'placeholder' => $this->translator->trans('Click to upload or drag and drop images here'),
                    'accept' => 'image/*',
                ],
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('videos', CollectionType::class, [
                'label' => 'Videos',
                'entry_type' => VideoType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
