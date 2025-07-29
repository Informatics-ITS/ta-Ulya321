<?php

namespace App\Services;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

class WhatsAppService
{
    protected $driver;

    public function __construct()
    {
        $options = new ChromeOptions();
        $options->addArguments([
            '--headless', // Mode tanpa tampilan
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920x1080'
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        $this->driver = ChromeDriver::start($capabilities);
    }

    public function sendMessage($phone, $message)
    {
        $url = "https://web.whatsapp.com/send?phone=$phone&text=" . urlencode($message);
        $this->driver->get($url);

        sleep(10); // Tunggu hingga WhatsApp memuat

        try {
            $sendButton = $this->driver->findElement(WebDriverBy::xpath('//span[@data-icon="send"]'));
            $sendButton->click();
            sleep(2);
            $this->driver->quit();
            return "Pesan berhasil dikirim!";
        } catch (\Exception $e) {
            $this->driver->quit();
            return "Gagal mengirim pesan: " . $e->getMessage();
        }
    }
}
