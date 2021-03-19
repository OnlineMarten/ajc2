<!--
EXPLANATION concerning availability checks
when a new sale is made the availability will automatically be checked when a basket is made or updated, through the session
which stores the basket id.
When a current sale is updated (including soft deleted sales) the availability will be checked from this component
with the checkavailability call
-->

<template>
<div>



        <!--MODAL ADD UPDATE-->


                    <h4 class="modal-title">
                        <span v-if="add_update=='add'">Add New Reservation</span><span v-else>Edit Reservation</span>
                    </h4>





                     <div class="alert alert-danger" v-if="errors.length > 0">

                        <ul>
                            <span v-html="errors">{{ errors }}</span>
                        </ul>
                    </div>
                    <div class="alert alert-danger" v-if="show_warning">
                        Warning messages:
                        <ul>
                            {{ warning_messages }}
                        </ul>
                    </div>



                <div class="row" v-if="add_update=='update'"><!--editing sale, show current date-->
                    <div class="col-sm-3">
                        Current date:
                    </div>
                    <div class="col-sm-8">
                             {{new Date(selected_event_date) | dateFormat('dd DD MMM YYYY')}}
                    </div>
                </div>

                <div class="row" v-if="add_update=='add' && event_id"><!--if event_id is given, adding sale for fixed date-->
                    <div class="col-sm-3">
                        Event date:
                    </div>
                    <div class="col-sm-8">
                             {{new Date(event.event_date) | dateFormat('dd DD MMM YYYY')}}
                    </div>
                </div>

                <div class="row" v-if="!event_id || add_update=='update'"><!-- if no event_id is given, or if it is an update of an existing sale the date is flexible, so show dates-->
                    <div class="col-sm-3">
                        Select date:
                    </div>
                    <div class="col-sm-8">

                        <!--show select box for choosing event date-->
                        <select v-model="selection.event_id" v-on:change="onEventChange()">
                            <option v-if="add_update=='add'" value="" selected disabled>Choose date</option>
                            <option v-if="add_update=='update' && sale_event_active== false" selected :value="event.id" :key="event.id">{{new Date(event.event_date) | dateFormat('dd DD MMM YYYY')}}</option>
                            <option v-for="event in events" :selected="event.id == selection.event_id"  :value="event.id" :key="event.id">{{new Date(event.date) | dateFormat('dd DD MMM YYYY')}}</option>
                            <!--<template v-if="selection.event_id == event.id">selected</template>-->
                        </select>
                    </div>
                </div>

                <span v-if="selection.event_id">

                <div class="row mt-2">
                    <div class="col-sm-3">
                        Tickets:
                    </div>
                    <div class="col-sm-8">
                        <select v-model="selection.nr_tickets" v-on:change="onNrTicketsChange()">
                            <option  :value="0" >0</option>
                            <option v-for="counter in event.capacity" :value="counter" :key="counter">{{counter}}</option>
                        </select>
                        <select v-model="selection.ticket_id" v-on:change="onTicketChange()">
                            <option value="" selected disabled>Select ticket type</option>
                            <option v-for="(ticket) in tickets" :value="ticket.id" :key="ticket.id">{{ticket.title}} {{ticket.price  | toCurrency}}</option>
                        </select>
                        <small v-if="selection.nr_tickets>0 && selection.ticket_id && ticket" class="text-right primary">{{selection.nr_tickets * ticket.price  | toCurrency}}</small>
                    </div>
                </div>


                    <hr>
                    <span v-for="(extra, index) in extras" :key="extra.id">

                            <div class="row mb-1">

                                <div class="col-sm-3">
                                    <span v-if="index===0">
                                    <!-- {{category.title}}:-->
                                    Extras
                                     </span>
                                </div>

                                <div class="col-sm-8">
                                    <span v-if="extra.max === 'ticket'">

                                            <input type="checkbox" :id="index" :value="extra.title" v-model="extra.nr">
                                            <label :for="extra.id" class="form-check-label">{{ extra.title }} {{extra.price | toCurrency}} per person</label>

                                            <small class="text-right" v-if="extra.nr>0" >Total: {{selection.nr_tickets*extra.price | toCurrency}}</small>
                                    </span>
                                    <span v-else>

                                        <select name="active" v-model="extra.nr">
                                            <option selected value=0>0</option>
                                            <option v-for="counter in parseInt(extra.max)" :key="counter" :value=counter >{{counter}}</option>
                                        </select>
                                        <label :for="extra.id" class="form-check-label">{{ extra.title }} {{extra.price | toCurrency}}</label>

                                        <small class="text-right" v-if="extra.nr>0" >Total: {{extra.nr*extra.price | toCurrency}}</small>
                                    </span>
                                </div><!--col 8-->
                            </div><!--row-->
                    </span><!--extras-->

                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        Promocode:
                    </div>
                    <div class="col-sm-8">
                        <select v-model="selection.promocode_id" v-on:change="onChangePromoCode()">
                            <option value="0">Select promocode</option>
                            <option v-for="(promocode) in promocodes" :value="promocode.id" :key="promocode.id">{{promocode.code}}</option>
                        </select>
                        <small class="text-danger" v-if="promocode_error_message"> * Invalid code</small>
                    </div>
                </div>



