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





Vue.component('booking-component', require('./components/BookingComponent.vue').default);
Vue.component('calendar-component', require('./components/CalendarComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});

