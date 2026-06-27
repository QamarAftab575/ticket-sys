<?php

namespace App\Services;

use Exception;
use Illuminate\Mail\Message;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;

class MailTestService
{
    /**
     * Test the SMTP connection using the provided configuration.
     *
     * @param array $config
     * @return array
     */
    public function testConnection(array $config): array
    {
        try {
            $encryption = isset($config['encryption']) && $config['encryption'] !== 'none' 
                ? $config['encryption'] 
                : null;
                
            $isTls = ($encryption === 'ssl') ? true : null;
                
            $transport = new EsmtpTransport(
                $config['host'] ?? '127.0.0.1',
                (int) ($config['port'] ?? 25),
                $isTls
            );

            if (!empty($config['username'])) {
                $transport->setUsername($config['username']);
            }
            
            if (!empty($config['password'])) {
                $transport->setPassword($config['password']);
            }

            // Start the transport to test connection and authentication
            $transport->start();
            
            // Cleanly stop after successful test
            $transport->stop();

            return [
                'success' => true,
                'message' => 'SMTP connection established successfully.',
            ];
        } catch (TransportExceptionInterface $e) {
            return [
                'success' => false,
                'message' => 'Unable to connect to the SMTP server. Please verify Host, Port, Username, Password, and Encryption.',
                'technical_details' => [
                    'exception' => get_class($e),
                    'error_message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An unexpected error occurred while testing the connection.',
                'technical_details' => [
                    'exception' => get_class($e),
                    'error_message' => $e->getMessage(),
                ]
            ];
        }
    }

    /**
     * Send a test email using the provided configuration.
     *
     * @param array $config
     * @param string $to
     * @param string|null $message
     * @return array
     */
    public function sendTestEmail(array $config, string $to, ?string $message = null): array
    {
        try {
            $encryption = isset($config['encryption']) && $config['encryption'] !== 'none' 
                ? $config['encryption'] 
                : null;
                
            $isTls = ($encryption === 'ssl') ? true : null;

            // Create transport
            $transport = new EsmtpTransport(
                $config['host'] ?? '127.0.0.1',
                (int) ($config['port'] ?? 25),
                $isTls
            );

            if (!empty($config['username'])) {
                $transport->setUsername($config['username']);
            }
            if (!empty($config['password'])) {
                $transport->setPassword($config['password']);
            }

            // Create Laravel mailer with symfony transport
            $app = app();
            $events = $app->make('events');
            $laravelMailer = new \Illuminate\Mail\Mailer('test', $app->make('view'), $transport, $events);
            
            $fromAddress = $config['from_address'] ?? 'test@example.com';
            $fromName = $config['from_name'] ?? 'SMTP Test';

            $laravelMailer->alwaysFrom($fromAddress, $fromName);

            $body = "This is a test email sent to verify your SMTP configuration.\n\n";
            if (!empty($message)) {
                $body .= "Message from sender:\n" . $message . "\n\n";
            }
            $body .= "If you received this email, your configuration is working correctly.";

            $laravelMailer->raw($body, function (Message $mail) use ($to) {
                $mail->to($to)->subject('SMTP Configuration Test');
            });

            return [
                'success' => true,
                'message' => 'A test email has been sent to: ' . $to,
            ];
        } catch (TransportExceptionInterface $e) {
            return [
                'success' => false,
                'message' => 'Unable to send the test email. Please check your configuration.',
                'technical_details' => [
                    'exception' => get_class($e),
                    'error_message' => $e->getMessage(),
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An unexpected error occurred while sending the email.',
                'technical_details' => [
                    'exception' => get_class($e),
                    'error_message' => $e->getMessage(),
                ]
            ];
        }
    }
}