<hr>

<!--contact details-->
<div class="row mt-2">
        <div v-show="selection.nr_tickets>0 && selection.ticket_id" class="col-sm-12">

        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">Name:</label>
            <div class="col-sm-8">
            <input required type="text" name="name" id="name" class="form-control"
                v-model="selection.name">
            </div>
        </div>

        <div class="form-group row">
                <label for="name" class="col-sm-3 col-form-label">Phone:</label>
            <div class="col-sm-8">
                <vue-tel-input
                    v-model="selection.phone"
                    @country-changed="onCountryChange"
                    :inputOptions="{
                        showDialCode: false,
                        tabindex: 0
                    }"
                    :preferredCountries="[
                    'NL',
                    'US',
                    'GB',
                    ]"
                    mode="international"
                    placeholder=""
                    v-bind:defaultCountry=selection.country_code

                    ></vue-tel-input>
                    <!-- {{phone_data}}-->
            </div>
        </div>

         <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">Email:</label>
            <div class="col-sm-8">
            <input required type="email" name="email" id="email" class="form-control"
                v-model="selection.email">
            </div>
        </div>

        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">language (emails):</label>
            <div class="col-sm-8">
                <select v-model="selection.lang">
                    <option value='en'>English</option>
                    <option value='nl'>Nederlands</option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">guestlist comments:</label>
            <div class="col-sm-8">
                <textarea name="guestlist_comments" id="guestlist_comments" cols="30" rows="2" class="form-control"
                                v-model="selection.guestlist_comments"></textarea>
            </div>
        </div>

         <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">admin comments:</label>
            <div class="col-sm-8">
                 <textarea name="admin_comments" id="admin_comments" cols="30" rows="2" class="form-control"
                                v-model="selection.admin_comments"></textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">New payment:</label>
            <div class="col-sm-8">
                <input required type="number" step="1" name="paying_now" id="paying_now" class="form-control" v-model="paying_now_div_100">
            </div>
        </div>

    </div><!--col 12-->
</div><!--row-->
<!--end contact details-->

<!--amount and payment details-->

                <div class="row mt-2">
                    <div class="col-sm-11" v-if="selection.nr_tickets>0 && selection.ticket_id">

                        <ul>

                            <li v-if="valid_promocode" class="list-group-item d-flex justify-content-between bg-light">
                                    <span>Total without discount</span>
                                <strong>{{(selection.total_amount+selection.total_discount) | toCurrency}}</strong>

                            </li>

                            <li v-if="valid_promocode" class="list-group-item d-flex justify-content-between bg-light">
                                <div class="text-success">
                                <h6 class="my-0">Promo code Discount
                                <small>
                                    <span v-if="promocode.discount_amount">- {{promocode.discount_amount | toCurrency}}</span>
                                    <span v-if="promocode.discount_perc">{{promocode.discount_perc}} %.<br>Applicable on:</span>
                                    <span v-if="promocode.discount_perc && promocode.apply_to_tickets"> tickets</span>
                                    <span v-if="promocode.discount_perc && promocode.apply_to_extras"> extras</span>

                                    </small></h6>
                                </div>
                                <span class="text-success">- {{selection.total_discount | toCurrency}}</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total</span>
                                <strong>{{totalAmount | toCurrency}}</strong>
                            </li>
                            <li v-if="selection.amount_paid>0" class="list-group-item d-flex justify-content-between">
                                <span>Already paid</span>
                                <strong>{{selection.amount_paid | toCurrency}}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Paying now</span>
                                <strong>{{selection.paying_now | toCurrency}}</strong>
                            </li>
                             <li class="list-group-item d-flex justify-content-between">
                                <span>Remaining after payment</span>
                                <strong>{{remaining_after_paying_now | toCurrency}}</strong>
                            </li>

                        </ul>
                    </div><!--col-->
                </div><!--row-->
            </span><!--show event detals-->


