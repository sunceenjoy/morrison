<?php

namespace Morrison\Web\Controller;

use Morrison\Core\Repository\Entity\EmailEntity;
use Morrison\Web\Helper\VoteChecker;

/**
 * Vote Controller
 *
 */
class VoteController extends BaseController
{
    /** @var VoteChecker $voteChecker; */
    protected $voteChecker;
    
    public function __construct(\Morrison\Core\Container $container)
    {
        parent::__construct($container);
        $this->voteChecker = $container['vote_checker'];
    }
    
    public function index()
    {
        return $this->render('web/vote/vote.html.twig');
    }

    public function post()
    {
        $emailAddress = $this->request->request->get('email_address');
        $sticker = $this->request->request->get('sticker');
        $ipAddress = $this->request->getClientIp();
        $stickerMaps = [1 => 'Love', 2 => 'DoGood', 3 => 'Unite', 4 => 'Rise'];
                
        if (!\Swift_Validate::email($emailAddress) || !isset($stickerMaps[$sticker])) {
            $this->addFlash('warning', 'Valid email and vote required.');
             return $this->redirect('/');
        }

        // Here we check the ip in redis(memory cache) to reduce potential impact to database
        if ($this->voteChecker->ipAllowed($ipAddress) === false) {
            $this->addFlash('warning', 'Request rate limit!');
             return $this->redirect('/');
        }
        
        // Here we check the email address in redis(memory cache) to reduce potential impact to database
        // Since we store all voted email addresses in redis, we don't need to check from the database.
        if ($this->voteChecker->checkEmailAddress($emailAddress)) {
             $this->addFlash('warning', 'A user with that email has already voted.');
             return $this->redirect('/');
        }
        
        $this->em->getRepository('Morrison:VoteEntity')->addNew($emailAddress, $sticker, $ipAddress);
        $this->addFlash('success', 'Vote counted. Thank you!');
        
        // Count voted email address
        $this->voteChecker->newEmailAddress($emailAddress);
        
        // Instead of sending it in the same request, we send the email asynchronously.
        // We add a new record into email send queue which is much faster.
        // Here I use database as email send queue, we may also use redis or RabbitMQ.
        $subject = 'Thank you';
        $content = sprintf('You have voted for %s. Thank you!', $stickerMaps[$sticker]);
        $type = EmailEntity::TYPE_HTML;
        $this->em->getRepository('Morrison:EmailEntity')->addNew($emailAddress, $subject, $content, $type);
        
        return $this->redirect('/');
    }
}
