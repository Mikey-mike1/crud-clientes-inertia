<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $client;
    protected $from;
    protected $defaultTemplateSid;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        $this->from = env('TWILIO_FROM'); // 'whatsapp:+14155238886'
        $this->defaultTemplateSid = env('TWILIO_TEMPLATE_CONTENT_SID'); // Template por defecto
    }

    /**
     * Enviar un template de WhatsApp
     *
     * @param string $to Número de destino (solo dígitos)
     * @param array $variables Variables para la plantilla
     * @param string|null $templateSid SID del template (opcional)
     * @return object
     */
    public function enviarTemplate(string $to, array $variables, string $templateSid = null)
    {
        $to = $this->formatWhatsappNumber($to);

        try {
            return $this->client->messages->create(
                $to,
                [
                    "messagingServiceSid" => env('TWILIO_MESSAGING_SERVICE_SID'), // SID del Messaging Service
                    "contentSid" => $templateSid ?? $this->defaultTemplateSid,
                    "contentVariables" => json_encode($variables),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error enviando template WhatsApp a $to: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar un mensaje de texto simple por WhatsApp
     *
     * @param string $to Número de destino (solo dígitos)
     * @param string $mensaje Texto a enviar
     * @return object
     */
    public function enviarMensaje(string $to, string $mensaje)
    {
        $to = $this->formatWhatsappNumber($to);

        try {
            return $this->client->messages->create(
                $to,
                [
                    "from" => $this->from,
                    "body" => $mensaje,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error enviando mensaje WhatsApp a $to: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Normalizar número para WhatsApp
     *
     * @param string $number
     * @return string
     */
    private function formatWhatsappNumber(string $number): string
    {
        // Quitar todo lo que no sean dígitos
        $number = preg_replace('/[^0-9]/', '', $number);

        // Asegurarse de que tenga prefijo +504 para Honduras
        if (substr($number, 0, 3) !== '504') {
            $number = '504' . $number;
        }

        return "whatsapp:+$number";
    }
}
