<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFunctionAdded extends Notification
{
    use Queueable;

    public $cityFunction;

    public function __construct($cityFunction)
    {
        $this->cityFunction = $cityFunction;
    }

    // We only want to store this in the database for the Inbox
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // Structure the data for the Inbox
    public function toArray(object $notifiable): array
    {
        return [
            'function_id' => $this->cityFunction->id,
            'title' => 'Nieuwe Functie: ' . $this->cityFunction->name,
            'message' => 'Er is een nieuwe functie toegevoegd. Controleer de effecten.',
            // Link to the simulation or library edit page
           'link' => route('simulation.dashboard', [], false) . '#function-' . $this->cityFunction->id,
        ];
    }
}