/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

//import multipleDatepicker from 'vue-multiple-datepicker';
//import VueMiniCalendar from 'vue-mini-calendar'
//Vue.component('VueMiniCalendar', require('vue-mini-calendar').default);

import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'

// Install BootstrapVue
Vue.use(BootstrapVue)
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)

Vue.filter('toCurrency', function (value) {

    let symbol;
    if (value<0){
        value= -value;
        symbol = '- € ';
    }
    else{
        symbol = '€ ';
    }
    value = symbol + parseFloat(value/100).toFixed(2);
    return value.replace(".00", "");//replace in case format is en
});


//toasted
import Toasted from 'vue-toasted';
Vue.use(Toasted);


// register a toast with a custom message
Vue.toasted.register('ajc_error',
    (payload) => {

        // if there is no message passed show default message
        if(! payload.message) {
    	    return "Oops.. Something Went Wrong.."
        }

        // if there is a message show it with the message
        return payload.message;
    },
    // options to the toast
    {
        theme: "bubble",
        type : 'error',
        //icon : 'error_outline',
        duration: '5000',
        action : [
            {
                text : 'X',
                onClick : (e, toastObject) => {
                    toastObject.goAway(0);
                }
            },
        ]
    }
)
// register a toast with a custom message
Vue.toasted.register('ajc_success',
    (payload) => {

        // if there is no message passed show default message
        if(! payload.message) {
    	    return "Success!"
        }

        // if there is a message show it with the message
        return payload.message;
    },
    // options to the toast
    {
        theme: "bubble",
        type : 'success',
       // icon : 'plus-square',
        duration: '5000',

    }
)
// register a toast with a custom message
Vue.toasted.register('ajc_info',
    (payload) => {

        // if there is no message passed show default message
        if(! payload.message) {
    	    return "Done!"
        }

        // if there is a message show it with the message
        return payload.message;
    },
    // options to the toast
    {
        theme: "bubble",
        type : 'info',
       // icon : 'plus-square',
        duration: '5000',

    }
)

//const VueFilterDateFormat = require('vue-filter-date-format');
import VueFilterDateFormat from 'vue-filter-date-format';

Vue.use(VueFilterDateFormat, {

        dayOfWeekNames: [
            'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday',
            'Friday', 'Saturday'
        ],
        dayOfWeekNamesShort: [
            'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'
        ],
        monthNames: [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ],
        monthNamesShort: [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ]
});



Vue.component('multipleDatepicker', require('vue-multiple-datepicker').default);


//import DatePicker from 'v-calendar/lib/components/date-picker.umd'
//Vue.component('date-picker', DatePicker);
Vue.component('date-picker', require('./components/VCalendarComponent.vue').default);

//Vue.component('booking-selection', require('./components/BookingSelection.vue').default);

//Vue.component('VueFilterDateFormat', require('vue-filter-date-format').default);
Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('category-component', require('./components/CategoryComponent.vue').default);
Vue.component('ticketgroup-component', require('./components/TicketGroupComponent.vue').default);
Vue.component('ticket-component', require('./components/TicketComponent.vue').default);
Vue.component('sale-component', require('./components/SaleComponent.vue').default);
Vue.component('basket-component', require('./components/BasketComponent.vue').default);
Vue.component('event-component', require('./components/EventComponent.vue').default);
Vue.component('extra-component', require('./components/ExtraComponent.vue').default);
Vue.component('promocode-component', require('./components/PromoCodeComponent.vue').default);

//Vue.component('crud-sale-component', require('./components/CrudSaleComponent.vue').default);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});

