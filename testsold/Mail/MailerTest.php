<?php

namespace LightWeight\Tests\Mail;

use LightWeight\Container\Container;
use LightWeight\Mail\Contracts\MailDriverContract;
use LightWeight\Mail\Contracts\MailerContract;
use LightWeight\Mail\Drivers\LogDriver;
use LightWeight\Mail\Mailer;
use PHPUnit\Framework\TestCase;

/**
 * Test del sistema de correo electrónico
 */
class MailerTest extends TestCase
{
    private Mailer $mailer;
    
    protected function setUp(): void
    {
        // Limpiar contenedor entre pruebas
        Container::deleteInstance();
        Container::getInstance();
        
        // Crear mailer con driver de log para las pruebas
        $this->mailer = new Mailer('log');
        Container::set(MailerContract::class, $this->mailer);
    }
    
    protected function tearDown(): void
    {
        Container::deleteInstance();
    }
    
    /**
     * Test de envío de correo básico
     */
    public function testSendBasicEmail(): void
    {
        $result = $this->mailer->send(
            'test@example.com',
            'Test Subject',
            '<p>This is a test email</p>',
            [
                'from' => 'sender@example.com',
                'from_name' => 'Test Sender'
            ]
        );
        
        $this->assertTrue($result);
        $this->assertInstanceOf(LogDriver::class, $this->mailer->getDriver());
    }
    
    /**
     * Test del cambio de driver
     */
    public function testSwitchDriver(): void
    {
        // Registrar un driver personalizado para la prueba
        $this->mailer->registerDriver('custom', LogDriver::class);
        
        // Cambiar al driver personalizado
        $this->mailer->setDriver('custom');
        
        // Verificar que el driver se cambió correctamente
        $this->assertInstanceOf(LogDriver::class, $this->mailer->getDriver());
    }
    
    /**
     * Test de envío con CC y BCC
     */
    public function testSendWithCcAndBcc(): void
    {
        $result = $this->mailer->send(
            'main@example.com',
            'Test With CC and BCC',
            '<p>This is a test email with CC and BCC</p>',
            [
                'cc' => ['cc1@example.com', 'cc2@example.com'],
                'bcc' => 'bcc@example.com'
            ]
        );
        
        $this->assertTrue($result);
    }
    
    /**
     * Test de la función helper mail_send
     */
    public function testMailSendHelper(): void
    {
        
        // Asegurarnos de que la función helper utiliza nuestro mailer mockup
        Container::set(MailerContract::class, $this->mailer);
        
        $result = mailSend(
            'helper@example.com',
            'Helper Test',
            '<p>Testing the mailSend helper</p>'
        );
        
        $this->assertTrue($result);
    }
    
    /**
     * Test de la función helper mail_driver
     */
    public function testMailDriverHelper(): void
    {
        
        // Asegurarnos de que la función helper utiliza nuestro mailer mockup
        Container::set(MailerContract::class, $this->mailer);
        
        // Obtener el driver actual
        $driver = mailDriver();
        $this->assertInstanceOf(MailDriverContract::class, $driver);
        
        // Cambiar el driver
        $result = mailDriver('log');
        $this->assertInstanceOf(MailerContract::class, $result);
    }
}
