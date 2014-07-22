<?php
namespace OnyxMail\Service;

use Zend\Mail;
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
        \Zend\Debug\Debug::dump($config);
        exit();
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
        $mail = new Mail\Message();
        $mail->setBody('This is the text of the mail.')
             ->setFrom('somebody@example.com', 'Some Sender')
             ->addTo('somebody_else@example.com', 'Some Recipient')
             ->setSubject('TestSubject');
        
    }
    
}