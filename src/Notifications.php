<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem;

use Silex;
use \Bolt\Legacy\Content;
//use \Bolt\Storage\Entity\Content;

/**
 * Notification class
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class Notifications
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
     * @var bool
     */
    private $debug;

    /**
     * @var string
     */
    private $debug_address;

    /**
     * @var \Bolt\Content
     */
    private $record;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $emailField;

    /**
     * @var string
     */
    private $subjectTpl;

    /**
     * @var string
     */
    private $bodyTpl;

    /**
     * @var string
     */
    private $from_name;

    /**
     * @var string
     */
    private $from_email;

    /**
     * @var string
     */
    private $replyto_name;

    /**
     * @var string
     */
    private $replyto_email;

    /**
     * @param Silex\Application $app
     * @param \Bolt\Content     $record
     */
    public function __construct(Silex\Application $app, array $config, Content $record)
    {
        $this->app    = $app;
        $this->config = $config;
        $this->record = $record;

        $this->setVars();
    }

    /**
     *
     */
    public function doNotification($recipients)
    {
        // Sort out the "to whom" list
        if ($this->debug) {
            $this->recipients = [[
                'author_name'  => 'Test',
                'author_email' => $this->debug_address
            ]];
        } else {
            // Get the subscribers
            $this->recipients = $recipients;
        }

        foreach ($this->recipients as $recipient) {
            // Get the email template
            $this->doCompose($recipient);

            $this->doSend($this->message, $recipient);
        }
    }

    /**
     * Compose the email data to be sent
     */
    private function doCompose($recipient)
    {
        /*
         * Subject
         */
        $html = $this->app['render']->render($this->subjectTpl, [
            'record' => $this->record
        ]);
        $subject = new \Twig_Markup($html, 'UTF-8');

        /*
         * Body
         */
        $html = $this->app['render']->render($this->bodyTpl, [
            'record'    => $this->record,
            'recipient' => $recipient
        ]);
        $body = new \Twig_Markup($html, 'UTF-8');


        /*
         * Build email
         */
        $this->message = $this->app['mailer']
                ->createMessage('message')
                ->setSubject($subject)
                ->setFrom([$this->from_email => $this->from_name])
                ->setBody(strip_tags($body))
                ->addPart($body, 'text/html');

        // Add reply to if necessary
        if (! empty($this->replyto_email) ) {
            $this->message->setReplyTo([$this->replyto_email => $this->replyto_name]);
        }
    }

    /**
     * Send a notification to a single user
     *
     * @param \Swift_Message $message
     * @param array          $recipient
     */
    private function doSend(\Swift_Message $message, $recipient)
    {
        // Set the recipient for *this* message
        $emailTo = $recipient['author_email'];
        $message->setTo($emailTo);

        if ($this->app['mailer']->send($message)) {
            $this->app['logger.system']->info("Sent BoltSimpleCommentSystem notification to {$emailTo}", ['event' => 'extensions']);
        } else {
            $this->app['logger.system']->error("Failed BoltSimpleCommentSystem notification to {$emailTo}", ['event' => 'extensions']);
        }
    }

    /**
     * Set Config vars
     */
    private function setVars()
    {
        // Set ContentType from record
        $this->contentType = $this->record->getContenttype();

        // Set Debug
        $this->debug         = $this->config['features']['notify']['debug']['enabled'];
        $this->debug_address = $this->config['features']['notify']['debug']['address'];

        // Get Templates
        $this->subjectTpl = $this->config['templates']['emailsubject'];
        $this->bodyTpl    = $this->config['templates']['emailbody'];

        // Get Sender
        $this->from_name     = $this->config['features']['notify']['email']['from_name'];
        $this->from_email    = $this->config['features']['notify']['email']['from_email'];
        $this->replyto_name  = $this->config['features']['notify']['email']['replyto_name'];
        $this->replyto_email = $this->config['features']['notify']['email']['replyto_email'];
    }
}
