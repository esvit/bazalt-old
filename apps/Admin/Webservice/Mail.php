<?php

class Admin_Webservice_Mail extends CMS_Webservice_Application
{
    public function sendTestEmail($address)
    {
        CMS_Mail::sendMail($address, 'Test');
    }
}