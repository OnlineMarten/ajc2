

<template>

  <FullCalendar  defaultView="dayGridMonth"
  :plugins="calendarPlugins"
  :events="events"
  @eventClick="handleEventClick"
  :showNonCurrentDates="false"

   />

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

export default {
  components: {
    FullCalendar // make the <FullCalendar> tag available
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
        if (arg.event.classNames=="open")
      alert(arg.event.id +' '+ arg.event.classNames);
      window.location.href = "booking/"+arg.event.id;
    },
  }
}

</script>
