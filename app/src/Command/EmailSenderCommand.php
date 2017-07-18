<?php

namespace Morrison\Command;

use Morrison\Core\Repository\Entity\EmailEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Email Sender Command
 */
class EmailSenderCommand extends BaseCommand
{
    /** @var \Swift_Mailer $mailer */
    private $mailer;
    
    /** @var string $from  sender's email address */
    private $from;
        
    /** @var Logger $logger */
    private $logger;
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->mailer = $this->container['mailer'];
        $this->from = $this->container['config']['admin_email'];
        $this->logger   = $this->container['log.send_email'];
    }

    protected function configure()
    {
        $this->setName('cron:send-email')
            ->setDescription('Send emails from queue')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Execute the command as a dry run.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->getOption('dry-run');
        $infoMsg = 'Begin running cron:send-email command';
        $infoMsg .= ($input->getOption('dry-run')) ? ' in dry-run' : '';
        $output->writeln($infoMsg);
        $this->logger->info($infoMsg, ['Command' => 'Ran send email command.']);
        
        do {
            $emails = $this->em->getRepository('Morrison:EmailEntity')->getEmails(10, !$dryRun);
            /** @var  EmailEntity $emailEntity */
            foreach ($emails as $emailEntity) {
                $message = \Swift_Message::newInstance();
                $message->setSubject($emailEntity->getSubject())
                    ->setFrom($this->from, 'Admin')
                    ->setTo($emailEntity->getEmailAddress())
                    ->setBody($emailEntity->getContent(), EmailEntity::getTypeText($emailEntity->getType()));
                
                if ($dryRun) {
                    echo $message->toString(),"\n";
                } else {
                    $this->mailer->send($message);
                    $this->logger->info('Email Sent', ['address' => $emailEntity->getEmailAddress()]);
                }
            }
            if (count($emails) == 0) {
                // If queue is empty, recheck it every 5 seconds
                sleep(5);
                $this->logger->info($infoMsg, ['Command is still running, queue is empty']);
            }
        } while (!$dryRun);
        
        $infoMsg = 'End running cron:send-email command';
        $output->writeln($infoMsg);
    }
}
