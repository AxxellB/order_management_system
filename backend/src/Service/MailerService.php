<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $mailer;
    private $adminEmail;

    private $logger;

    public function __construct(MailerInterface $mailer, string $adminEmail, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->logger = $logger;
    }

    public function sendLowStockAlert(array $lowStockProducts)
    {
        $subject = 'Low Stock Alert';
        $body = "The following products are low in stock:\n\n";

        foreach ($lowStockProducts as $product) {
            $body .= "Product: {$product['name']}, Remaining Quantity: {$product['remainingQuantity']}\n";
        }

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($this->adminEmail)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}