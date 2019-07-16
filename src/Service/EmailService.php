<?php


namespace App\Service;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send($email, $filename)
    {
        $email = (new Email())
            ->from('info@risman.xyz')
            ->to($email)
            ->subject('Exported File')
            ->text('This is your order summary, please check the attachment!')
            ->attachFromPath($filename);

        $this->mailer->send($email);
    }

    public function validate($email)
    {
        $validator = new EmailValidator();
        return $validator->isValid($email, new RFCValidation());
    }
}