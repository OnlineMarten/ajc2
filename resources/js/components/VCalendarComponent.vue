<template>
<div>
    <DatePicker
        v-model="date"
        mode="single"
        :value="null"
        :attributes='attrs'
        :available-dates='allevents'
        :select-attribute='selectAttribute'
        is-dark
        is-inline

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


<!--<p>{{events}}</p>-->
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
        }
    },//methods
}//export default
</script>
