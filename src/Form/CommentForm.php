<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem\Form;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

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
                    'data'     => false,
                    'required' => false
                  ]
            )
            ->add('post',
                  'submit',
                  [
                    'label' => Trans::__('Post comment')
                  ]
            );

        // @todo
        if ( false ) {
            $builder
                ->add(  'question',
                    'text',
                    [
                        'label'    => $aQuestion['question'],
                        'required' => true,
                        'mapped'   => false,
                        'constraints' => [
                          new Assert\NotBlank(),
                          new Assert\Callback(['methods' => 'isQuestionCorrect']),
                        ],
                    ]
                )
                ->add('questionuniq',
                    'hidden',
                    [
                        'data'   => $uniqID,
                        'mapped' => false
                    ]
                );
        }
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

    // @see http://www.lafermeduweb.net/tutorial/la-validation-de-donnees-avec-symfony2-p101.html
    public function isQuestionCorrect(ExecutionContext $context)
    {
        // $badWords = "#poule|poulette|cocotte#i"; // FDW FTW

        // // Nous testons si nos propriétés contiennent ces mots reservés
        // if (preg_match($badWords, $this->getTitle())) {
        //     $propertyPath = $context->getPropertyPath() . '.title';
        //     $context->setPropertyPath($propertyPath);
        //     $context->addViolation('Vous utilisez un mot réservé dans le titre !', array(), null); // On renvoi l'erreur au contexte
        // }
        // if (preg_match($badWords, $this->getDescription())) {
        //     $propertyPath = $context->getPropertyPath() . '.description';
        //     $context->setPropertyPath($propertyPath);
        //     $context->addViolation('Vous utilisez un mot réservé dans la description !', array(), null);
        // }
    }

    public function chooseARandomQuestion($config)
    {
        if (array_key_exists('list', $config['features']['questions']) ) {
            $questions = $config['features']['questions']['list'];
            $nbOfQuest = count($questions);

            if ( $nbOfQuest > 0 ) {
                $idx = mt_rand(0, $nbOfQuest - 1);
                return $questions[$idx];
            }
        }

        return false;
    }

    public function findAQuestion($config, $sUniqQuestion)
    {
        if (array_key_exists('list', $config['features']['questions']) ) {
            $questions = $config['features']['questions']['list'];

            foreach ($questions as $question) {
                if (   $question['question'] != ''
                    && $this->uniqID($question['question']) == $sUniqQuestion) {
                    return $question;
                }
            }
        }

        return false;
    }

    private function uniqID($input)
    {
        return md5(trim(strtolower($input ) ) );
    }
}
