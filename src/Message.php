<?php

namespace connectionsbv\smtp2go;

use Exception;
use SMTP2GO\Collections\Mail\AddressCollection;
use SMTP2GO\Collections\Mail\AttachmentCollection;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Types\Mail\Attachment;
use yii\base\NotSupportedException;
use yii\mail\BaseMessage;

class Message extends BaseMessage
{
    protected $from;
    protected $to = [];
    protected $replyTo;
    protected $cc = [];
    protected $bcc = [];
    protected $returnPath;
    protected $subject;
    protected $textBody;
    protected $htmlBody;
    protected $attachments = [];
    protected $tag;
    protected $trackOpens = false;
    protected $clickTracking = false;
    protected $transactional = true;
    protected $showToInHeader = true;
    protected $headers = [];
    protected $charset = 'utf-8';

    public function getCharset()
    {
        return $this->charset;
    }

    public function setCharset($charset)
    {
        throw new NotSupportedException();
    }

    public function getFrom(): Address
    {
        $from = null;
        foreach ($this->from as $email => $name) {
            if (is_int($email)) { // no name
                $email = $name;
                $name = null;
            }
            $from = new Address($email, $name ?? '');
        }

        return $from;
    }

    public function setFrom($from)
    {
        if (is_string($from) === true) {
            $from = [$from];
        }
        $this->from = $from;

        return $this;
    }

    public function getTo()
    {
        $addresses = [];
        foreach ($this->to as $email => $name) {
            if (is_int($email)) { // no name
                $email = $name;
                $name = null;
            }
            $addresses[] = new Address($email, $name ?? '');
        }

        return new AddressCollection($addresses);
    }

    public function setTo($to)
    {
        if (is_string($to) === true) {
            $to = [$to];
        }
        $this->to = $to;

        return $this;
    }

    /**
     * @param array|string $emailsData email can be defined as string. In this case no transformation is done
     *                                 or as an array ['email@test.com', 'email2@test.com' => 'Email 2']
     * @return string|null
     * @since XXX
     */
    public static function stringifyEmails($emailsData)
    {
        $emails = null;
        if (empty($emailsData) === false) {
            if (is_array($emailsData) === true) {
                foreach ($emailsData as $key => $email) {
                    if (is_int($key) === true) {
                        $emails[] = $email;
                    } else {
                        if (preg_match('/[.,:]/', $email) > 0) {
                            $email = '"'.$email.'"';
                        }
                        $emails[] = $email.' '.'<'.$key.'>';
                    }
                }
                $emails = implode(', ', $emails);
            } elseif (is_string($emailsData) === true) {
                $emails = $emailsData;
            }
        }

        return $emails;
    }

    public function getReplyTo()
    {
        return self::stringifyEmails($this->replyTo);
    }

    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getCc()
    {
        $addresses = [];
        foreach ($this->cc as $email => $name) {
            if (is_int($email)) { // no name
                $email = $name;
                $name = null;
            }
            $addresses[] = new Address($email, $name ?? '');
        }

        return new AddressCollection($addresses);
    }

    public function setCc($cc)
    {
        if (is_string($cc) === true) {
            $cc = [$cc];
        }
        $this->cc = $cc;

        return $this;
    }

    public function getBcc()
    {
        $addresses = [];
        foreach ($this->bcc as $email => $name) {
            if (is_int($email)) { // no name
                $email = $name;
                $name = null;
            }
            $addresses[] = new Address($email, $name ?? '');
        }

        return new AddressCollection($addresses);
    }

    public function setBcc($bcc)
    {
        if (is_string($bcc) === true) {
            $bcc = [$bcc];
        }
        $this->bcc = $bcc;

        return $this;
    }

    public function getReturnPath()
    {
        return $this->returnPath;
    }

    public function setReturnPath($returnPath)
    {
        throw new Exception("Don't set returnPath. Will be handled automatically by SMTP2GO");
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string text body of the message
     */
    public function getTextBody()
    {
        return $this->textBody ?? '';
    }

    public function setTextBody($text)
    {
        $this->textBody = $text;

        return $this;
    }

    /**
     * @return string|null html body of the message
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    public function setHtmlBody($html)
    {
        $this->htmlBody = $html;

        return $this;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTrackOpens()
    {
        return $this->trackOpens;
    }

    public function setTrackOpens($trackOpens)
    {
        $this->trackOpens = $trackOpens;

        return $this;
    }

    public function getClickTracking()
    {
        return $this->clickTracking;
    }

    public function setClickTracking($clickTracking)
    {
        $this->clickTracking = $clickTracking;

        return $this;
    }

    public function getTransactional()
    {
        return $this->transactional;
    }

    public function setTransactional($transactional)
    {
        $this->transactional = $transactional;

        return $this;
    }

    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    public function getHeaders()
    {
        return empty($this->headers) ? null : $this->headers;
    }

    public function getAttachments()
    {
        if (empty($this->attachments)) {
            return null;
        }

        return new AttachmentCollection($this->attachments);
    }

    public function attach($fileName, array $options = [])
    {
        $this->attachments[] = new Attachment($fileName);

        return $this;
    }

    public function attachContent($content, array $options = [])
    {
        $filename = '';
        if (!empty($options['fileName'])) {
            $filename = $options['fileName'];
        } else {
            throw new Exception('Filename is missing');
        }
        $tempFilename = tempnam(sys_get_temp_dir(), 'smtpattach');
        file_put_contents($tempFilename, $content);

        $this->attachments[] = new Attachment($tempFilename, $filename);

        return $this;
    }

    public function embed($fileName, array $options = [])
    {
        $embed = [
            'Content' => base64_encode(file_get_contents($fileName)),
        ];
        if (!empty($options['fileName'])) {
            $embed['Name'] = $options['fileName'];
        } else {
            $embed['Name'] = pathinfo($fileName, PATHINFO_BASENAME);
        }
        if (!empty($options['contentType'])) {
            $embed['ContentType'] = $options['contentType'];
        } else {
            $embed['ContentType'] = 'application/octet-stream';
        }
        $embed['ContentID'] = 'cid:'.uniqid();
        $this->attachments[] = $embed;

        return $embed['ContentID'];
    }

    public function embedContent($content, array $options = [])
    {
        $embed = [
            'Content' => base64_encode($content),
        ];
        if (!empty($options['fileName'])) {
            $embed['Name'] = $options['fileName'];
        } else {
            throw new Exception('Filename is missing');
        }
        if (!empty($options['contentType'])) {
            $embed['ContentType'] = $options['contentType'];
        } else {
            $embed['ContentType'] = 'application/octet-stream';
        }
        $embed['ContentID'] = 'cid:'.uniqid();
        $this->attachments[] = $embed;

        return $embed['ContentID'];
    }

    public function toString()
    {
        return serialize($this);
    }
}
