<?php

namespace App\Mail;

class Message
{
    public array $to;
    public string $subject;
    public string $body;
    public string $altBody;
    public bool $isHtml = true;
    public string $view;
    public array $viewData = [];
    public array $attachments = [];

    /**
     * Sets the recipient of the message.
     *
     * @param string $address The recipient's email address.
     * @param string $name The recipient's name.
     * @return $this
     */
    public function to(string $address, string $name = ''): static
    {
        $this->to = ['address' => $address, 'name' => $name];
        return $this;
    }

    /**
     * Sets the subject of the message.
     *
     * @param string $subject The subject of the message.
     * @return $this
     */
    public function subject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Sets the body of the message.
     *
     * @param string $body The body of the message.
     * @return $this
     */
    public function body(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Sets the alternative body of the message.
     *
     * @param string $altBody The alternative body of the message.
     * @return $this
     */
    public function altBody(string $altBody): static
    {
        $this->altBody = $altBody;
        return $this;
    }

    /**
     * Sets whether the body of the message is HTML.
     *
     * @param bool $isHtml Whether the body is HTML.
     * @return $this
     */
    public function isHtml(bool $isHtml = true): static
    {
        $this->isHtml = $isHtml;
        return $this;
    }

    /**
     * Sets the view to use for the message body.
     *
     * @param string $view The view to use.
     * @param array $data The data to pass to the view.
     * @return $this
     */
    public function view(string $view, array $data = []): static
    {
        $this->view = $view;
        $this->viewData = $data;
        return $this;
    }

    /**
     * Attaches a file to the message.
     *
     * @param string $filePath The path to the file to attach.
     * @return $this
     */
    public function attach(string $filePath): static
    {
        $this->attachments[] = $filePath;
        return $this;
    }
}