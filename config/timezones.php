<?php

/**
 * Central timezone configuration.
 *
 * Each entry contains:
 *   - timezone:       IANA timezone identifier
 *   - label:          Human-readable label shown in the UI dropdown
 *   - offset_minutes: UTC offset in minutes (positive = ahead of UTC, negative = behind)
 *
 * Note: offsets here represent the standard (non-DST) offset.
 * Actual offsets may vary during daylight saving time.
 */
return [
    // UTC
    ['timezone' => 'UTC',                    'label' => '(UTC+00:00) UTC',                          'offset_minutes' => 0],

    // Africa
    ['timezone' => 'Africa/Abidjan',         'label' => '(UTC+00:00) Abidjan',                      'offset_minutes' => 0],
    ['timezone' => 'Africa/Accra',           'label' => '(UTC+00:00) Accra',                        'offset_minutes' => 0],
    ['timezone' => 'Africa/Cairo',           'label' => '(UTC+02:00) Cairo',                        'offset_minutes' => 120],
    ['timezone' => 'Africa/Casablanca',      'label' => '(UTC+01:00) Casablanca',                   'offset_minutes' => 60],
    ['timezone' => 'Africa/Johannesburg',    'label' => '(UTC+02:00) Johannesburg',                 'offset_minutes' => 120],
    ['timezone' => 'Africa/Lagos',           'label' => '(UTC+01:00) Lagos',                        'offset_minutes' => 60],
    ['timezone' => 'Africa/Nairobi',         'label' => '(UTC+03:00) Nairobi',                      'offset_minutes' => 180],
    ['timezone' => 'Africa/Tripoli',         'label' => '(UTC+02:00) Tripoli',                      'offset_minutes' => 120],
    ['timezone' => 'Africa/Tunis',           'label' => '(UTC+01:00) Tunis',                        'offset_minutes' => 60],

    // America
    ['timezone' => 'America/Anchorage',      'label' => '(UTC-09:00) Anchorage',                    'offset_minutes' => -540],
    ['timezone' => 'America/Bogota',         'label' => '(UTC-05:00) Bogota',                       'offset_minutes' => -300],
    ['timezone' => 'America/Buenos_Aires',   'label' => '(UTC-03:00) Buenos Aires',                 'offset_minutes' => -180],
    ['timezone' => 'America/Caracas',        'label' => '(UTC-04:00) Caracas',                      'offset_minutes' => -240],
    ['timezone' => 'America/Chicago',        'label' => '(UTC-06:00) Chicago (Central Time)',        'offset_minutes' => -360],
    ['timezone' => 'America/Denver',         'label' => '(UTC-07:00) Denver (Mountain Time)',        'offset_minutes' => -420],
    ['timezone' => 'America/Halifax',        'label' => '(UTC-04:00) Halifax (Atlantic Time)',       'offset_minutes' => -240],
    ['timezone' => 'America/Lima',           'label' => '(UTC-05:00) Lima',                         'offset_minutes' => -300],
    ['timezone' => 'America/Los_Angeles',    'label' => '(UTC-08:00) Los Angeles (Pacific Time)',    'offset_minutes' => -480],
    ['timezone' => 'America/Mexico_City',    'label' => '(UTC-06:00) Mexico City',                  'offset_minutes' => -360],
    ['timezone' => 'America/New_York',       'label' => '(UTC-05:00) New York (Eastern Time)',       'offset_minutes' => -300],
    ['timezone' => 'America/Phoenix',        'label' => '(UTC-07:00) Phoenix',                      'offset_minutes' => -420],
    ['timezone' => 'America/Santiago',       'label' => '(UTC-04:00) Santiago',                     'offset_minutes' => -240],
    ['timezone' => 'America/Sao_Paulo',      'label' => '(UTC-03:00) São Paulo',                    'offset_minutes' => -180],
    ['timezone' => 'America/St_Johns',       'label' => '(UTC-03:30) St. John\'s',                  'offset_minutes' => -210],
    ['timezone' => 'America/Toronto',        'label' => '(UTC-05:00) Toronto',                      'offset_minutes' => -300],
    ['timezone' => 'America/Vancouver',      'label' => '(UTC-08:00) Vancouver',                    'offset_minutes' => -480],

    // Asia
    ['timezone' => 'Asia/Almaty',            'label' => '(UTC+06:00) Almaty',                       'offset_minutes' => 360],
    ['timezone' => 'Asia/Baghdad',           'label' => '(UTC+03:00) Baghdad',                      'offset_minutes' => 180],
    ['timezone' => 'Asia/Baku',              'label' => '(UTC+04:00) Baku',                         'offset_minutes' => 240],
    ['timezone' => 'Asia/Bangkok',           'label' => '(UTC+07:00) Bangkok',                      'offset_minutes' => 420],
    ['timezone' => 'Asia/Colombo',           'label' => '(UTC+05:30) Colombo',                      'offset_minutes' => 330],
    ['timezone' => 'Asia/Dhaka',             'label' => '(UTC+06:00) Dhaka',                        'offset_minutes' => 360],
    ['timezone' => 'Asia/Dubai',             'label' => '(UTC+04:00) Dubai',                        'offset_minutes' => 240],
    ['timezone' => 'Asia/Hong_Kong',         'label' => '(UTC+08:00) Hong Kong',                    'offset_minutes' => 480],
    ['timezone' => 'Asia/Jakarta',           'label' => '(UTC+07:00) Jakarta',                      'offset_minutes' => 420],
    ['timezone' => 'Asia/Jerusalem',         'label' => '(UTC+02:00) Jerusalem',                    'offset_minutes' => 120],
    ['timezone' => 'Asia/Kabul',             'label' => '(UTC+04:30) Kabul',                        'offset_minutes' => 270],
    ['timezone' => 'Asia/Karachi',           'label' => '(UTC+05:00) Karachi',                      'offset_minutes' => 300],
    ['timezone' => 'Asia/Kathmandu',         'label' => '(UTC+05:45) Kathmandu',                    'offset_minutes' => 345],
    ['timezone' => 'Asia/Kolkata',           'label' => '(UTC+05:30) Kolkata (India)',               'offset_minutes' => 330],
    ['timezone' => 'Asia/Kuala_Lumpur',      'label' => '(UTC+08:00) Kuala Lumpur',                 'offset_minutes' => 480],
    ['timezone' => 'Asia/Kuwait',            'label' => '(UTC+03:00) Kuwait',                       'offset_minutes' => 180],
    ['timezone' => 'Asia/Manila',            'label' => '(UTC+08:00) Manila',                       'offset_minutes' => 480],
    ['timezone' => 'Asia/Muscat',            'label' => '(UTC+04:00) Muscat',                       'offset_minutes' => 240],
    ['timezone' => 'Asia/Riyadh',            'label' => '(UTC+03:00) Riyadh',                       'offset_minutes' => 180],
    ['timezone' => 'Asia/Seoul',             'label' => '(UTC+09:00) Seoul',                        'offset_minutes' => 540],
    ['timezone' => 'Asia/Shanghai',          'label' => '(UTC+08:00) Shanghai',                     'offset_minutes' => 480],
    ['timezone' => 'Asia/Singapore',         'label' => '(UTC+08:00) Singapore',                    'offset_minutes' => 480],
    ['timezone' => 'Asia/Taipei',            'label' => '(UTC+08:00) Taipei',                       'offset_minutes' => 480],
    ['timezone' => 'Asia/Tashkent',          'label' => '(UTC+05:00) Tashkent',                     'offset_minutes' => 300],
    ['timezone' => 'Asia/Tehran',            'label' => '(UTC+03:30) Tehran',                       'offset_minutes' => 210],
    ['timezone' => 'Asia/Tokyo',             'label' => '(UTC+09:00) Tokyo',                        'offset_minutes' => 540],
    ['timezone' => 'Asia/Yangon',            'label' => '(UTC+06:30) Yangon',                       'offset_minutes' => 390],

    // Atlantic
    ['timezone' => 'Atlantic/Azores',        'label' => '(UTC-01:00) Azores',                       'offset_minutes' => -60],
    ['timezone' => 'Atlantic/Cape_Verde',    'label' => '(UTC-01:00) Cape Verde',                   'offset_minutes' => -60],

    // Australia
    ['timezone' => 'Australia/Adelaide',     'label' => '(UTC+09:30) Adelaide',                     'offset_minutes' => 570],
    ['timezone' => 'Australia/Brisbane',     'label' => '(UTC+10:00) Brisbane',                     'offset_minutes' => 600],
    ['timezone' => 'Australia/Darwin',       'label' => '(UTC+09:30) Darwin',                       'offset_minutes' => 570],
    ['timezone' => 'Australia/Hobart',       'label' => '(UTC+10:00) Hobart',                       'offset_minutes' => 600],
    ['timezone' => 'Australia/Melbourne',    'label' => '(UTC+10:00) Melbourne',                    'offset_minutes' => 600],
    ['timezone' => 'Australia/Perth',        'label' => '(UTC+08:00) Perth',                        'offset_minutes' => 480],
    ['timezone' => 'Australia/Sydney',       'label' => '(UTC+10:00) Sydney',                       'offset_minutes' => 600],

    // Europe
    ['timezone' => 'Europe/Amsterdam',       'label' => '(UTC+01:00) Amsterdam',                    'offset_minutes' => 60],
    ['timezone' => 'Europe/Athens',          'label' => '(UTC+02:00) Athens',                       'offset_minutes' => 120],
    ['timezone' => 'Europe/Belgrade',        'label' => '(UTC+01:00) Belgrade',                     'offset_minutes' => 60],
    ['timezone' => 'Europe/Berlin',          'label' => '(UTC+01:00) Berlin',                       'offset_minutes' => 60],
    ['timezone' => 'Europe/Brussels',        'label' => '(UTC+01:00) Brussels',                     'offset_minutes' => 60],
    ['timezone' => 'Europe/Bucharest',       'label' => '(UTC+02:00) Bucharest',                    'offset_minutes' => 120],
    ['timezone' => 'Europe/Budapest',        'label' => '(UTC+01:00) Budapest',                     'offset_minutes' => 60],
    ['timezone' => 'Europe/Copenhagen',      'label' => '(UTC+01:00) Copenhagen',                   'offset_minutes' => 60],
    ['timezone' => 'Europe/Dublin',          'label' => '(UTC+00:00) Dublin',                       'offset_minutes' => 0],
    ['timezone' => 'Europe/Helsinki',        'label' => '(UTC+02:00) Helsinki',                     'offset_minutes' => 120],
    ['timezone' => 'Europe/Istanbul',        'label' => '(UTC+03:00) Istanbul',                     'offset_minutes' => 180],
    ['timezone' => 'Europe/Kiev',            'label' => '(UTC+02:00) Kyiv',                         'offset_minutes' => 120],
    ['timezone' => 'Europe/Lisbon',          'label' => '(UTC+00:00) Lisbon',                       'offset_minutes' => 0],
    ['timezone' => 'Europe/London',          'label' => '(UTC+00:00) London',                       'offset_minutes' => 0],
    ['timezone' => 'Europe/Madrid',          'label' => '(UTC+01:00) Madrid',                       'offset_minutes' => 60],
    ['timezone' => 'Europe/Minsk',           'label' => '(UTC+03:00) Minsk',                        'offset_minutes' => 180],
    ['timezone' => 'Europe/Moscow',          'label' => '(UTC+03:00) Moscow',                       'offset_minutes' => 180],
    ['timezone' => 'Europe/Oslo',            'label' => '(UTC+01:00) Oslo',                         'offset_minutes' => 60],
    ['timezone' => 'Europe/Paris',           'label' => '(UTC+01:00) Paris',                        'offset_minutes' => 60],
    ['timezone' => 'Europe/Prague',          'label' => '(UTC+01:00) Prague',                       'offset_minutes' => 60],
    ['timezone' => 'Europe/Rome',            'label' => '(UTC+01:00) Rome',                         'offset_minutes' => 60],
    ['timezone' => 'Europe/Sofia',           'label' => '(UTC+02:00) Sofia',                        'offset_minutes' => 120],
    ['timezone' => 'Europe/Stockholm',       'label' => '(UTC+01:00) Stockholm',                    'offset_minutes' => 60],
    ['timezone' => 'Europe/Vienna',          'label' => '(UTC+01:00) Vienna',                       'offset_minutes' => 60],
    ['timezone' => 'Europe/Warsaw',          'label' => '(UTC+01:00) Warsaw',                       'offset_minutes' => 60],
    ['timezone' => 'Europe/Zurich',          'label' => '(UTC+01:00) Zurich',                       'offset_minutes' => 60],

    // Indian Ocean
    ['timezone' => 'Indian/Maldives',        'label' => '(UTC+05:00) Maldives',                     'offset_minutes' => 300],
    ['timezone' => 'Indian/Mauritius',       'label' => '(UTC+04:00) Mauritius',                    'offset_minutes' => 240],

    // Pacific
    ['timezone' => 'Pacific/Auckland',       'label' => '(UTC+12:00) Auckland',                     'offset_minutes' => 720],
    ['timezone' => 'Pacific/Fiji',           'label' => '(UTC+12:00) Fiji',                         'offset_minutes' => 720],
    ['timezone' => 'Pacific/Guam',           'label' => '(UTC+10:00) Guam',                         'offset_minutes' => 600],
    ['timezone' => 'Pacific/Honolulu',       'label' => '(UTC-10:00) Honolulu',                     'offset_minutes' => -600],
    ['timezone' => 'Pacific/Midway',         'label' => '(UTC-11:00) Midway Island',                'offset_minutes' => -660],
    ['timezone' => 'Pacific/Port_Moresby',   'label' => '(UTC+10:00) Port Moresby',                 'offset_minutes' => 600],
    ['timezone' => 'Pacific/Tongatapu',      'label' => '(UTC+13:00) Nuku\'alofa',                  'offset_minutes' => 780],
];
