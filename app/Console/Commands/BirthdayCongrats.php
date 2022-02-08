<?php

namespace App\Console\Commands;

use App\Client;
use App\ClientInformationOwner;
use App\Mail\PriceListSubscription as PriceListSubscriptionMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class BirthdayCongrats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bd_congrats:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Happy Birthday';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $clientBirthday = ClientInformationOwner::getTodayBirthdays();
        dd($clientBirthday);
        Mail::to(/** email именинника */)
            ->send(/** Поздравление с ДР */);
    }
}
