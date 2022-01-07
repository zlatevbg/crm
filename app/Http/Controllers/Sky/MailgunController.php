<?php

namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgentContact;
use App\Models\Client;
use App\Models\Investor;
use App\Models\Guest;
use App\Models\Subscriber;
use App\Models\Gvcontact;
use App\Models\RentalContact;
use Mailgun\Mailgun;

class MailgunController extends Controller
{
    public function __invoke(Request $request)
    {
        // \Log::debug($request->all());
        $mailgun = Mailgun::create(env('MAILGUN_SECRET'), 'https://api.eu.mailgun.net');

        $timestamp = $request->input('signature')['timestamp'];
        $token = $request->input('signature')['token'];
        $signature = $request->input('signature')['signature'];
        $event = $request->input('event-data')['event'];
        $tags = $request->input('event-data')['tags'];
        $recipient = $request->input('event-data')['recipient'];

        $valid = $mailgun->webhooks()->verifyWebhookSignature($timestamp, $token, $signature);
        abort_if(!$valid, 403);

        if ($event == 'unsubscribed') {
            if (in_array('newsletter-clients', $tags) || in_array('*', $tags)) {
                $client = Client::where('email', $recipient)->first();
                if ($client) {
                    $client->newsletters = 0;
                    $client->save();
                }
            }

            if (in_array('newsletter-agent-contacts', $tags) || in_array('*', $tags)) {
                $agent_contact = AgentContact::where('email', $recipient)->first();
                if ($agent_contact) {
                    $agent_contact->newsletters = 0;
                    $agent_contact->save();
                }
            }

            if (in_array('newsletter-investors', $tags) || in_array('*', $tags)) {
                $guest = Investor::where('email', $recipient)->first();
                if ($guest) {
                    $guest->newsletters = 0;
                    $guest->save();
                }
            }

            if (in_array('newsletter-guests', $tags) || in_array('*', $tags)) {
                $guest = Guest::where('email', $recipient)->first();
                if ($guest) {
                    $guest->newsletters = 0;
                    $guest->save();
                }
            }

            if (in_array('newsletter-mespil', $tags) || in_array('*', $tags)) {
                $email = Subscriber::where('website', 'mespil')->where('email', $recipient)->first();
                if ($email) {
                    $email->is_subscribed = 0;
                    $email->save();
                }
            }

            if (in_array('newsletter-ph', $tags) || in_array('*', $tags)) {
                $email = Subscriber::where('website', 'ph')->where('email', $recipient)->first();
                if ($email) {
                    $email->is_subscribed = 0;
                    $email->save();
                }
            }

            if (in_array('newsletter-pgv', $tags) || in_array('*', $tags)) {
                $email = Subscriber::where('website', 'pgv')->where('email', $recipient)->first();
                if ($email) {
                    $email->is_subscribed = 0;
                    $email->save();
                }
            }

            if (in_array('newsletter-gvcontacts', $tags) || in_array('*', $tags)) {
                $gvcontact = Gvcontact::where('email', $recipient)->first();
                if ($gvcontact) {
                    $gvcontact->is_subscribed = 0;
                    $gvcontact->save();
                }
            }

            if (in_array('newsletter-rental-contacts', $tags) || in_array('*', $tags)) {
                $rentalContact = RentalContact::where('email', $recipient)->first();
                if ($rentalContact) {
                    $rentalContact->is_subscribed = 0;
                    $rentalContact->save();
                }
            }
        }
    }
}
