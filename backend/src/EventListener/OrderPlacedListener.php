<?php

namespace App\EventListener;

use App\Event\OrderPlacedEvent;
use Knp\Snappy\Pdf;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class OrderPlacedListener
{
    private MailerInterface $mailer;
    private Pdf $pdfGenerator;
    private UrlGeneratorInterface $urlGenerator;
    private KernelInterface $kernel;
    private Environment $twig;

    public function __construct(
        MailerInterface       $mailer,
        Pdf                   $pdfGenerator,
        UrlGeneratorInterface $urlGenerator,
        KernelInterface       $kernel,
        Environment           $twig
    )
    {
        $this->mailer = $mailer;
        $this->pdfGenerator = $pdfGenerator;
        $this->urlGenerator = $urlGenerator;
        $this->kernel = $kernel;
        $this->twig = $twig;
    }

    public function onOrderPlaced(OrderPlacedEvent $event)
    {
        $order = $event->getOrder();
        $user = $order->getUserId();
        $address = $order->getAddress();

        $invoiceHtml = $this->twig->render('invoice/invoice.html.twig', [
            'order' => $order,
        ]);
        $pdfContent = $this->pdfGenerator->getOutputFromHtml($invoiceHtml);

        $projectDir = $this->kernel->getProjectDir();
        $filePath = $projectDir . '/public/invoices/invoice_' . $order->getId() . '.pdf';
        file_put_contents($filePath, $pdfContent);

        $invoiceUrl = $this->urlGenerator->generate(
            'invoice_download',
            ['orderId' => $order->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $productListHtml = '';
        foreach ($order->getOrderProducts() as $orderProduct) {
            $product = $orderProduct->getProductEntity();
            $productListHtml .= "
            <li>
                <strong>{$product->getName()}</strong> - Quantity: {$orderProduct->getQuantity()}, 
                Price: {$orderProduct->getPricePerUnit()}, 
                Subtotal: {$orderProduct->getSubtotal()}
            </li>
        ";
        }

        $addressHtml = "
        <p><strong>Shipping Address:</strong></p>
        <p>{$address->getLine()} {$address->getLine2()}</p>
        <p>{$address->getCity()}, {$address->getPostcode()}</p>
        <p>{$address->getCountry()}</p>
    ";

        $email = (new Email())
            ->from('example@example.com')
            ->to($user->getEmail())
            ->subject('Your Order Confirmation')
            ->html("
            <p>Thank you for your order, {$user->getFirstName()}!</p>
            <p><strong>Order Reference Number:</strong> {$order->getId()}</p>
            <p><strong>Order Date:</strong> {$order->getOrderDate()->format('Y-m-d H:i:s')}</p>
            <p><strong>Total Amount:</strong> {$order->getTotalAmount()}</p>
            <p><strong>Payment Method:</strong> {$order->getPaymentMethod()}</p>
            <p><strong>Status:</strong> New</p>
            
            $addressHtml
            
            <p>You can download your invoice <a href=\"$invoiceUrl\">here</a>.</p>
            <p>Thank you for shopping with us!</p>
        ");

        $this->mailer->send($email);
    }
}