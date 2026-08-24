<?php

return [
    /*
    | Firma Electrónica Avanzada (FEA): verificación del correo del firmante
    | mediante código OTP antes de aceptar la firma. Actívala en producción.
    */
    'otp_enabled' => env('SIGNING_OTP_ENABLED', true),
];
