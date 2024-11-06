<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class EmailController extends AbstractController
{
    #[Route('/api/send-email', name: 'send_email', methods: ['POST'])]
    public function sendEmail(MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('ADMIN_ACCESS');

        try {
            $email = (new Email())
                ->from('example@example.com')
                ->to('recipient@example.com')
                ->subject('Test Email using Mailpit and Symfony')
                ->text('This is a plain text email body')
                ->html('<p>This is an HTML email body</p>');

            $mailer->send($email);

            $logger->info('Email sent successfully to recipient@example.com');
            return new Response('Email sent successfully', Response::HTTP_OK);
        } catch (\Exception $e) {

            $logger->error('Failed to send email: ' . $e->getMessage());
            return new Response('Failed to send email: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
