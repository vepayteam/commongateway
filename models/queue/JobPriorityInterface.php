<?php


namespace app\models\queue;


interface JobPriorityInterface
{
    const REFRESH_STATUS_PAY_JOB_PRIORITY = 100;
    const CALLBACK_SEND_JOB_PRIORITY = 90;
    const REFUND_PAY_JOB_PRIORITY = 90;
    const RECEIVE_STATEMENTS_JOB_PRIORITY = 80;
    const RECURRENT_PAY_JOB_PRIORITY = 70;
    const SEND_MAIL_JOB_PRIORITY = 60;
    const DRAFT_PRINT_JOB_PRIORITY = 50;
}
