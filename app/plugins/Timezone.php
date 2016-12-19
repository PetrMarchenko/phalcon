<?php

namespace Shark\Plugin;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

class Timezone extends Plugin
{
    public $timezones = array(
        'Pacific/Midway'       => "Midway Island",
        'US/Samoa'             => "Samoa",
        'US/Hawaii'            => "Hawaii",
        'US/Alaska'            => "Alaska",
        'US/Pacific'           => "Pacific Time (US &amp; Canada)",
        'America/Tijuana'      => "Tijuana",
        'US/Arizona'           => "Arizona",
        'US/Mountain'          => "Mountain Time (US &amp; Canada)",
        'America/Chihuahua'    => "Chihuahua",
        'America/Mazatlan'     => "Mazatlan",
        'America/Mexico_City'  => "Mexico City",
        'America/Monterrey'    => "Monterrey",
        'Canada/Saskatchewan'  => "Saskatchewan",
        'US/Central'           => "Central Time (US &amp; Canada)",
        'US/Eastern'           => "Eastern Time (US &amp; Canada)",
        'US/East-Indiana'      => "Indiana (East)",
        'America/Bogota'       => "Bogota",
        'America/Lima'         => "Lima",
        'America/Caracas'      => "Caracas",
        'Canada/Atlantic'      => "Atlantic Time (Canada)",
        'America/La_Paz'       => "La Paz",
        'America/Santiago'     => "Santiago",
        'Canada/Newfoundland'  => "Newfoundland",
        'Atlantic/Stanley'     => "Stanley",
        'Atlantic/Azores'      => "Azores",
        'Atlantic/Cape_Verde'  => "Cape Verde Is.",
        'Africa/Casablanca'    => "Casablanca",
        'Europe/Dublin'        => "Dublin",
        'Europe/Lisbon'        => "Lisbon",
        'Europe/London'        => "London",
        'Africa/Monrovia'      => "Monrovia",
        'Europe/Amsterdam'     => "Amsterdam",
        'Europe/Berlin'        => "Berlin",
        'Europe/Brussels'      => "Brussels",
        'Europe/Paris'         => "Paris",
        'Europe/Rome'          => "Rome",
        'Europe/Stockholm'     => "Stockholm",
        'Europe/Vienna'        => "Vienna",
        'Europe/Warsaw'        => "Warsaw",
        'Europe/Athens'        => "Athens",
        'Europe/Bucharest'     => "Bucharest",
        'Europe/Kiev'          => "Kyiv",
        'Europe/Minsk'         => "Minsk",
        'Europe/Riga'          => "Riga",
        'Europe/Sofia'         => "Sofia",
        'Europe/Tallinn'       => "Tallinn",
        'Europe/Vilnius'       => "Vilnius",
        'Europe/Moscow'        => "Moscow",
        'Asia/Tehran'          => "Tehran",
        'Asia/Baku'            => "Baku",
        'Asia/Muscat'          => "Muscat",
        'Asia/Tbilisi'         => "Tbilisi",
        'Asia/Yerevan'         => "Yerevan",
        'Asia/Kabul'           => "Kabul",
        'Asia/Karachi'         => "Karachi",
        'Asia/Tashkent'        => "Tashkent",
        'Asia/Kolkata'         => "Kolkata",
        'Asia/Kathmandu'       => "Kathmandu",
        'Asia/Yekaterinburg'   => "Ekaterinburg",
        'Asia/Almaty'          => "Almaty",
        'Asia/Dhaka'           => "Dhaka",
        'Asia/Novosibirsk'     => "Novosibirsk",
        'Asia/Bangkok'         => "Bangkok",
        'Asia/Jakarta'         => "Jakarta",
        'Asia/Krasnoyarsk'     => "Krasnoyarsk",
        'Asia/Chongqing'       => "Chongqing",
        'Asia/Hong_Kong'       => "Hong Kong",
        'Asia/Kuala_Lumpur'    => "Kuala Lumpur",
        'Australia/Perth'      => "Perth",
        'Asia/Singapore'       => "Singapore",
        'Asia/Taipei'          => "Taipei",
        'Asia/Ulaanbaatar'     => "Ulaan Bataar",
        'Asia/Urumqi'          => "Urumqi",
        'Asia/Irkutsk'         => "Irkutsk",
        'Asia/Seoul'           => "Seoul",
        'Asia/Tokyo'           => "Tokyo",
        'Australia/Adelaide'   => "Adelaide",
        'Australia/Darwin'     => "Darwin",
        'Australia/Brisbane'   => "Brisbane",
        'Australia/Canberra'   => "Canberra",
        'Pacific/Guam'         => "Guam",
        'Australia/Hobart'     => "Hobart",
        'Australia/Melbourne'  => "Melbourne",
        'Australia/Sydney'     => "Sydney",
        'Pacific/Auckland'     => "Auckland",
        'Pacific/Fiji'         => "Fiji",
    );

    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $timezoneList = array();
        foreach ($this->timezones as $key => $value) {
            $dateTime = new \DateTime(null, new \DateTimeZone($key));
            $offset = $dateTime->getOffset() / 3600;
            if (array_key_exists("$offset", $timezoneList)) {
                $timezoneList["$offset"] .= ", " . $value;
            } else {
                $timezoneList["$offset"] =  "(GMT " . $this->formatOffset($dateTime->getOffset()) . ") " . $value;
            }

        }
        ksort($timezoneList);

        $currentTimezone = $this->session->get('timezone');
        $dispatcher->getDI()->getShared('view')->timezones = $timezoneList;
        if (isset($currentTimezone)) {
            $dispatcher->getDI()->getShared('view')->currentTimezone = $currentTimezone;
        } else {
            $dispatcher->getDI()->getShared('view')->currentTimezone = date('Z') / 3600;
            $this->session->set('timezone', date('Z') / 3600);
        }

    }

    protected function formatOffset($offset)
    {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);

        if ($hour == 0 && $minutes == 0) {
            $sign = ' ';
        }
        return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) .':'. str_pad($minutes, 2, '0');
    }
}
