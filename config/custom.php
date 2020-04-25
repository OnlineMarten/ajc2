

<?php

return [
    'minimal_tickets_required' => '1', //minimal number of tckets reuired per purchase if not set at event level
    'minutes_close_sale_before_start' => '90', //number of minutes before start time event to stop selling online
    'basket_lifetime' => '10', //number of MINUTES before basket will be deleted
    'psp_lifetime' => '30', //number of SECONDS before payment link will expire (within this time customer has to finish payment on psp)
];
