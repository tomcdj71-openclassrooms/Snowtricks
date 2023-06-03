<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationFormType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'autocomplete' => 'email',
                    'autofocus' => true,
                    'placeholder' => $this->translator->trans('yourname@email.com'),
                ],
                'label' => $this->translator->trans('Your email'),
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('Please enter a valid email address.'),
                    ]),
                ],
                'invalid_message' => $this->translator->trans('Please enter a valid email address.'),
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => $this->translator->trans('You should agree to our terms.'),
                    ]),
                ],
            ])
            ->add('username', TextType::class, [
                'label' => $this->translator->trans('Username'),
                'attr' => [
                    'placeholder' => $this->translator->trans('JohnDoe_25'),
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('Please enter an username.'),
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => '******',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->translator->trans('Please enter a password'),
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => $this->translator->trans('Your password should be at least').' {{ limit }} '.$this->translator->trans('characters'),
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    'label' => $this->translator->trans('Password'),
                ],
                'second_options' => [
                    'label' => $this->translator->trans('Confirm Password'),
                ],
                'invalid_message' => $this->translator->trans('The password fields must match.'),
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
