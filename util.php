<?php
namespace bennetcc\dovecot_impersonate;

function __(string $val): string
{
    return "dovecot_impersonate" . "_" . $val;
}