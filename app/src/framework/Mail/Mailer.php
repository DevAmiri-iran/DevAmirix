<?php

namespace App\Mail;

use Exception;
use Swift_Attachment;
use Swift_IoException;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_SendmailTransport;
use Swift_SpoolTransport;
use Swift_FileSpool;

class Mailer
{
    protected Swift_Mailer $mailer;
    protected mixed $logChannel;
    protected mixed $config;

    /**
     * Initializes the mailer with the specified configuration.
     *
     * @throws Exception If an unsupported mail driver is specified.
     */
    public function __construct()
    {
        $config = config('mail');
        $this->config = $config;

        $driver = $config['default'];
        $mailerConfig = $config['mailers'][$driver];

        switch ($mailerConfig['transport']) {
            case 'smtp':
                $transport = (new Swift_SmtpTransport($mailerConfig['host'], $mailerConfig['port']))
                    ->setUsername($mailerConfig['username'])
                    ->setPassword($mailerConfig['password'])
                    ->setEncryption($mailerConfig['encryption']);
                break;

            case 'sendmail':
                $transport = new Swift_SendmailTransport($mailerConfig['path']);
                break;

            case 'log':
                $this->logChannel = $mailerConfig['channel'];
                $transport = $this->createLogTransport();
                break;

            default:
                throw new Exception("Unsupported mail driver: {$mailerConfig['transport']}");
        }

        $this->mailer = new Swift_Mailer($transport);
    }

    /**
     * Sends the given message.
     *
     * @param Message $message The message to send.
     * @return bool|string True if the message was sent successfully, otherwise an error message.
     * @throws Exception
     */
    public function send(Message $message): bool|string
    {
        $config = $this->config;

        $swiftMessage = (new Swift_Message($message->subject))
            ->setFrom([$config['from']['address'] => $config['from']['name']])
            ->setTo([$message->to['address'] => $message->to['name']])
            ->setBody($message->body, $message->isHtml ? 'text/html' : 'text/plain');

        if ($message->altBody) {
            $swiftMessage->addPart($message->altBody, 'text/plain');
        }

        if ($message->view) {
            $viewContent = $this->renderView($message->view, $message->viewData);
            $swiftMessage->setBody($viewContent, $message->isHtml ? 'text/html' : 'text/plain');
        }

        if (!empty($message->attachments)) {
            foreach ($message->attachments as $filePath) {
                $swiftMessage->attach(Swift_Attachment::fromPath($filePath));
            }
        }

        try {
            $this->mailer->send($swiftMessage);
            return true;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$e->getMessage()}";
        }
    }

    /**
     * Creates a new Message instance.
     *
     * @return Message A new message instance.
     */
    public function Message(): Message
    {
        return new Message();
    }

    /**
     * Renders a view into a string.
     *
     * @param string $view The name of the view to render.
     * @param array $data The data to pass to the view.
     * @return bool|string The rendered view content.
     * @throws Exception If the view rendering fails.
     */
    protected function renderView(string $view, array $data = []): bool|string
    {
        ob_start();
        view($view, $data);
        return ob_get_clean();
    }

    /**
     * Creates a log transport for mailing.
     *
     * @return Swift_SpoolTransport The created log transport.
     * @throws Swift_IoException If an error occurs while creating the transport.
     */
    protected function createLogTransport(): Swift_SpoolTransport
    {
        $spool = new Swift_FileSpool($this->logChannel);
        return new Swift_SpoolTransport($spool);
    }
}