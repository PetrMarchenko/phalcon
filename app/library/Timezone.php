<?php

namespace Shark\Library;

use Phalcon\DI;

/**
 * Class Timezone
 */
class Timezone
{
    /**
     * @var string    timezone offset
     */
    public $offset;

    const TIMEZONE_MIN = -12;
    const TIMEZONE_MAX = 12;
    const TIMEZONE_DEFAULT = 0;

    const OFFSET_MASK = '/^[-+]?\d{1,2}(\.\d{0,2})?$/';
    /**
     * @param   int                $tz              timezone offset
     */
    public function __construct($tz)
    {
        if (!$tz) {
            $tz = self::TIMEZONE_DEFAULT;
        }
        $this->checkTimeZone($tz);
        $this->offset = $tz;
    }

    /**
     * Modify date with timezone offset
     * @param   string $date date for modifying in format Y-m-d H:i:s
     * @param   bool $revert flag for revert date (use 'true' for search by date in special
     *                                                  tz, 'false' for show date in special tz)
     * @param string $format
     * @return  string                                  modified date in format Y-m-d H:i:s
     */
    public function modifyDate($date, $revert = false, $format = 'Y-m-d H:i:s')
    {
        if (!$date) {
            return $date;
        }
        $eventDate = new \DateTime($date . ' GMT');
        $timezone = $this->offset * 60;

        if ($this->offset) {
            if ($revert) {
                $timezone = '-' . $timezone;
            }
            // @TODO: find solution for using \DateTimeZone with offset
            // Can not use timezone_name_from_abbr and new \DateTimeZone() https://bugs.php.net/bug.php?id=44780&edit=1
            // $timezoneName = timezone_name_from_abbr(null, (int)$timezone * 3600, null);
            // return new \DateTimeZone($timezoneName);
            $eventDate->modify($timezone . ' minutes');
        }

        return $eventDate->format($format);
    }

    public function toTimezone($date, $format = 'Y-m-d H:i:s')
    {
        return $this->modifyDate($date, false, $format);
    }

    public function fromTimezone($date, $format = 'Y-m-d H:i:s')
    {
        return $this->modifyDate($date, true, $format);
    }



    public function checkTimeZone($tz)
    {
        if (!preg_match(self::OFFSET_MASK, $tz)) {
            throw new \Exception(__(
                'Timezone offset value should be two-digit integer or float with "-" prefix for negative values, got "%s"',
                $tz
            ));
        } elseif ($tz < self::TIMEZONE_MIN) {
            throw new \Exception(__(
                'Timezone offset value can\'t be less than "%s"',
                self::TIMEZONE_MIN
            ));
        } elseif ($tz > self::TIMEZONE_MAX) {
            throw new \Exception(__(
                'Timezone offset value can\'t be greater than "%s"',
                self::TIMEZONE_MAX
            ));
        }
    }
}