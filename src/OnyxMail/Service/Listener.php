<?php
namespace OnyxMail\Service;

use Zend\Mail;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class Listener implements ListenerAggregateInterface
{
    private $transportAdapter;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    protected $mailDefaults = array();


    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    public function __construct(\Zend\ServiceManager\ServiceManager $sm) {
        $this->serviceManager = $sm;
        $config = $sm->get('config');
        $mailConfig = $config['onyx_mail'];
        if(isset($mailConfig['transport_method'])){
            $method = $mailConfig['transport_method'];
        }else{
            $method = "sendmail";
        }

        if(isset($mailConfig['defaults'])){
            $this->mailDefaults = $mailConfig['defaults'];
        }

        switch($method){
            case "sendmail":
                $this->transportAdapter = new SendmailTransport();
                break;
            case "smtp":
                $this->transportAdapter = new SmtpTransport();
                $options   = new SmtpOptions($mailConfig['smtp']);
                $this->transportAdapter->setOptions($options);
                break;
            default:
                $this->transportAdapter = new SendmailTransport();
                break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('Onyx\Service\EventManger', 'sendMessage', array($this, 'sendMessage'), 100);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }


    public function sendMessage($e){
        $params = $e->getParams();

        if(isset($params['body'])){
            $body = $params['body'];
        }else{
            // "no body";
            return false;
        }

        if(isset($params['subject'])){
            $subject = $params['subject'];
        }else{
            // "no subject";
            return false;
        }

        if(isset($params['to'])){
            $to = $params['to'];
        }else{
            // "no to";
            return false;
        }

        if(isset($params['from'])){
            $from = $params['from'];
        }else{
            $from = $this->mailDefaults['from'];
        }

        if(isset($params['ical'])){
            $iCalText = $params['ical'];
        }else{
            $iCalText = null;
        }

        if(isset($params['header'])){
            $header = Mail\Headers::fromString($params['header']);
        }else{
            $header = null;
        }

        $textBody = strip_tags($body);

        $text = new MimePart($textBody);
        $text->type = \Zend\Mime\Mime::TYPE_TEXT;
        $text->encoding = \Zend\Mime\Mime::ENCODING_7BIT;
        $text->charset = 'UTF-8';

        $html = new MimePart($body);
        $html->type = \Zend\Mime\Mime::TYPE_HTML;
        $html->encoding = \Zend\Mime\Mime::ENCODING_7BIT;
        $html->charset = 'UTF-8';

        $mimeMessage = new MimeMessage();

        //$mimeMessage->setParts(array($html, $text));
        $mimeMessage->addPart($html);
        if ($iCalText) {
            $iCal = new MimePart($iCalText);
            //$iCal->type = 'message/rfc822';
            $iCal->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
            $iCal->encoding = \Zend\Mime\Mime::ENCODING_8BIT;
            $iCal->filename = 'calendar.ics';
            $iCal->id = 'calendar.ics';
            $mimeMessage->addPart($iCal);
        }

        $message = new Mail\Message();
        if ($header) {
            $message->setHeaders($header);
        }
        $message->setBody($mimeMessage)
            ->setFrom($from['address'], $from['name'])
            ->addTo($to[0], $to[1])
            ->setSubject($subject);

        //$message->getHeaders()->get('content-type')->setType('multipart/alternative'); //this sets the text version as an alt
        //$message->getHeaders()->setTransferEncoding('UTF-8');

        if(isset($params['cc'])){
            $message->setCc($params['cc']);
        }

        if(isset($params['bcc'])){
            $message->setBcc($params['bcc']);
        }

        if(isset($params['replyto'])){
            $message->addReplyTo($params['replyto'][0], $params['replyto'][1]);
        }

        if(isset($params['encoding'])){
            //$message->setEncoding($params['encoding']);
        }else{
            //$message->setEncoding($this->mailDefaults['encoding']);
        }

        try{
            $this->transportAdapter->send($message);
        }catch(Exception $e){
            //echo $e->getMessage();
            //exit();
        }

    }

}
