<template>
<div>
    <div v-if="step===1">
    <h2>Choose date</h2>
    <DatePicker
        v-model="date"
        mode="single"
        :value="null"
        :attributes='attrs'
        :available-dates='allevents'
        :select-attribute='selectAttribute'
        is-inline
        @input="onChangeDate"
        :input-props='{
            placeholder: "Please choose a date",
            readonly: true,
        }'
    />




<!--
    *Unused DatePicket props*
    :available-dates='events'
    :min-page=

    *input-props*
    class: "w-full shadow appearance-none border rounded py-2 px-3 text-gray-700 hover:border-blue-5",
-->




</div><!--step1-->

<div v-if="step===2">
    <h2>Your selection</h2>
     {{ new Date(date) | dateFormat('dd DD MMM YYYY', dateFormatConfig) }}
    <h2>Choose tickets</h2>
    <p>ticket selection box</p>
    <h2>Choose Extra's</h2>
    <p>extra's selection box</p>

</div><!--step2-->

<div v-if="step===3">
    <h2>Your selection</h2>
     {{ new Date(date) | dateFormat('dd DD MMM YYYY', dateFormatConfig) }}
     <br/><p>ticketselection and extra 's here</p>
    <h2>Name country, phone here</h2>
    <button  type="submit" @click.prevent="next()">Go to payment</button>

</div><!--step3-->

<div v-if="step>1"><button type="submit" @click.prevent="prev()">Previous</button></div>
<div v-if="(step>=1 && step<3 && date!=='')"><button  type="submit" @click.prevent="next()">Next</button></div>



</div>

</template>



<script>
import DatePicker from 'v-calendar/lib/components/date-picker.umd';


export default {
  components: {

    DatePicker
  },

  data(){
    return {
            date:"",
            events:"",
            events2:"",
            events3:"",
            allevents:"",
            selectAttribute: {
                fillMode: 'solid',
                highlight:'gray'

            },
            step:1,

            //date config
            dateFormatConfig: {
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
            }

        }//return
    },//data

    computed:{
        attrs() {  return[
                {
                    highlight: {
                        color: 'green',
                        //fillMode: 'light',
                        class: 'my-dot-class',
                        },
                    dates: this.events,
                },
                {
                    highlight: {
                        color: 'green',
                       // fillMode: 'light',
                        class: 'my-dot-class',
                        },
                    dates: this.events2,
                },
                {
                    highlight: {
                            color: 'red',
                            borderColor: 'gray',
                          //  fillMode: 'light',
                            class: 'my-dot-class',
                        },
                        popover: {
                            label: 'sold out',
                            visibility: 'click'
                        },
                    dates: this.events3,
                },

            ] //attrib
        }

    },//computed

    mounted() {
        this.readEvents()
    },//mounted

    methods: {
        readEvents()
        {

            axios.get("admin/calendarevents").then(response => {
            //this.events = response.data.events;
            this.allevents=
                [
                    new Date(2019, 9, 1),
                    new Date(2019, 9, 10),
                    new Date(2019, 9, 22),
                    new Date(2019, 9, 12),
                    new Date(2019, 9, 11),
                    new Date(2019, 9, 15),
                ];
            this.events=
                [
                    new Date(2019, 9, 1),
                    new Date(2019, 9, 10),
                    new Date(2019, 9, 22),
                ];
            this.events2=
                [
                    new Date(2019, 9, 12),
                    new Date(2019, 9, 11),
                    new Date(2019, 9, 15),
                ];
                this.events3=
                [
                    new Date(2019, 9, 14),
                    new Date(2019, 9, 2),
                    new Date(2019, 9, 18),
                ];
            });
        },
        prev() {
            this.step--;
        },
        next() {
            this.step++;
        },
        onChangeDate(){
            console.log('in onchangedate, new date:'+this.date);
            //get tickets for this date
        }
    },//methods
}//export default
</script>
