<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem\Form;

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
                                              'attr'        => ['style' => 'height: 150px;'],
                                              'constraints' => [
                                                  new Assert\NotBlank(),
                                                  new Assert\Length(['min' => 2]),
                                             ], ])
            ->add('name',   'text',     ['label'         => 'Display name',
                                              'required' => true,
                                              'constraints' => [
                                                  new Assert\NotBlank(),
                                                  new Assert\Length(['min' => 2]),
                                             ], ])
            ->add('email',  'email',    ['label'         => 'Email',
                                              'required' => true,
                                             ])
            ->add('linked_entity',  'hidden')
            ->add('notify', 'checkbox', ['label'         => 'Notify me of updates to this topic',
                                              'data'     => true,
                                              'required' => false, ])
            ->add('post',   'submit',   ['label' => 'Post reply']);
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
