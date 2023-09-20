<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnergyReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file,$name,$multiple=false)
    {
        $this->file = $file;
        $this->file_name = $name;
        $this->multiple = $multiple;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       if($this->multiple){

       $message =  $this->markdown('Email.energyReport');
       foreach( $this->file as $key=>$value)

       $message->attachData($value,$this->file_name[$key],
            ['mime' => 'application/'.explode( '.',  $this->file_name[$key] )[1],
        ]);

       }
        else  {
            return $this->markdown('Email.energyReport')->attachData($this->file,$this->file_name,
                ['mime' => 'application/'.explode( '.', $this->file_name )[1],
            ]);

        }
    }
}
