<?php
namespace AppBundle\Event;
final class EventEnum
{
    const USER_SIGN_UP              = 'user.sign_up';
    const USER_CONFIRMATION         = 'user.confirmation';
    const USER_RESEND_CONFIRMATION  = 'user.resend_confirmation';
    const USER_DEACTIVATION         = 'user.deactivation';
    const USER_DELETION             = 'user.deletion';
    const USER_PASSWORD_REQUEST     = 'user.password_request';
    const USER_PASSWORD_UPDATE      = 'user.password_update';
    const USER_REACTIVATION_REQUEST = 'user.reactivation_request';
    const USER_INVITE_USER          = 'user.invite_user';
    const USER_CONFIRMATION_REMINDER= 'user.confirmation_reminder';
    const USER_CONFIRMATION_REMINDER_EXPIRED = 'user.confirmation_reminder_expired';
    const USER_SIGN_UP_FINISH       = 'user.sign_up_finish';
}
