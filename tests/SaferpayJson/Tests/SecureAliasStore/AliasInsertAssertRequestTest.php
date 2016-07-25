<?php
namespace Ticketpark\SaferpayJson\Tests\SecureAliasStore;

use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\SerializerBuilder;
use Ticketpark\SaferpayJson\SecureAliasStore\AliasInsertAssertRequest;

/**
 * Created by PhpStorm.
 * User: thomasm
 * Date: 25.07.2016
 * Time: 11:13
 */
class AliasInsertAssertRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testErrorResponse()
    {
        $initializer = new AliasInsertAssertRequest();
        $initializer->setBrowser($this->getBrowserMock(false));
        $response = $initializer->execute();

        $this->assertInstanceOf('Ticketpark\SaferpayJson\Message\ErrorResponse', $response);
    }

    public function testSuccessfulResponse()
    {
        $initializer = new AliasInsertAssertRequest();
        $initializer->setBrowser($this->getBrowserMock(true));
        $response = $initializer->execute();

        $this->assertInstanceOf('Ticketpark\SaferpayJson\SecureAliasStore\AliasInsertAssertResponse', $response);
    }

    public function getBrowserMock($successful)
    {
        $browser = $this->getMockBuilder('Buzz\Browser')
            ->disableOriginalConstructor()
            ->setMethods(array('post'))
            ->getMock();

        $browser->expects($this->once())
            ->method('post')
            ->will($this->returnValue($this->getResponseMock($successful)));

        return $browser;
    }

    public function getResponseMock($successful)
    {
        $response = $this->getMockBuilder('Buzz\Message\Response')
            ->disableOriginalConstructor()
            ->setMethods(array('getStatusCode', 'isClientError', 'getContent'))
            ->getMock();

        $response->expects($this->any())
            ->method('isClientError')
            ->will($this->returnValue(!$successful));

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        if ($successful) {
            $content = $this->getFakedApiResponse('Ticketpark\SaferpayJson\SecureAliasStore\AliasInsertAssertResponse');
        } else {
            $content = $this->getFakedApiResponse('Ticketpark\SaferpayJson\Message\ErrorResponse');
        }

        $response->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($content));

        return $response;
    }

    public function getFakedApiResponse($class)
    {
        AnnotationRegistry::registerLoader('class_exists');
        $serializer = SerializerBuilder::create()->build();

        $response = new $class();

        return $serializer->serialize($response, 'json');
    }
}