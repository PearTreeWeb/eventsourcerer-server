<?php

declare(strict_types=1);

namespace App\Domain\Tool\Handler;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Settings\Repository\SettingsRepository;
use App\Domain\Settings\Service\SshKeyConverter;
use App\Domain\Tool\Command\EncryptPersonalData;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EncryptPersonalDataHandler
{
    private const int BATCH_SIZE = 100;

    public function __construct(
        private StreamEventRepository $streamEventRepository,
        private EventRepository $eventRepository,
        private SettingsRepository $settingsRepository,
        private SshKeyConverter $sshKeyConverter,
        private ClockInterface $clock,
    ) {}

    public function __invoke(EncryptPersonalData $command): void
    {
        $settings = $this->settingsRepository->get();

        if (null === $settings || null === $settings->getPublicSshKey()) {
            throw new \RuntimeException('Public SSH key is not set in Settings.');
        }

        $publicKey = $this->sshKeyConverter->convertToPem($settings->getPublicSshKey()->toString());

        // Basic validation of the converted key
        if (!str_contains($publicKey, '-----BEGIN PUBLIC KEY-----')) {
            throw new \RuntimeException('Failed to convert SSH key to PEM format. Only ssh-rsa format is supported for personal data encryption. Please update your SSH key in Settings.');
        }

        $eventPropertiesWithPersonalData = $this->eventRepository->allPersonalDataPropertyIds();
        $eventsCount = $this->streamEventRepository->countEventsWithPersonalDataNotEncryptedBefore($command->beforeDate);
        $batchSize = self::BATCH_SIZE;

        for ($offset = 0; $offset < $eventsCount; $offset += $batchSize) {
            $events = $this->streamEventRepository->eventsWithPersonalDataNotEncryptedBefore($command->beforeDate, $batchSize);
            $count = 0;

            foreach ($events as $event) {
                $count++;
                $eventId = $event->getEventId()->toString();
                
                if (!isset($eventPropertiesWithPersonalData[$eventId])) {
                    $event->setPersonalDataHasBeenEncrypted(true);
                    $this->streamEventRepository->update($event);
                    continue;
                }

                $personalDataPropertyIds = $eventPropertiesWithPersonalData[$eventId];

                foreach ($event->getProperties() as $property) {
                    if (in_array($property->eventPropertyId()->toRfc4122(), $personalDataPropertyIds, true)) {
                        $plainText = $property->getSerializedValue();

                        // RSA has a limit on the size of the data it can encrypt.
                        // We use hybrid encryption: AES for the data, RSA for the AES key.
                        $aesKey = openssl_random_pseudo_bytes(32);
                        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

                        $encryptedData = openssl_encrypt($plainText, 'aes-256-cbc', $aesKey, OPENSSL_RAW_DATA, $iv);

                        if (false === $encryptedData) {
                            throw new \RuntimeException('Symmetric encryption failed: ' . openssl_error_string());
                        }

                        // Encrypt the key and IV together with RSA
                        $keyAndIv = $aesKey . $iv;
                        $success = openssl_public_encrypt($keyAndIv, $encryptedKeyAndIv, $publicKey);

                        if (!$success) {
                            throw new \RuntimeException('Asymmetric encryption failed: ' . openssl_error_string());
                        }

                        // Store as base64(encryptedKeyAndIv) . ':' . base64(encryptedData)
                        $storedValue = base64_encode($encryptedKeyAndIv) . ':' . base64_encode($encryptedData);

                        $property->setSerializedValue($storedValue);
                    }
                }

                $event->setPersonalDataHasBeenEncrypted(true);
                $this->streamEventRepository->update($event);
            }

            if ($count === 0) {
                break;
            }
        }

        $settings->setLastSshKeyUpdate($this->clock->now());
        $this->settingsRepository->update($settings);
    }
}
