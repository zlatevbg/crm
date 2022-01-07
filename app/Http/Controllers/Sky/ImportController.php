<?php

namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientStatus;
use App\Models\ClientProject;
use Carbon\Carbon;
use Storage;

class ImportController extends Controller {

    public function __construct()
    {
        ini_set('max_execution_time', 0);
    }

    public function clients()
    {
        $now = Carbon::now();
        $statuses = [];
        $projects = [];

        $rows = array_map(function($row) {
            return str_getcsv($row, "\t");
        }, file('C:\\Users\\Dimitar Zlatev\\Desktop\\data.txt'));

        foreach($rows as $row) {
            array_walk($row, function(&$value) {
                $value = trim(html_entity_decode($value), " \t\n\r\0\x0B\xC2\xA0"); // \xC2\xA0 = &nbsp;
            });

            $dataName = $row[0];
            $firstName = null;
            $lastName = null;
            if (mb_strpos($dataName, '&') !== false) {
                $firstName = $dataName;
            } elseif (($pos = mb_strpos($dataName, ',')) !== false) {
                $firstName = trim(mb_substr($dataName, 0, $pos));
                $lastName = trim(mb_substr($dataName, $pos + 1));
            } else {
                $pos = mb_strrpos($dataName, ' ');
                $firstName = trim(mb_substr($dataName, 0, $pos));
                $lastName = trim(mb_substr($dataName, $pos + 1));
            }


            $client = Client::create([
                'created_at' => $now,
                'updated_at' => $now,
                'agent_id' => 89,
                'source_id' => 11,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => 'not-known',
            ]);

            if (!Storage::disk('public')->exists('3' . DIRECTORY_SEPARATOR . $client->id)) {
                Storage::disk('public')->makeDirectory('3' . DIRECTORY_SEPARATOR . $client->id);
            }

            array_push($statuses, [
                'created_at' => $now,
                'updated_at' => $now,
                'client_id' => $client->id,
                'status_id' => 13,
                'user_id' => 1,
            ]);

            array_push($projects, [
                'created_at' => $now,
                'updated_at' => $now,
                'client_id' => $client->id,
                'project_id' => 2,
            ]);
        }

        ClientStatus::insert($statuses);
        ClientProject::insert($projects);

        return 'Done';
    }

}
