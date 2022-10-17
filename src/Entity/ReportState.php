<?php

namespace App\Entity;

abstract class ReportState
{
    const NONE = 0;
    const REPORTED = 1;
    const IGNORED = 2;
}
