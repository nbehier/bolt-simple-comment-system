<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Events\StorageEvent;
use Bolt\Events\StorageEvents;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Controller\CommentController;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Entity\Comment;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Form\CommentForm;
use Bolt\Extension\SimpleExtension;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
        $comment->setLinkedEntity($context['guid']);

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
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        // Pre-save hook
        $dispatcher->addListener(StorageEvents::PRE_SAVE, [$this, 'hookPreSave']);
    }

     /**
     * Pre-save hook
     * @param \Bolt\Events\StorageEvent $event
     */
    public function hookPreSave(StorageEvent $event)
    {
        $app         = $this->getContainer();
        $config      = $this->getConfig();
        $contenttype = $event->getContentType();

        if (   empty($contenttype)
            || ! $config['features']['notify']['enabled']
            || $contenttype != 'comments' ) {
            return;
        }

        // Get the record : Bolt\Storage\Entity\Content
        $record = $event->getContent();

        // Test if newly published
        $contentNewlyPublished = false;
        if (   $event->isCreate()
            && $record->getStatus() == 'published') {
            $contentNewlyPublished = true;
        }
        else if ($record->getStatus() == 'published') {
            // @todo : check if notification already sent
            // use a temporary file or a db table ?
            $repo = $app['storage']->getRepository($contenttype);
            $oldRecord = $repo->find($record->getId() );
            if (   !empty($oldRecord)
                && $oldRecord->getStatus() != 'published') {
                $contentNewlyPublished = true;
            }
        }

        if ($contentNewlyPublished) {

            // Launch the notification
            $notify = new Notifications($app, $config, $record);

            // Search subscribers
            try {
                $aSubscribers = $this->getSubscribers($record->getLinkedEntity() );

                // Send email foreach subscriber
                $notify->doNotification($aSubscribers);
            } catch (\Exception $e) {
                $app['logger.system']->error(sprintf("BoltSimpleCommentSystemExtension notifications can't be sent - %s", $e->getMessage() ), ['event' => 'extensions']);
                return;
            }
        }

        return;
    }

    /**
     * Get Subscribers for notifications
     * @return array email
     */
    private function getSubscribers($linked_entity)
    {
        $app          = $this->getContainer();
        $config       = $this->getConfig();
        $aSubscribers = false;

        $repo      = $app['storage']->getRepository('comments');
        $aComments = $repo->findBy([
            'linked_entity' => $linked_entity,
            'status'        => 'published',
            'notify'        => true
        ]);

        if ($aComments) {
            // @todo Do not sent email to owner
            $aSubscribers = [];
            foreach ($aComments as $aComment) {
                $emailUniq = $this->gravatarTwigFilter($aComment['author_email']);
                $aSubscribers[$emailUniq] = [
                    'email' => $aComment['author_email'],
                    'name'  => $aComment['author_display_name']
                ];
            }
        }

        return $aSubscribers;
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

        if (   $config['assets']['frontend']['load_js']
            && $config['features']['emoticons']['enabled'] ) {
            $emoticons_js = new JavaScript();
            $emoticons_js->setFileName('jQuery-CSSEmoticons/javascripts/jquery.cssemoticons.min.js')
                ->setLate(true)
                ->setPriority(97);

            $emoticons_css = new Stylesheet();
            $emoticons_css->setFileName('jQuery-CSSEmoticons/stylesheets/jquery.cssemoticons.css');

            $assets[] = $emoticons_css;
            $assets[] = $emoticons_js;
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
                    'enabled' => true,
                    'url'     => 'https://www.gravatar.com/avatar/XXX?s=40&d=mm'
                ],
                'emoticons' => [
                    'enabled' => true,
                    'animate' => false
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
