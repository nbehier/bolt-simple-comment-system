<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem\Form;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment form
 *
 * @author Nicolas Béhier-Dévigne
 */
class CommentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body',
                  'textarea',
                  [
                    'label' => false,
                    'attr'  => [
                      'style'       => 'height: 150px;',
                      'placeholder' => Trans::__('Write a comment…')
                    ],
                    'constraints' => [
                      new Assert\NotBlank(),
                      new Assert\Length(['min' => 2]),
                    ],
                  ]
            )
            ->add('author_display_name',
                  'text',
                  [
                    'label'    => 'Display name',
                    'required' => true,
                    'attr'     => [
                      'placeholder' => Trans::__('Your name')
                    ],
                    'constraints' => [
                      new Assert\NotBlank(),
                      new Assert\Length(['min' => 2]),
                    ],
                  ]
            )
            ->add('author_email',
                  'email',
                  [
                    'label'    => 'Email',
                    'required' => true,
                    'attr'     => [
                      'placeholder' => Trans::__('Your email')
                    ],
                  ]
            )
            ->add('guid',
                  'hidden'
            )
            ->add('notify',
                  'checkbox',
                  [
                    'label'    => Trans::__('Notify me of next comments'),
                    'data'     => true,
                    'required' => false
                  ]
            )
            ->add('post',
                  'submit',
                  [
                    'label' => Trans::__('Post comment')
                  ]
            );
    }

    public function getName()
    {
        return 'addcomment';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bolt\Extension\Leskis\BoltSimpleCommentSystem\Entity\Comment',
        ));
    }
}
