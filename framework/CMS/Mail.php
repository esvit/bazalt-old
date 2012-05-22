<?php

using('Framework.Vendors.PHPMailer');

class CMS_Mail implements CMS_Interface_MessageTransport
{
    const EMAIL_NAME_OPTION = 'CMS.EmailName';

    const EMAIL_OPTION = 'CMS.Email';

    const USE_SMTP_OPTION = 'CMS.UseSmtp';

    const SMTP_HOST_OPTION = 'CMS.SmtpHost';

    const SMTP_USER_OPTION = 'CMS.SmtpUser';

    const SMTP_PASSWORD_OPTION = 'CMS.SmtpPassword';

    const SMTP_PORT_OPTION = 'CMS.SmtpPort';

    const SMTP_SECURITY_OPTION = 'CMS.SmtpSecurity';

    public static function useSmtp()
    {
        return CMS_Option::get(self::USE_SMTP_OPTION, false);
    }

    public static function getSMTPSecure()
    {
        $secure = CMS_Option::get(self::SMTP_SECURITY_OPTION, 'none');
        if ($secure != 'ssl' && $secure != 'tls') {
            return '';
        }
        return $secure;
    }

    protected static function createMailer()
    {
        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'UTF-8';

        if (self::useSmtp()) {
            $mailer->IsSMTP();
            if (DEBUG) {
                $mailer->SMTPDebug  = 2;
            }
            $mailer->SMTPSecure = self::getSMTPSecure();
            if ($mailer->SMTPSecure != '') {
                $mailer->SMTPAuth   = true;
                $mailer->Host       = CMS_Option::get(self::SMTP_HOST_OPTION, '');
                $mailer->Port       = (int)CMS_Option::get(self::SMTP_PORT_OPTION, '');
                $mailer->Username   = CMS_Option::get(self::SMTP_USER_OPTION, '');
                $mailer->Password   = CMS_Option::get(self::SMTP_PASSWORD_OPTION, '', true);
            }
        }
        //$mailer->IsSendmail();

        $mailer->isHTML(true);
        $from = CMS_Option::get(self::EMAIL_OPTION, '');
        $name = CMS_Option::get(self::EMAIL_NAME_OPTION, '');
        if (empty($from)) {
            $from = 'www@unknown.com';
        }
        $mailer->setFrom($from, $name);
        return $mailer;
    }

    /*
     * CMS_Interface_MessageTransport
     */
    public static function send($address, $body, $subject = null)
    {
        self::sendMail($address, $body, $subject);
    }
    
    /*
     * CMS_Interface_MessageTransport
     */
    public static function getTitle()
    {
        return __('Email');
    }
    
    public static function init($params)
    {
    }
    
    public static function sendMail($address, $body, $subject = null)
    {
        if(defined('EMAIL_SAVE_LOCAL') && EMAIL_SAVE_LOCAL) {
            file_put_contents(
                TEMP_DIR.'/email_'.md5($address).'_'.date('Y.m.d.H.i.s').'txt', 
                sprintf("To: %s\nSubject: %s\nMessage: %s", $address, $subject, $body)
            );
            return;
        }
        $mail = self::createMail($address, $body, $subject);
        $mail->send();
    }

    public static function createMail($address, $body, $subject = null)
    {
        $mail = self::createMailer();
        $mail->AddAddress($address);
        $mail->Body = $body;
        $mail->Subject = $subject;

        return $mail;
    }
}