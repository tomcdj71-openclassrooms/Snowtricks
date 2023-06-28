<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentFormType extends AbstractType
{
    private \Symfony\Contracts\Translation\TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500',
                    'rows' => 6,
                    'placeholder' => $this->translator->trans('Write your comment here...'),
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 10,
                        'max' => 2000,
                        'minMessage' => $this->translator->trans('The comment should be at least ').'{{ limit }}'.$this->translator->trans(' characters.'),
                        'maxMessage' => $this->translator->trans('The comment should not exceed ').'{{ limit }}'.$this->translator->trans(' characters.'),
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
