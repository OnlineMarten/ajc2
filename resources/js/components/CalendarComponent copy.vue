

<template>
<div>
  <FullCalendar  defaultView="dayGridMonth"
  :plugins="calendarPlugins"
  :events="events"
  @eventClick="handleEventClick"
  :showNonCurrentDates="false"

   />


   <div class="modal fade" tabindex="-1" role="dialog" id="booking_component_modal">
       <BookingComponent></BookingComponent>
       <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
</div>
</template>

<style lang='scss'>

//class added to bookable events
.open{
    cursor: pointer;
}
//class added to non-bookable events
.closed{
    cursor: not-allowed;
}


@import '~@fullcalendar/core/main.css';
@import '~@fullcalendar/daygrid/main.css';

</style>

<script>

import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'

import BookingComponent from "./BookingComponent";

export default {
  components: {
    FullCalendar, // make the <FullCalendar> tag available
    BookingComponent
  },
  data() {
    return {
      calendarPlugins: [ dayGridPlugin ],
      events: [],
    }

  },
  mounted() {
    this.readEvents()

  },
  methods: {
  readEvents() {
      //this.events = [  { title: 'event 1', date: '2019-11-01' }, { title: 'event 2', date: '2019-11-02' }  ];
        axios.get("calendarevents").then(response => {
        this.events = response.data.events;
      });
    },
    handleEventClick(arg) {
        if (arg.event.classNames=="open"){
            this.event_id = arg.event.id;
            /*
            axios.get("/getevent/"+arg.event.id).then(response => {
            this.event = response.data.event;
            this.selection.categories = response.data.event.categories;
            this.selection.event = response.data.event.event;
            });

            */

            //window.location.href = "booking/"+arg.event.id;
            $("#booking_component_modal").modal("show");
        };
    },
}
}

</script>
