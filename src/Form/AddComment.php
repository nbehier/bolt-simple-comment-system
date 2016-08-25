<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem\Form;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reply form types
 *
 * @author Nicolas Béhier-Dévigne
 */
class AddComment extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body',   'textarea', ['label'            => false,
                                              'attr'        => [
                                                  'style'       => 'height: 150px;',
                                                  'placeholder' => Trans::__('Write a comment…')
                                              ],
                                              'constraints' => [
                                                  new Assert\NotBlank(),
                                                  new Assert\Length(['min' => 2]),
                                             ], ])
            ->add('name',   'text',     ['label'         => 'Display name',
                                              'required' => true,
                                              'attr'        => [
                                                  'placeholder' => Trans::__('Your name')
                                              ],
                                              'constraints' => [
                                                  new Assert\NotBlank(),
                                                  new Assert\Length(['min' => 2]),
                                             ], ])
            ->add('email',  'email',    ['label'         => 'Email',
                                              'required' => true,
                                              'attr'        => [
                                                  'placeholder' => Trans::__('Your email')
                                              ],
                                             ])
            ->add('linked_entity',  'hidden')
            ->add('notify', 'checkbox', ['label'         => Trans::__('Notify me of next comments'),
                                              'data'     => true,
                                              'required' => false, ])
            ->add('post',   'submit',   ['label' => Trans::__('Post reply')]);
    }

    public function getName()
    {
        return 'addcomment';
    }

//     public function setDefaultOptions(OptionsResolverInterface $resolver)
//     {
//         $resolver->setDefaults(array(
//             'data_class' => 'Bolt\Extension\Bolt\BoltBB\Entity\Reply',
//         ));
//     }
}
