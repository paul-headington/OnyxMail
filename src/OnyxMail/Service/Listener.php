<?php
namespace OnyxMail\Service;

use Zend\Mail;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class Listener implements ListenerAggregateInterface
{
    private $transportAdapter;
    
    /**
     * @var \Zend\ServiceManager\ServiceManager 
     */
    protected $serviceManager;


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
        switch(sendmail){
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
    
    protected function sendMessage($e){
        $message = new Mail\Message();
        $message->setBody('This is the text of the mail.')
             ->setFrom('somebody@example.com', 'Some Sender')
             ->addTo('paul.headington@colensobbdo.co.nz', 'Paul')
             ->setSubject('TestSubject');
        
        $this->transportAdapter->send($message);
        
    }
    
}