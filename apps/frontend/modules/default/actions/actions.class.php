<?php

class defaultActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
  }

  public function executeBlank(sfWebRequest $request)
  {
  }
  
  public function executeError404(sfWebRequest $request)
  {
  }

  public function executeTestMail(sfWebRequest $request)
  {
      frontendConfiguration::registerZend();
      $mail = new Zend_Mail();
      $mail->setFrom('core-noreply@kaeberleinlab.org', 'Core Resources');
      $mail->addTo('bradyo@uw.edu');
      $mail->setSubject('email sent');
      $body = 'hello world';
      $mail->setBodyText(strip_tags($body));
      $mail->setBodyHtml($body);
      $mail->send();
  }
}
