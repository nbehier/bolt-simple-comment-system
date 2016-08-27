<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem\Controller;

use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Entity\Comment;
use Bolt\Extension\Leskis\BoltSimpleCommentSystem\Form\CommentForm;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class.
 *
 * @author Nicolas Béhier-Dévigne
 */
class CommentController implements ControllerProviderInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * Initiate the controller with Bolt Application instance and extension config.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Specify which method handles which route.
     *
     * Base route/path is '/bscs-comment'
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        /** @var $ctr \Silex\ControllerCollection */
        $ctr = $app['controllers_factory'];

        // /bscs-comment/save
        $ctr->match('/save', [$this, 'save'])
            ->bind('bscs-comment-save')
            ->method('POST');

        return $ctr;
    }

    /**
     * Handles POST requests on /bscs-comment/save
     *
     * @param Request $request
     *
     * @return Response
     */
    public function save(Application $app, Request $request)
    {
        $comment = new Comment();
        $form    = $app['form.factory']->createBuilder(new CommentForm(), $comment)
                                       ->getForm();

        // Handle the form request data
        $form->handleRequest($request);

        if ($form->isValid() ) {
            $values = [
                'datecreated'         => date('Y-m-d H:i:s'),
                'datepublish'         => date('Y-m-d H:i:s'),
                'status'              => 'published',
                'author_email'        => $comment->getAuthorEmail(),
                'author_display_name' => $comment->getAuthorDisplayName(),
                'body'                => $comment->getBody(),
                'linked_entity'       => $comment->getLinkedEntity(),
                'notify'              => $comment->getNotify()
            ];

            if (! $config['features']['comments']['default_approve']) {
                $values['status'] = 'draft';
            }

            $record = $app['storage']->getEmptyContent('comments');
            $record->setValues($values);
            $id = $app['storage']->saveContent($record);

            if ($id === false) {
                $request->getSession()->getFlashBag()->add('error', 'Saving error in comment form');
            }
        }
        else {
            $request->getSession()->getFlashBag()->add('error', 'Validating error in comment form');
        }

        return $app->redirect($request->headers->get('referer') );
    }
}
