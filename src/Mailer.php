<?php

namespace connectionsbv\smtp2go;

use SMTP2GO\ApiClient;
use SMTP2GO\Service\Mail\Send as MailSend;
use SMTP2GO\Types\Mail\CustomHeader;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;

class Mailer extends BaseMailer
{
    public $token;
    public $messageClass = 'connectionsbv\smtp2go\Message';

    /**
     * @param yii\mail\MessageInterface $message
     * @return messageId on success, null on failure
     * @throws InvalidConfigException
     */
    public function sendMessage($message)
    {
        if ($this->token === null) {
            throw new InvalidConfigException('Token is missing');
        }

        $sendService = new MailSend($message->getFrom(), $message->getTo(), $message->getSubject(), $message->getTextBody());
        if ($message->getHtmlBody()) {
            $sendService->setHtmlBody($message->getHtmlBody());
        }
        $cc = $message->getCc();
        if (!empty($cc->getItems())) {
            foreach ($cc as $to) {
                $sendService->addAddress('cc', $to);
            }
        }
        $bcc = $message->getBcc();
        if (!empty($bcc->getItems())) {
            foreach ($bcc as $to) {
                $sendService->addAddress('bcc', $to);
            }
        }
        $attachments = $message->getAttachments();
        if ($attachments) {
            $sendService->setAttachments($attachments);
        }
        $replyTo = $message->getReplyTo();
        if ($replyTo) {
            $sendService->addCustomHeader(new CustomHeader('Reply-To', $replyTo));
        }

        $apiClient = new ApiClient($this->token);
        $success = $apiClient->consume($sendService);

        $responseBody = $apiClient->getResponseBody();
        if (!empty($responseBody)) {
            if (!empty($responseBody->data->succeeded)) {
                return $responseBody->data->email_id;
            }
            if (!empty($responseBody->data->error)) {
                throw new InvalidConfigException('SMTP2GO: '.$responseBody->data->error);
            }
        }

        return false;
    }

    public function send($message)
    {
        return $this->sendMessage($message);
    }
}
