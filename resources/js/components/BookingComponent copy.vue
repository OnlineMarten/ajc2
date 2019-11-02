<template>
<div>
    <h2>Booking Component!!</h2>
    <div v-if="event">

    <!--basket contents-->
    <div class="form-group col-sm-8" style="background:gray;color:white;min-height:200px;margin-bottom:10px">
            <h4>Your selection</h4>
            <div  v-if="event">
               <h4>Event: {{ event.event.title}}</h4>
            <p>   {{ new Date(event.event.event_date) | dateFormat('dd DD MMM YYYY') }} ,
                   {{ event.event.start_time }} -  {{ event.event.end_time }}</p>
            <p v-if="selection.nrtickets>0 && selection.ticket.length!==0">{{selection.nrtickets}} x {{event.event.title}}
                <span>
                    {{selection.ticket.title}}  {{selection.ticket.price/100  | toCurrency}} total: {{selection.nrtickets * (selection.ticket.price/100)  | toCurrency}}
                </span>
            </p>
        </div>

        <div v-if="selection.nrtickets>0 && selection.ticket.length!==0">
            <span v-for="category in selection.categories" :key="category.title">
                <div v-for="(extra) in category.extras" :key="extra.id">
                    <div v-if="extra.max === 'ticket' && extra.selected">
                        <span v-if="selection.nrtickets>0 ">
                            {{selection.nrtickets }} x {{ extra.title }}
                            {{extra.price/100 | toCurrency}}. Total: {{selection.nrtickets * (extra.price/100) | toCurrency}}
                        </span>
                    </div>
                    <div v-else>
                        <div v-if="extra.selected>0">
                            <span v-if="selection.nrtickets>0 ">
                                {{ extra.selected }} x {{ extra.title }}
                                {{extra.price/100  | toCurrency}}. Total: {{extra.selected * (extra.price/100) | toCurrency}}

                            </span>
                        </div>
                    </div>
                </div>
            </span>
            <hr>
            <p class="text-right">Total price: {{totalAmount | toCurrency}}</p>
        </div><!--col-sm-8-->


    </div>
    <!--end basket contents-->

    <div v-if="step===1 || nosteps">

    <!--event description-->
    <!--
    <div class="col-sm-8"  v-if="show_titles" style="border:1px solid blue;margin-bottom:10px">
        <div class="col-sm-8" v-if="event.event.description">
            <h4 v-if="show_titles">{{event.event.title}}</h4>
            <p v-if="show_descriptions">{{event.event.description}}</p>
        </div>
    </div>
    -->
    <!--end event description-->


    <!--ticketselection-->
    <div class="col-sm-8" style="border:1px solid green;margin-bottom:10px">

        <!--ticket group title and description-->
        <div class="form-group" v-if="event.ticketgroup.description && show_titles">
            <h4>{{event.ticketgroup.title}}</h4>
            <hr>
        </div>

        <br>



        <!-- # tickets -->
        <div class="form-group">
            <select v-model="selection.nrtickets">
                <option  :value="0" >0</option>
                <option v-for="counter in getNumbers(event.event.min_per_sale,event.event.max_per_sale)" :value="counter" :key="counter">{{counter}}</option>
            </select> Tickets
        </div>

        <!-- ticket type -->
        <div class="form-group">
                <label  v-if="show_descriptions" for="ticket_selection" class="col-sm-8 col-form-label">{{event.ticketgroup.description}}</label>
                <br>
                <span v-for="(ticket, index) in event.tickets" :key="ticket.id">
                    <input type="radio" :id="index" :value="ticket" v-model="selection.ticket">
                    <!--let op: we gebruiken de array index en niet de ticket id!!!-->
                    <label :for="ticket.id" class="form-check-label">{{ ticket.title }}</label>
                        <div class="col-sm-8 ml-3" v-if="ticket.description && show_descriptions">Info: {{ticket.description}}</div>
                    <br/>
                </span>
                <p class="text-right" v-if="selection.nrtickets>0 && selection.ticket.length!==0">Total: {{selection.nrtickets * (selection.ticket.price/100)  | toCurrency}}</p>
        </div><!--form group-->
    </div>
    <!--end ticketselection-->


</div><!--step1-->

