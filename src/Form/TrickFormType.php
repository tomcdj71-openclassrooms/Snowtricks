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
use Symfony\Component\Validator\Constraints as Assert;
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
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 5,
                        'max' => 100,
                        'minMessage' => $this->translator->trans('The title should be at least ').'{{ limit }}'.$this->translator->trans(' characters.'),
                        'maxMessage' => $this->translator->trans('The title should not exceed ').'{{ limit }}'.$this->translator->trans(' characters.'),
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description'),
                'attr' => [
                    'class' => 'block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500',
                    'rows' => 6,
                    'placeholder' => $this->translator->trans('Write your article here...'),
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 10,
                        'max' => 2000,
                        'minMessage' => $this->translator->trans('The description should be at least ').'{{ limit }}'.$this->translator->trans(' characters.'),
                        'maxMessage' => $this->translator->trans('The description should not exceed ').'{{ limit }}'.$this->translator->trans(' characters.'),
                    ]),
                ],
            ])
            ->add('featuredImage', FileType::class, [
                'label' => $this->translator->trans('Featured Image'),
                'mapped' => false,
                'required' => false,
                'constraints' => $options['edit_mode'] ? [] : [
                    new Assert\NotBlank([
                        'message' => $this->translator->trans('Please upload an image as the featured image.'),
                    ]),
                    new Assert\Image([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => $this->translator->trans('Please upload a valid image (jpg or png).'),
                    ]),
                ],
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
                'required' => !(bool) $options['edit_mode'],
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
            'edit_mode' => false,
        ]);
    }
}
