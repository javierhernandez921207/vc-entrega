<?php


namespace App;

use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Transport;

class Telegram
{
    private $token = '1720564854:AAF8Pz_ZVLrpmqp-fQ0SK2yqjUzWVq2Z2i8';

    public function notifTelegramGrupo($usuarios, $mensaje)
    {
        foreach ($usuarios as $user) {
            if ($user->getIdTelegram()) {
                try {
                    $message = new ChatMessage($mensaje);
                    $telegram = Transport::fromDsn('telegram://' . $this->token . '@default?channel=' . $user->getIdTelegram());
                    $telegram->send($message);
                } catch (\Exception $exception) {
                    //$this->addFlash('error', "Telegram no enviado a usuario " . $user->getNombre());
                }
            }
        }
    }

    public function notifTelegramUsuario($usuario, $mensaje)
    {
        if ($usuario->getIdTelegram()) {
            try {
                $message = new ChatMessage($mensaje);
                $telegram = Transport::fromDsn('telegram://' . $this->token . '@default?channel=' . $usuario->getIdTelegram());
                $telegram->send($message);
            } catch (\Exception $exception) {
                //$this->addFlash('error', "Telegram no enviado a usuario " . $user->getNombre());
            }
        }
    }
}