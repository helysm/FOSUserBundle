<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Tests\Mailer;

use FOS\UserBundle\Command\ActivateUserCommand;
use FOS\UserBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Model\UserInterface;
use Swift_Events_EventDispatcher;
use Swift_Mailer;
use Swift_Transport_NullTransport;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;
use Twig_Template;

class TwigSwiftMailerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider goodEmailProvider
     */
    public function testSendConfirmationEmailMessageWithGoodEmails($emailAddress)
    {
        $mailer = $this->getTwigSwiftMailer();
        $mailer->sendConfirmationEmailMessage($this->getUser($emailAddress));

        $this->assertTrue(true);
    }

    /**
     * @dataProvider badEmailProvider
     * @expectedException Swift_RfcComplianceException
     */
    public function testSendConfirmationEmailMessageWithBadEmails($emailAddress)
    {
        $mailer = $this->getTwigSwiftMailer();
        $mailer->sendConfirmationEmailMessage($this->getUser($emailAddress));
    }

    /**
     * @dataProvider goodEmailProvider
     */
    public function testSendResettingEmailMessageWithGoodEmails($emailAddress)
    {
        $mailer = $this->getTwigSwiftMailer();
        $mailer->sendResettingEmailMessage($this->getUser($emailAddress));

        $this->assertTrue(true);
    }

    /**
     * @dataProvider badEmailProvider
     * @expectedException Swift_RfcComplianceException
     */
    public function testSendResettingEmailMessageWithBadEmails($emailAddress)
    {
        $mailer = $this->getTwigSwiftMailer();
        $mailer->sendResettingEmailMessage($this->getUser($emailAddress));
    }

    private function getTwigSwiftMailer()
    {
        return new TwigSwiftMailer(
            new Swift_Mailer(
                new Swift_Transport_NullTransport(
                    $this->getMock('Swift_Events_EventDispatcher')
                )
            ),
            $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface'),
            $this->getTwigEnvironment(),
            array(
                'template' => array(
                    'confirmation' => 'foo',
                    'resetting' => 'foo',
                ),
                'from_email' => array(
                    'confirmation' => 'foo@example.com',
                    'resetting' => 'foo@example.com',
                ),
            )
        );
    }

    private function getTwigEnvironment()
    {
        $twigEnvironment = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $twigEnvironment->method('mergeGlobals')
            ->willReturn(array())
        ;

        $twigEnvironment->method('loadTemplate')
            ->willReturn($this->getTwigTemplate())
        ;

        return $twigEnvironment;
    }

    private function getTwigTemplate()
    {
        // Using this method of building a mock due to a possible bug in phpunit
        // see http://tinyurl.com/gtybc3b
        $methods = get_class_methods('Twig_Template');
        $twigTemplate = $this->getMockBuilder('Twig_Template')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMockForAbstractClass()
        ;

        return $twigTemplate;
    }

    private function getUser($emailAddress)
    {
        $user = $this->getMock('FOS\UserBundle\Model\UserInterface');
        $user->method('getEmail')
            ->willReturn($emailAddress)
        ;

        return $user;
    }

    private function getEmailAddressValueObject($emailAddressAsString)
    {
        $emailAddress = $this->getMock('EmailAddress', array(
            '__toString',
        ));

        $emailAddress->method('__toString')
            ->willReturn($emailAddressAsString)
        ;

        return $emailAddress;
    }

    public function goodEmailProvider()
    {
        return array(
            array('foo@example.com'),
            array('foo@example.co.uk'),
            array($this->getEmailAddressValueObject('foo@example.com')),
            array($this->getEmailAddressValueObject('foo@example.co.uk')),
        );
    }

    public function badEmailProvider()
    {
        return array(
            array('foo'),
            array($this->getEmailAddressValueObject('foo')),
        );
    }
}