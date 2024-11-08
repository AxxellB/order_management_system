<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class TwoFactorAuthenticatorService
{
    private CacheInterface $cache;
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(CacheInterface $cache, MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function generateAndSend2FACode(string $userId, string $userEmail): int
    {
        $code = random_int(100000, 999999);

        $cacheKey = '2fa_code_' . $userId;
        $this->cache->deleteItem($cacheKey);

        $this->cache->get($cacheKey, function (ItemInterface $item) use ($code) {
            $item->expiresAfter(300);
            $this->logger->info("Storing 2FA code for user in cache: {$code}");
            return $code;
        });

        try {
            $this->send2FACodeByEmail($userEmail, $code);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send 2FA code email: ' . $e->getMessage());
            throw new \RuntimeException('Failed to send 2FA code email: ' . $e->getMessage());
        }

        $this->logger->info("Generated 2FA code for user {$userId}: {$code}");
        return $code;
    }

    public function regenerateAndSend2FACode(string $userId, string $userEmail): int
    {
        $this->invalidate2FACode($userId);

        $newCode = random_int(100000, 999999);
        $cacheKey = '2fa_code_' . $userId;

        $this->cache->get($cacheKey, function (ItemInterface $item) use ($newCode) {
            $item->expiresAfter(300);
            $this->logger->info("Storing new 2FA code for user in cache: {$newCode}");
            return $newCode;
        });

        try {
            $this->send2FACodeByEmail($userEmail, $newCode);
        } catch (\Exception $e) {
            $this->logger->error('Failed to resend 2FA code email: ' . $e->getMessage());
            throw new \RuntimeException('Failed to resend 2FA code email: ' . $e->getMessage());
        }

        $this->logger->info("Resent 2FA code for user {$userId}: {$newCode}");
        return $newCode;
    }


    private function send2FACodeByEmail(string $email, int $code): void
    {
        $emailMessage = (new Email())
            ->from('no-reply@Ecommerce.com')
            ->to($email)
            ->subject('Your 2FA Code')
            ->text("Your two-factor authentication code is: $code");

        $this->mailer->send($emailMessage);
        $this->logger->info("Sent 2FA code to email: {$email}");
    }

    public function get2FACode(string $userId): ?int
    {
        $cacheKey = '2fa_code_' . $userId;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $this->logger->info("2FA code found in cache for user {$userId}");
            return $cacheItem->get();
        } else {
            $this->logger->warning("No 2FA code found in cache for user {$userId}");
            return null;
        }
    }

    public function invalidate2FACode(string $userId): void
    {
        $cacheKey = '2fa_code_' . $userId;
        $this->cache->deleteItem($cacheKey);
        $this->logger->info("2FA code invalidated for user {$userId}");
    }
}

