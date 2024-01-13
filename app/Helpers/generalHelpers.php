<?php

use Carbon\Carbon;

function parse_date($date, $format = null)
{
    if ( is_string($date) && in_array($date, ["now","now()"]))
        return Carbon::now();
    if ( is_string($date) && in_array($date, ["today","today()"]))
        return Carbon::today();

    if ( is_string($format) && !empty($format) )
    {
        $result = Carbon::createFromFormat($format, $date);
    }
    else
    {
        $date= str_replace('/','-',$date);
        $date= str_replace('.','-',$date);
        $date= str_replace('|','-',$date);
        $date= str_replace('*','-',$date);
        $result = Carbon::parse(str_replace('/','-',$date));
    }
    return $result;
}

function smsReport_validation($to_be_added = [])
{
    $result = [
        'start_date.date' => 'The start date must be a valid date.',
        'end_date.date' => 'The end date must be a valid date.',
        'end_date.after_or_equal' => 'The end date must be later than or the same as the start date.',
        'user_id.integer' => 'User ID must be an integer.',
        'status.string' => 'The status must be a text.',
        'status.in' => 'Invalid status value. Only pending, delivered, failed values are accepted.',
        'sort_by.string' => 'The sort field must be text.',
        'sort_by.in' => 'Invalid sort field value. Only user_id, phone, send_time values are accepted.',
        'sort_direction.string' => 'Sorting direction must be a text.',
        'sort_direction.in' => 'Invalid sort direction value. Only asc, desc values are accepted.',
        'page.integer' => 'The page number must be an integer.',
        'page.min' => 'The page number must be at least 1.',
        'per_page.integer' => 'The number of items per page must be an integer.',
        'per_page.min' => 'The number of items per page must be at least 1.',
        'per_page.max' => 'The maximum number of items per page should be 100.',
    ];
    $result = array_merge($result, $to_be_added);
    return $result;
}
