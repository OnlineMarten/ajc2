<template>
<div>
    <div v-if="event">

    <!--basket contents-->
    <div class="form-group" style="background:gray;color:white;min-height:150px;margin-bottom:10px">
        <div class="col-sm-8">
            <h2>Your selection</h2>
            <div  v-if="event">
            <p>   {{ new Date(event.event.event_date) | dateFormat('dd DD MMM YYYY', dateFormatConfig) }}</p>
            <p v-if="nrtickets>0">{{nrtickets}} x {{event.event.title}} <span v-if="ticket__array_index !== ''"> {{event.tickets[ticket__array_index].title}}</span></p>
            </div>
        </div>
    </div>
    <!--end basket contents-->

    <div v-if="step===1 || nosteps">

    <!--event description-->
    <div style="border:1px solid blue;margin-bottom:10px">
        <div class="col-sm-8" v-if="event.event.description">
            <h3>{{event.event.title}}</h3>{{event.event.description}}
        </div>
    </div>
    <!--end event description-->

    <!--ticketselection-->
    <div style="border:1px solid green;margin-bottom:10px">

        <div class="col-sm-8" v-if="event.ticketgroup.description">
            <h3>{{event.ticketgroup.title}}</h3>
        </div>
        <br>

        <!--# tickets-->
        <div class="form-group">
            <div class="col-sm-8">
            <select v-model="nrtickets">
                <option  value="0" >0</option>
                <option v-for="n in getNumbers(event.event.min_per_sale,event.event.max_per_sale)" :value="n" :key="n">{{n}}</option>
            </select> Tickets<br>
            </div>
        </div>

        <!--# ticket type-->
        <div class="form-group">
            <div class="col-sm-8">
                <label for="ticket_selection" class="col-sm-8 col-form-label">{{event.ticketgroup.description}}</label>
                <br>
                <span v-for="(ticket, index) in event.tickets" :key="ticket.id">
                    <input type="radio" :id="index" :value="index" v-model="ticket__array_index">
                    <!--let op: we gebruiken de array index en niet de ticket id!!!-->
                    <label :for="ticket.id" class="form-check-label">{{ ticket.title }}</label>
                        <div class="col-sm-8 ml-3" v-if="ticket.description">Info: {{ticket.description}}</div>
                    <br/>
                </span>
            </div>
        </div><!--form group row 1-->
    </div>
    <!--end ticketselection-->


</div><!--step1-->
<div v-if="step===2 || nosteps">

    <!--extras selection-->

        <span v-for="(category, index) in event.categories" :key="category.id">
            <div style="border:1px solid orange;margin-bottom:10px">
                <div class="col-sm-8">
                    <h2>{{category.title}}</h2>
                    {{category.description}}<br>

                    <span v-for="(extra, index) in category.extras" :key="extra.id">
                        <input type="radio" :id="index" :value="index" v-model="selected_extra">
                        <!--let op: we gebruiken de array index en niet de ticket id!!!-->
                        <label :for="extra.id" class="form-check-label">{{ extra.title }}</label>
                        <div class="col-sm-8 ml-3" v-if="extra.description">Info: {{extra.description}}</div>
                        <br/>
                    </span>
                </div><!--col-sm-8-->
            </div><!--border-->
        </span>
    </div>









    </div><!--step2-->

    <div v-if="step===3 || nosteps">


    <button  type="submit" @click.prevent="">Go to payment</button>

    </div><!--step3-->
    <div v-if="step=1 && !nosteps"><button type="submit" @click.prevent="cancel()">Cancel</button></div>
    <div v-if="step>1 && !nosteps"><button type="submit" @click.prevent="prev()">Previous</button></div>
    <div v-if="(step>=1 && step<3 && !nosteps )"><button  type="submit" @click.prevent="next()">Next</button></div>


    </div><!-- if-event-->
</div>

</template>



<script>


//import axioscalls from '@./resources/services/axioscalls'
export default {


  data(){
    return {
        nosteps:true,
        event_id:"",
        event:"",
        tickets:"",
        step:1,
        nrtickets:"0",
        ticket__array_index:"",



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


    mounted() {
        console.log('mounted');
        console.log(axios.defaults.baseURL);
        this.event_id = _.last( window.location.pathname.split( '/' ) );
        console.log(this.event_id);
        this.getEvent();

    },//mounted

    methods: {

        getEvent()
        {
            axios.get("/getevent/"+this.event_id).then(response => {
            this.event = response.data.event;

            });
        },
        getExtras() {
            axios.get("getextras/"+this.event_id).then(response => {
                this.extras = response.data.extras;
            });
        },
        getTickets() {
            axios.get("ticketgroupgettickets/"+this.event.ticket_group_id).then(response => {
                this.tickets = response.data.tickets;
            });
        },

        prev() {
            this.step--;
        },
        next() {
            this.step++;
            this.getTickets();
        },
        toCurrency (val) {

            return (val/100).toFixed(2);
        },
getNumbers:function(start,stop){

            stop++;//add one to the end to include the last iteration (2-6 tickets needs 5 iterations, not 4)
            return new Array(stop-start).fill(start).map((n,i)=>n+i);
        }



    },//methods
    computed:{

    }
}//export default
</script>
