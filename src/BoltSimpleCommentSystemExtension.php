<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Controller\CommentController;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Entity\Comment;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Form\CommentForm;
use Bolt\Extension\SimpleExtension;
use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * BoltSimpleCommentSystem extension class.
 *
 * @author Nicolas Béhier-Dévigne
 */
class BoltSimpleCommentSystemExtension extends SimpleExtension
{

    /**
     * The callback function when {{ bscs_comments() }} is used in a template.
     *
     * @return string
     */
    public function listCommentsFunction($context)
    {
        $config = $this->getConfig();
        $html   = $this->renderTemplate($config['templates']['list'], $context);

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * The callback function when {{ bscs_add_comment() }} is used in a template.
     *
     * @return string
     */
    public function addCommentFunction($context)
    {
        $app     = $this->getContainer();
        $config  = $this->getConfig();
        $comment = new Comment();
        $comment->setLinkedEntity($context['slug']);

        // @see https://openclassrooms.com/courses/developpez-votre-site-web-avec-le-framework-symfony2/creer-des-formulaires-avec-symfony2
        $form = $app['form.factory']->createBuilder(new CommentForm(), $comment)
                                    ->setAction($app['url_generator']->generate('bscs-comment-save') )//'/bscs-comment/save')
                                    ->setMethod('POST')
                                    ->getForm();

        // Render the Twig
        $html = $app['render']->render(
            $config['templates']['form'], [
                'form' => $form->createView()
            ]
        );
        return new \Twig_Markup($html, 'UTF-8');
    }

    public function gravatarTwigFilter($input)
    {
        return md5(trim(strtolower($input ) ) );
    }

    /**
     * {@inheritdoc}
     *
     * Mount the ExampleController class to all routes that match '/example/url/*'
     *
     * To see specific bindings between route and controller method see 'connect()'
     * function in the ExampleController class.
     */
    protected function registerFrontendControllers()
    {
        $config = $this->getConfig();
        return [
            '/bscs-comment' => new CommentController($config),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
        $assets = [];
        $config = $this->getConfig();

        if ( $config['assets']['frontend']['load_css'] ) {
            $main_css = new Stylesheet();
            $main_css->setFileName('simplecommentsystem.css');

            $assets[] = $main_css;
        }

        if ( $config['assets']['frontend']['load_js'] ) {
            $main_js = new JavaScript();
            $main_js->setFileName('simplecommentsystem.js')
                ->setLate(true)
                ->setPriority(99);

            $assets[] = $main_js;
        }

        if (   $config['assets']['frontend']['load_js']
            && $config['features']['gravatar']['enabled'] ) {
            $gravatar_js = new JavaScript();
            $gravatar_js->setFileName('blueimp-md5/js/md5.min.js')
                ->setLate(true)
                ->setPriority(98);

            $assets[] = $gravatar_js;
        }

        return $assets;
    }

    protected function registerServices(Application $app)
    {
        $app['bolt-simple-comment.config'] = $app->share(
           function ($app) {
               return new ParameterBag($this->getConfig() );
           }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return ['templates'];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        return [
            'bscs_comments'    => 'listCommentsFunction',
            'bscs_add_comment' => 'addCommentFunction'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFilters()
    {
        return [
            'bscs_gravatar' => 'gravatarTwigFilter'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'features' => [
                'comments' => [
                    'order'           => 'asc',
                    'default_approve' => true
                ],
                'gravatar' => [
                    'enabled' => false,
                    'url'     => 'https://www.gravatar.com/avatar/XXX?s=40&d=mm'
                ],
                'debug' => [
                    'enabled' => true,
                    'address' => 'noreply@example.com'
                ],
                'notify' => [
                    'enabled' => true,
                    'email'   => [
                        'from_name'     => 'Your website',
                        'from_email'    => 'your-email@your-website.com',
                        'replyto_name'  => '',
                        'replyto_email' => ''
                    ]
                ]
            ],

            'templates' => [
                'form'         => 'form_comment.twig',
                'list'         => 'list_comments.twig',
                'emailbody'    => 'email_body.twig',
                'emailsubject' => 'email_subject.twig'
            ],

            'assets' => [
                'frontend' => [
                    'load_js'  => true,
                    'load_css' => true
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return 'Simple comment system';
    }
}