<!--amount and payment details-->



<hr>

                            <!-- in case of add-->
                            <span v-if="add_update=='add'">
                        <button type="button" @click="cancelAddSale" class="btn btn-primary btn-outline" data-dismiss="modal">Cancel</button>
                        <button  v-if="selection.nr_tickets>0 && selection.ticket_id"  type="button" @click="createSale" class="btn btn-primary">Make reservation</button>
                        </span>
                            <!-- in case of update-->
                            <span v-if="add_update=='update'">
                        <button type="button" @click="cancelUpdateSale" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" @click="updateSale" class="btn btn-primary">Update</button>
                        </span>
                    </div>


</div>

</template>



<script>
import { VueTelInput } from 'vue-tel-input'

//import axioscalls from '@./resources/services/axioscalls'
export default {
    props:['sale_id','event_id'],


    components: {
        VueTelInput,
    },

  data(){
    return {

        sale:[],
        temp:[],
        sale_event_active:"",
        first_update:true,

        //oud
        counter:"0",
        show: false,
        show_warning:false,
        error:"",
        errors: "",
        show_past_sales:false,
        warning_messages:"",
        add_update: "",

      //  selection:[],

        selection:{
            name:"",
            email:"",
            phone:"",
            country_code:"",
            dial_code:"",
            nr_tickets:"0",
            ticket_nr:"",
            guestlist_comments:"",
            admin_comments:"",
            lang:"en",
            event_id:"",
            ticket_id:"",
            promocode_id:"0",
            extras:[],
            total_amount:"0",
            total_discount:"0",
            amount_paid:"0",
            paying_now:"0",
        },
        //end selection

        ticket:{
            price:"0",
        },
        promocode:{
            code:"",
            id:"0",
            discount_amount:"0",
            discount_perc:"0",
            apply_to_extras:"0",
            apply_to_tickets:"0",
        },
        valid_promocode:false,
        promocode_error_message:false,

        phone:"",
        phone_data:[],

        show_event_details:false,
        remaining_after_paying_now:"0",
        paying_now_div_100:"0",
        event:[],
        events: [],
        categories:[],
        tickets:[],
        sales:[],
        promocodes:[],//get and store all promocodes
        selected_sale:[],
        selected_extras:[],
        extras:[],
        show_deleted_sales:false,
        not_enough_tickets:false,
        selected_event_date:"",
    }//return
  },//data


    mounted() {
        console.log('mounted');
        console.log("sale_id="+this.sale_id);
        //load promocodes and available events so it is ready in case we want to add or update a sale.
        this.readPromoCodes();
        this.readAllEvents();

        if (this.sale_id) this.initUpdateSale();
        else this.initAddSale();

    },//mounted

    methods: {
        initAddSale(){
            if (this.event_id){

                this.selection.event_id =this.event_id;
                console.log('we have an event id so lets get the event. ID='+this.selection.event_id);
            }
            this.readEvent();
            this.add_update = "add";

        },
        createSale(){

            // create a ticket number
            let random_nr = Math.floor(Math.random() * (99999 - 10000) ) + 10000;
            this.selection.ticket_nr = "AJC-"+this.formatDate(this.event.event_date)+'-'+this.formatDate(new Date(),true)+'-'+random_nr;

            //update basket so we are sure we ave a valid basket
           axios
                .post("/basket", this.selection )
                .then(response => {

                    //basket succesfuly updated, now add sale
                    axios
                    .post("/admin/sale", this.selection )
                    .then(response => {
                        this.$emit('returnValue', response.data.message);

                    })
                    .catch(error => {
                        //coud not add sale, show error
                        console.log("error admin/sale");
                        this.showErrors(error);
                    });

                })
                .catch(error => {
                    //could not update basket, show error
                    console.log("error /basket");
                     this.showErrors(error);
            });


        },

        cancelAddSale() {
            console.log('cancel add');
            this.deleteBasket();
            this.$emit('returnValue', 'canceled');

        },

         initUpdateSale() {
            this.add_update = "update";
          //  this.first_update=true;
            this.paying_now_div_100="0";
            //get selected extras sale
                this.temp=[];
                this.selected_extras=[];
            //get sale and extras
            axios.get("admin/getsale/"+ this.sale_id)
            .then(response => {
                this.sale = response.data.sale;
                this.temp = response.data.extras;

                for (var i = 0; i < this.temp.length; i++  ) {
                    var newItem = {
                    title: this.temp[i].title,
                    max: this.temp[i].max,
                    price: this.temp[i].price,
                    id: this.temp[i].id,
                    nr: this.temp[i].pivot.nr
                    };
                    this.selected_extras.push(newItem);

                };//for extras

            //load event data into selecion and make a copy in case we want to change the date.
            this.selected_sale = this.sale;
            this.selection = this.sale;
            this.selection.event_id=this.selected_sale.event_id;//set event id into selection because we know that it exists
            this.readEvent();
            });


        },
        updateSale(){
            console.log('update sale');
            this.checkAvailability();//we need to recheck at the moment we are actually updating as in the meantime the status could have changed
            if (this.not_enough_tickets){
                console.log('not enough tickets');//TODO message to user
            }
            else{
                if (this.selected_event_date !== this.event.event_date){
                    console.log('date changed');
                    this.selection.admin_comments+="* date changed, original date:"+this.selected_event_date;
                    // date changed, so create a new ticket number
                    let random_nr = Math.floor(Math.random() * (99999 - 10000) ) + 10000;
                    this.selection.ticket_nr="AJC-"+this.formatDate(this.event.event_date)+'-'+this.formatDate(new Date(),true)+'-'+random_nr;
                }
                axios
                    .put("/admin/sale/" + this.selection.id, this.selection)

                    .then(response => {
                    console.log(response.data.message);
                    this.$emit('returnValue', response.data.message);
                    })

                .catch(error => {
                    console.log('error admin/sale');
                    this.showErrors(error);
                });
            }
        },
        cancelUpdateSale(){
             console.log('cancel update');
             this.deleteBasket();
             this.$emit('returnValue', 'canceled');

        },
        readAllEvents(){
            axios.get("openevents").then(response => {
                //  axios.get("allevents").then(response => {
                this.events = response.data.events;
            });
        },
        readEvent(){
            console.log('readevent id='+this.selection.event_id);
            axios.get("/getevent/"+this.selection.event_id).then(response => {
                this.event = response.data.event.event;
                //do we need to check if still available?->no, will be chekced with updatebasket

                //load event details
                this.categories = response.data.event.categories;
                this.tickets = response.data.event.tickets;
                this.show_event_details=true;
                this.extras=[];
                for (var i = 0; i < this.categories.length; i++  ) {
                    for (var n = 0; n < this.categories[i].extras.length; n++  ) {
                        //rebuild extras selection array with extra id and amount(nr)
                        console.log('pushing: '+ this.categories[i].extras[n].title);
                        this.extras.push({
                        title: this.categories[i].extras[n].title,
                        max: this.categories[i].extras[n].max,
                        price: this.categories[i].extras[n].price,
                        id: this.categories[i].extras[n].id,
                        nr: 0});
                    }//for extras
                }//for catagories


                //if we have received a sale id we need to enter the details in the form
                if (this.sale_id){




                    //check if current sale event id is in events list
                    this.sale_event_active = this.events.some(el => el.id === this.selection.event_id);
                    console.log("found live event?:"+this.sale_event_active);

                    //compare event extras with sale extras
                    this.checkSaleDateTransferrable();

                    //if we are editing an exisiting sale we have to get the selected ticket and promocode details

                    //only the first update this is needed
                    if(this.first_update){

                        console.log("current event date:"+this.event.event_date);
                        this.selected_event_date = this.event.event_date;//used for displaying original date in update screen

                        if (this.selection.ticket_id>0){
                            this.ticket = this.tickets.find(ticket => ticket.id === this.selection.ticket_id);
                            console.log('current ticket loaded');
                        }
                        if (this.selection.promocode_id>0){
                            this.promocode = this.promocodes.find(promocode => promocode.id === this.selection.promocode_id);
                            console.log('current promocode loaded');

                            //if we have a promocode we also need to fake a promocode on change event to set all the values:
                            this.onChangePromoCode();
                        }

                    }
                }
                this.first_update=false;

            })
            .catch(error => {
                //error.response.data.errors

                console.log("error getevent");
                console.log('error = '+error);
                this.showErrors(error);
            });

        },

        async readPromoCodes(){
            console.log('get promocodes before');
           try{
                let response = await axios.get("/admin/promocode");
                this.promocodes = response.data.promocodes;
            }
            catch(error){
                console.log(error)
            }
            console.log('get promocodes after');
        },



        checkSaleDateTransferrable(){
            console.log('checking sale transferrable');
            //selected date event details are in this.selection, this.tickets and this.extras
            //current sale details are stored in this.selected_sale and this.selected_extras
            // now compare and set all selected options into new date where possible and notify where not possible
            //this.warning_messages="Not found: ";
            var warnings=false;

            //first check tickets
            if(this.tickets.find(ticket => ticket.id === this.selected_sale.ticket_id)){
                console.log('ticket found');
                this.selection.ticket_id=this.selected_sale.ticket_id;
            }
            else{
                console.log('ticket not found');
                this.selection.ticket_id="";//verplaatsen naar reset?
                this.warning_messages+="ticket not found";
                warnings=true;
               // this.showWarningMessage("ticket not found")
            }

            //then check extras
            for (var i = 0; i < this.selected_extras.length; i++  ) {
                var index = this.extras.findIndex(extra => extra.id === this.selected_extras[i].id);
                if (index>=0){
                    console.log('extra found, id='+index);
                        this.extras[index].nr = this.selected_extras[i].nr;

                }
                else{
                    console.log('extra not found, id='+index);
                    //check if this extra was selected
                    if (this.selected_extras[i].nr){
                        this.warning_messages+=" + "+this.selected_extras[i].title + " not found";
                        warnings=true;
                    }
                    else{
                        //extra not found, but no problem
                        console.log('extra not found, but not selected, so its fine'+this.selected_extras[i].title);
                    }
                }
            }//end check extras

            if(warnings) this.showWarningMessage(this.warning_messages);
        },




        deleteBasket(){
            axios
            .get("admin/deletesessionbasket")

            .then(response => {
                console.log(response.data.message);
            })

            .catch(error => {
                console.log(error.response.data.message);
                // Error
            });

        },

        updateBasket(){

            if (this.selection.event_id>0 && this.selection.nr_tickets>0 && this.selection.ticket_id>0){
                axios
                    .post("/basket", this.selection )
                    .then(response => {
                        console.log('basket updated');
                    })
                    .catch(error => {
                        console.log('errors: '+error);
                       this.selection.nr_tickets=0;
                       console.log("error updatebasket");
                        this.showErrors(error);
                    });
            }//end if

        },

        checkAvailability(){
            this.show_warning = false;
            axios
                .post("/eventcheckavailability",
                {
                    event_id: this.selected_sale.event_id,
                    sale_id: this.selected_sale.id,
                    ingore_reserved: false,
                })
                .then(response => {
                    console.log(response.data);
                    if (response.data.available<this.selection.nr_tickets){//not enough tickets
                        console.log('not enough tickets');
                        this.not_enough_tickets=true;//will be checked before updating sale
                        if (response.data.available===0){
                            this.warning_messages="Another customer is currently holding the last tickets, please try again in 10 minutes."
                        }
                        else{
                            this.warning_messages="not enough tickets, only "+response.data.available+" available."
                            console.log(this.warning_messages);
                        }

                        this.showWarningMessage(this.warning_messages);
                    }
                    else{//enough tickets available
                        this.not_enough_tickets=false;
                    }

                })
                .catch(error => {
                });
        },


        onTicketChange(){
            this.ticket = this.tickets.find(ticket => ticket.id === this.selection.ticket_id);
             if (this.add_update === "add")  this.updateBasket();
             else{
                //we are updating a sale, no basket, nothing needs to be done
            }
            console.log('new ticket loaded: '+ this.ticket.title);
        },
        onNrTicketsChange(){
            if (this.add_update === "add")  this.updateBasket();
            else{
                //we are updating a sale, no basket, just check availability
                this.checkAvailability();
            }
            this.errors="";
            console.log('nrtickets changed:'+this.selection.nr_tickets);
        },
        onChangePromoCode(){
            console.log('promocode changed');
            this.promocode_error_message=false;
            console.log(this.selection.promocode_id);
           if (this.selection.promocode_id>0){
                //check code
                this.promocode = this.promocodes.find(promocode => promocode.id === this.selection.promocode_id);
                console.log('checking '+ this.promocode.code);

                axios.get("/checkpromocode/"+this.promocode.code).then(response => {

                    if (response.data.promocode!='false'){//we have a valid code

                        this.valid_promocode = true;
                        //this.promocode = this.promocodes.find(promocode => promocode.id === this.selection.promocode_id);
                        console.log("we have a valid code. ID= "+this.selection.promocode_id);
                    }
                    else{
                        console.log("invalid code");
                        this.valid_promocode = false;
                        this.promocode_error_message=true;
                    }
                });
            }
            else{
                console.log("no code");
                this.valid_promocode = false;
                }
        },
        getPromoCode(id){//show promocode code in sales list
            console.log('getting promocode');
            this.promocode = this.promocodes.find(promocode => promocode.id === id);
            console.log("promocode id="+id+". code found ="+this.promocode.code);
            return this.promocode.code;
        },
        onEventChange(){
            console.log('event change, first update? '+this.first_update);
            if(!this.first_update){//on entering the form we are updating the event, but this is not an event change, therefore ignore
                this.readEvent();
                if (this.add_update === "add")  this.updateBasket();//basket only needs create or update when tickets have been selected
                else{
                    //we are updating a sale, no basket, just check availability
                    this.checkAvailability();
                }

            }

        },

        onCountryChange(data){
            console.log('country changed');
            this.phone_data=data;
            if (this.selection){
                this.selection.dial_code = this.phone_data.dialCode;
                this.selection.country_code = this.phone_data.iso2;

            }
        },

         //helper function for iterating over the correct number of tickets
        getNumbers:function(start,stop){

            stop++;//add one to the end to include the last iteration (2-6 tickets needs 5 iterations, not 4)
            return new Array(stop-start).fill(start).map((n,i)=>n+i);
        },

        //helper function for formatting ticketnr
        formatDate(date, no_year="") {
            var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear().toString().substr(-2);
            if (month.length < 2)
                month = '0' + month;
            if (day.length < 2)
                day = '0' + day;
            if (no_year) return [day,month].join('');
            else return [day,month,year].join('');
        },

        showWarningMessage(warning_message) {
            this.warning_messages = warning_message;
            this.show_warning = true;
        },

         showErrors(error) {
             console.log('there are errors: '+error.response);
            this.errors = "<ul>";
            let response = error.response;
            Object.keys(response.data.errors).forEach(item => {
                this.errors += "<li>" + response.data.errors[item] + "</li>";
            });
            this.errors += "</ul>";
        },




    },//methods
    computed:{

        //calculate total amount in basket

        totalAmount: function () {
            this.selection.total_discount=0;
            this.selection.paying_now = this.paying_now_div_100 * 100;
            this.selection.extras = this.extras;//copy selected extras into selection which is sent to basket
            let totalTickets=0;
            let totalExtras=0;
          //  this.selection.extras=[];//clear extras selection and rebuild
            totalTickets = this.selection.nr_tickets*(this.ticket.price);

            for (var i = 0; i < this.extras.length; i++  ) {
                if(this.extras[i].nr){
                    if(this.extras[i].max==="ticket"){
                        totalExtras+= this.extras[i].price*this.selection.nr_tickets;
                    }
                    else{
                        totalExtras+= this.extras[i].price*this.extras[i].nr;
                    }
                }
            }


             if (this.valid_promocode){//we have a valid promocode, calculate discounts

                if(this.promocode.discount_amount){
                     this.selection.total_discount += this.promocode.discount_amount;

                     //Here we assume the total discount amount is less than the total ticket price.
                     //If the discount is larger the calculation is incorrect!!
                }

                if(this.promocode.discount_perc){

                    if(this.promocode.apply_to_tickets){
                        this.selection.total_discount += (this.promocode.discount_perc/100)*totalTickets;

                    }
                    if(this.promocode.apply_to_extras){
                         this.selection.total_discount += (this.promocode.discount_perc/100)*totalExtras;

                    }

                }
             }

            this.selection.total_amount = totalTickets + totalExtras - this.selection.total_discount;

            //adjust remaining amount when paying now changes
            this.remaining_after_paying_now = this.selection.total_amount - this.selection.amount_paid-this.selection.paying_now;
            console.log('total amount: '+this.selection.total_amount +' paying now: '+this.selection.paying_now +' already paid: ' +this.selection.amount_paid +' remaining: '+this.remaining_after_paying_now  );

            return this.selection.total_amount;
        }
    }
}//export default
</script>
