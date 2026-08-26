<?php

return [
    /*
    | Firma Electrónica Simple / Avanzada: verificación del correo del firmante
    | mediante código OTP antes de aceptar la firma. Desactivado por defecto.
    */
    'otp_enabled' => filter_var(env('SIGNING_OTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
];