<div v-if="step===2 || nosteps">

    <!--extras selection-->
    <div v-if="selection.nrtickets>0 && selection.ticket.length!==0">
        <span v-for="category in selection.categories" :key="category.id" >
            <div class="col-sm-8" style="border:1px solid orange;margin-bottom:10px">

            <h4 v-if="show_titles">{{category.title}}</h4>
            <p v-if="show_descriptions">{{category.description}}</p>

            <span v-for="(extra, index) in category.extras" :key="extra.id">
                <span v-if="extra.max === 'ticket'">
                    <input type="checkbox" :id="index" :value="extra.title" v-model="extra.selected">
                    <label :for="extra.id" class="form-check-label">{{ extra.title }} {{extra.price/100 | toCurrency}} per person</label>

                    <p class="text-right" v-if="extra.selected>0" >Total: {{selection.nrtickets*extra.price/100 | toCurrency}}</p>
                </span>
                <span v-else>

                    <select name="active" v-model="extra.selected">
                        <option selected value="0">0</option>
                        <option v-for="counter in parseInt(extra.max)" :key="counter" >{{counter}}</option>
                    </select>
                    <label :for="extra.id" class="form-check-label">{{ extra.title }} {{extra.price/100 | toCurrency}}</label>

                    <p class="text-right" v-if="extra.selected>0" >Total: {{extra.selected*extra.price/100 | toCurrency}}</p>
                    <hr>
                </span>


                <div class="col-sm-8 ml-3" v-if="extra.description && show_descriptions">Info: {{extra.description}}</div>
                <br/>
            </span>

            </div><!--border-->
        </span>
    </div><!--col-sm-8-->

</div><!--step2-->

<div v-if="step===3 || nosteps">

    <p>name, email, contact, paymentmethod</p>


</div><!--step3-->

<div class="col-sm-8" style="padding:0px">
        <button style="" v-if="step>1 && !nosteps" class="btn btn-outline-primary" type="submit" @click.prevent="prev()">Previous</button>
        <button style="" v-if="step===1 &&!nosteps" class="btn btn-outline-primary" type="submit" @click.prevent="cancel()">Cancel</button>

        <button style="float:right" v-if="step>=1 && step<3 && !nosteps && selection.nrtickets>0 && selection.ticket.length!==0" class="btn btn-primary" type="submit" @click.prevent="next()">Next</button>
        <button style="float:right" v-if="step===3 && selection.nrtickets>0 && selection.ticket.length!==0" class="btn btn-outline-primary" type="submit" @click.prevent="" >Go to payment</button>
</div>

    </div><!-- if-event-->
</div>

</template>



<script>


//import axioscalls from '@./resources/services/axioscalls'
export default {



  data(){
    return {
        show_titles:true,
        show_descriptions:false,
        nosteps:false,
        event_id:"",
        event:"",
        tickets:"",
        step:1,
        counter:"0",
        selection:{
            nrtickets:"0",
            ticket:[],
            categories:{
                extras:[],
            },
            event:[],
            total_amount:"0",
        },


    }//return
  },//data


    mounted() {
        console.log('mounted');
        console.log(axios.defaults.baseURL);
     //   this.event_id = _.last( window.location.pathname.split( '/' ) );
        this.getEvent();

    },//mounted

    methods: {

        getEvent()
        {
            console.log(this.event_id);
            /*
            axios.get("/getevent/"+this.event_id).then(response => {
            this.event = response.data.event;
            this.selection.categories = response.data.event.categories;
            this.selection.event = response.data.event.event;

            });
            */
        },

        prev() {
            this.step--;
        },
        next() {
            this.step++;
        },
        cancel(){
            window.history.back();
        },
        toCurrency (val) {

            return (val/100).toFixed(2);
        },
        getNumbers:function(start,stop){

            stop++;//add one to the end to include the last iteration (2-6 tickets needs 5 iterations, not 4)
            return new Array(stop-start).fill(start).map((n,i)=>n+i);
        },



    },//methods
    computed:{
        totalAmount: function () {
            this.selection.total_amount =  this.selection.nrtickets*(this.selection.ticket.price/100);

            for (var i = 0; i < this.selection.categories.length; i++  ) {
                console.log("in categories loop");
                for (var n = 0; n < this.selection.categories[i].extras.length; n++  ) {

                   if(this.selection.categories[i].extras[n].selected===true){
                        console.log(this.selection.categories[i].extras[n].title + ' : multiply by nrtickets');
                       // console.log(this.selection.categories[i].extras[n].price + this.selection.categories[i].extras[n].selected);
                        this.selection.total_amount += this.selection.categories[i].extras[n].price/100*this.selection.nrtickets;
                   }
                   else{
                       if (this.selection.categories[i].extras[n].selected){//check if selected exists, it does not exist automatically
                            console.log(this.selection.categories[i].extras[n].title +  ' : multiply by selected amount');
                           // console.log(this.selection.categories[i].extras[n].price + this.selection.categories[i].extras[n].selected);
                            this.selection.total_amount += this.selection.categories[i].extras[n].price/100*this.selection.categories[i].extras[n].selected;
                       }
                   }

                }//extras

            }//for catagories

            return this.selection.total_amount;
        }

    }
}//export default
</script>
