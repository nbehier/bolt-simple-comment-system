<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Controller\AjaxController;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Form\AddComment;
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
    public function listCommentsFunction()
    {
        $context = [
            'something' => mt_rand(),
        ];
        $html = $this->renderTemplate('list_comments.twig', $context);
        return new \Twig_Markup($html, 'UTF-8');
    }
    /**
     * The callback function when {{ bscs_add_comment() }} is used in a template.
     *
     * @return string
     */
    public function addCommentFunction($context)
    {
        $app = $this->getContainer();

        $data = [
            'csrf_protection' => true,
            'linked_entity'   => $context['slug'],
        ];
//https://openclassrooms.com/courses/developpez-votre-site-web-avec-le-framework-symfony2/creer-des-formulaires-avec-symfony2
        $form = $app['form.factory']->createBuilder(new AddComment(), $data)
                                    ->getForm();

        // Handle the form request data
        //$form->handleRequest($request);

        // Render the Twig
        $html = $app['render']->render(
            'form_comment.twig', [
                'form' => $form->createView()
            ]
        );
        return new \Twig_Markup($html, 'UTF-8');
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
        $app = $this->getContainer();
        $config = $this->getConfig();
        return [
            '/bscs-ajax' => new AjaxController($config),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
        // https://docs.bolt.cm/3.0/extensions/building/web-assets#snippets
        return [
            (new JavaScript('blueimp-md5/js/md5.min.js'))->setLate(true)->setPriority(98),
            (new JavaScript('simplecommentsystem.js'))->setLate(true)->setPriority(99),
            (new Stylesheet('simplecommentsystem.css'))
        ];
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
            'bscs_comments' => 'listCommentsFunction',
            'bscs_add_comment' => 'addCommentFunction'
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
