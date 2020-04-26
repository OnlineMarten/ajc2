<!--
EXPLANATION concerning availability checks
when a new sale is made the availability will automatically be checked when a basket is made or updated, through the session
which stores the basket id.
When a current sale is updated (including soft deleted sales) the availability will be checked from this component
with the checkavailability call
-->

<template>
<div>

    <div class="row">
        <div class="col-md-12">

            <div class="alert alert-info alert-dismissible fade show" v-if="show" role="alert" id="alert" name="alert">
                <button type="button" class="close" v-on:click="show = !show" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {{ message }}
            </div>

            <div class="card">
                <div class="card-header">Reservations


                </div>
                <!--card header-->
                <div class="card-body">
                    <button @click="initAddSale()" class="btn btn-primary btn-xs float-left">
                            + Add New Reservation
                    </button>

                    <br><hr>
                    <input type="checkbox" id="checkbox" v-model="show_deleted_sales" v-on:change="onToggleDeletedSales()">
                    <label for="checkbox">Show deleted reservations</label>
              <!--  <input type="checkbox" id="checkbox" v-model="show_past_events">
                    <label for="checkbox">show past events</label>
            -->
                    <table class="table table-striped table-bordered table-responsive table-sm" v-if="sales.length > 0" ref="table">
                        <thead>
                            <tr>


                                <th>Reservation made on</th>
                                <th>Event Date</th>
                                <th>tickets</th>
                                <th>name</th>
                                <th>country</th>
                                <th>details</th>
                                <th>ticket sent</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(sale, index) in sales" :key="sale.id">
                         <!--   <template v-if="show_past_sales || (!show_past_sales && (new Date(sale.event_date) >= Date.now()))">
-->
                                <td>
                                    {{ new Date(sale.created_at) | dateFormat('HH:mm dd DD MMM YYYY') }}
                                </td>
                                <td>
                                    {{ new Date(sale.event_date) | dateFormat('dd DD MMM YYYY') }}
                                </td>
                                <td>
                                    {{ sale.nr_tickets}} x {{sale.ticket_title}}
                                </td>
                                <td>
                                    {{ sale.name}}
                                </td>
                                <td>
                                    {{ sale.country_code}}
                                </td>
                                <td>
                                   <small>{{ sale.ticket_nr}}<br>

                                           <span v-if="sale.promocode_code">Promocode: {{sale.promocode_code}}<br></span>

                                       Total: {{(sale.total_amount+sale.total_discount) | toCurrency}}
                                       <span v-if="sale.promocode_id">Discount: {{(sale.total_discount) | toCurrency}} </span>
                                            Still to pay: {{(sale.total_amount-sale.amount_paid) | toCurrency}}
                                           <br>pspRef: {{sale.pspReference}}</small>
                                </td>
                                <td>
                                    <span v-if="sale.ticket_sent">{{ new Date(sale.ticket_sent) | dateFormat('dd DD MMM YYYY') }}</span>
                                </td>

                                <td>

                                    <button @click="initUpdateSale(index)" class="btn btn-success btn-sm"><i class="fas fa-edit"></i>Edit</button>
                                    <button @click="deleteSale(index)" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i>Delete</button>
                                </td>

                        <!--    </template>-->
                              </tr>
                        </tbody>
                    </table>
                     <div v-else>
                        <br><hr>
                        <p>No sales yet</p>
                    </div>
                </div>
                <!--card body-->

            </div>
            <!--card-->

        </div>
        <!--col-->
    </div>
    <!--row-->
 <div class="modal fade" tabindex="-1" role="dialog" id="add_sale_model">

        <!--MODAL ADD UPDATE-->
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">
                        <span v-if="add_update=='add'">Add New Reservation</span><span v-else>Edit Reservation</span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>

                </div>
                <!--modal header-->


                <div class="modal-body">

                    <div class="alert alert-danger" v-if="errors.length > 0">
                        We have errors:
                        <ul>
                            <span v-html="errors">{{showErrors()}}</span>
                        </ul>
                    </div>
                    <div class="alert alert-danger" v-if="show_warning">
                        Warning messages:
                        <ul>
                            {{ warning_messages }}
                        </ul>
                    </div>



                <div class="row">
                    <div class="col-sm-3">
                        Select event:
                    </div>
                    <div class="col-sm-8">
                        <div v-if="add_update=='update'">Current reservation date: {{new Date(selected_event_date) | dateFormat('dd DD MMM YYYY')}}</div>
                        <!--show select box for choosing event date-->
                        <select v-model="selection.event_id" v-on:change="onEventChange()">
                            <option v-if="add_update=='add'" value="" selected disabled>Choose date</option>
                            <option v-for="event in events" :value="event.id" :key="event.id">{{new Date(event.date) | dateFormat('dd DD MMM YYYY')}}</option>
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
</div><!-- row-->

                        <!--modal body-->

                        <div class="modal-footer">
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
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->


</div>
</template>



<script>
import { VueTelInput } from 'vue-tel-input'

//import axioscalls from '@./resources/services/axioscalls'
export default {
  components: {
        VueTelInput,
  },

  data(){
    return {

        counter:"0",
        show_error:false,
        show: false,
        show_warning:false,
        error:"",
        errors: "",
        show_past_sales:false,
        message: "",
        warning_messages:"",
        add_update: "",

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

        //load promocodes and available events so it is ready in case we want to add or update a sale.
        this.readPromoCodes();
        this.readAllEvents();
        this.readSales();


    },//mounted

    methods: {
        cancelAddSale() {
            this.errors="";
            this.reset();
            this.deleteBasket();
            console.log('cancel add');

        },
        cancelUpdateSale(){
            this.reset();
             this.readSales();
             console.log('cancel update');

        },
        reset(){
            //this.selection ={};
            this.selection.name="";
            this.selection.email="";
            this.selection.phone="";
            this.selection.country_code="NL";
            this.selection.nr_tickets="0";
            this.selection.ticket_nr="";
            this.selection.guestlist_comments="";
            this.selection.admin_comments="";
            this.selection.lang="en";
            this.selection.event_id="";
            this.selection.ticket_id="";
            this.selection.promocode_id="0";
            this.selection.extras=[];
            this.selection.total_amount="0";
            this.selection.total_discount="0";
            this.selection.amount_paid="0";
            this.selection.paying_now="0";
            this.paying_now_div_100="0";
            this.categories=[];
           // this.promocode={};
            this.promocodes=[];
            this.promocode.id="0";
            this.promocode.code="";
            this.promocode.discount_amount="0";
            this.promocode.discount_perc="0";
            this.promocode.apply_to_extras="0";
            this.promocode.apply_to_tickets="0";
            this.errors="";
            this.show_error=false;
            this.valid_promocode=false;
            this.promocode_error_message=false;
            this.show_warning=false;
            this.not_enough_tickets=false;
            this.show_deleted_sales=false;
            this.show_warning=false;
        },



        async readSales(){
            console.log('get sales before');
           try{
                let response = await axios.get("/admin/sale");
                this.sales = response.data.sales;
            }
            catch(error){
                console.log(error)
            }
            console.log('get sales after');
        },

        readDeletedSales(){
            axios.get("/admin/deletedsales").then(response => {
                this.sales = response.data.sales;
            });
        },

        readAllEvents(){
            axios.get("allevents").then(response => {
                this.events = response.data.events;
            });
        },

         readEvent(){
            this.show_warning=false;
            this.warning_messages="";
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

                if(this.add_update==="update"){
                    console.log('compare events')
                    this.checkSaleDateTransferrable();
                }
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
            //move extras to
          //  this.selected_extras=this.extras;
           // this.extras=[];
            //selected date event details are in this.selection, this.tickets and this.extras
            //current sale details are stored in this.selected_sale and this.selected_extras
            // now compare and set all selected options into new date where possible and notify where not possible
            this.warning_messages="Not found: ";
            var warnings=false;

            //first check tickets
            if(this.tickets.find(ticket => ticket.id === this.selected_sale.ticket_id)){
                console.log('ticket found');
                this.selection.ticket_id=this.selected_sale.ticket_id;
            }
            else{
                console.log('ticket not found');
                this.selection.ticket_id="";//verplaatsen naar reset?
                this.warning_messages+="ticket";
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
                        this.warning_messages+=" + "+this.selected_extras[i].title;
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

        initAddSale(){
         //   this.resetSelection();
            this.add_update = "add";
         //   this.readAvailableEvents();
            //this.readPromoCodes();
            $("#add_sale_model").modal("show");

        },

         initUpdateSale(index) {
            this.add_update = "update";
            this.paying_now_div_100="0";
            this.add_update = "update";
            //load event data into selecion and make a copy in case we want to change the date.
            this.selected_sale = this.sales[index];
            this.selection = this.sales[index];
            this.selection.event_id=this.selected_sale.event_id;//set event id into selection because we know that it exists
           // this.selection.extras=[];


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
                console.log("current event date:"+this.event.event_date);
                this.selected_event_date = this.event.event_date;//used for displaying original date in update screen
                //get selected extras sale
                this.temp=[];
                this.selected_extras=[];

                axios.get("admin/salegetextras/"+ this.sales[index].id)
                    .then(response => {
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


                    //compare event extras with sale extras
                    this.checkSaleDateTransferrable();

                    //if we are editing an exisiting sale we have to get the selected ticket and promocode details
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
                        //this.onChangePromoCode();
                    $("#add_sale_model").modal("show");

                    })
                    .catch(error => {
                        //error.response.data.errors
                    //  console.log('error = '+error.response.data.errors);
                    this.showErrors(error);
                    });

            });

        },

        updateSale(){
            console.log('update sale');
            this.checkAvailability();//we need to recheck at the moment we are actually updating as in the meantime the status could hgave changed
            if (this.not_enough_tickets){

            }
            else{
                if (this.selected_event_date !== this.event.event_date){
                    console.log('date changed');
                    this.selection.admin_comments+="* date changed, original date:"+this.selected_event_date;
                }
                axios
                    .put("/admin/sale/" + this.selection.id, this.selection)

                    .then(response => {
                    $("#add_sale_model").modal("hide");
                    this.readSales();
                    this.showMessage(response.data.message);
                    this.reset();

                    console.log('response');
                    })

                .catch(error => {
                    //error.response.data.errors
                    console.log('error = '+error.response.data.errors);
                this.showErrors(error);
                });
            }
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
                        $("#add_sale_model").modal("hide");
                        //refresh table on screen (there may be a better way of doing this) *verbeterpunt*
                        this.readSales();
                        this.showMessage(response.data.message);
                        this.reset();
                    })
                    .catch(error => {
                        //coud not add sale, show error
                        this.showErrors(error);
                    });

                })
                .catch(error => {
                    //could not update basket, show error
                     this.showErrors(error);
            });


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

        deleteSale(index) {
            if (this.show_deleted_sales){//we are destroying a deleted sale
                let conf = confirm(
                'Do you ready want permanently delete reservation "' +
                this.sales[index].ticket_nr +
                '"? This can not be undone!'
                );
                if (conf === true) {
                    axios
                    .get("/admin/forcedeletesale/" + this.sales[index].id)

                    .then(response => {
                        this.sales.splice(index, 1);
                        this.showMessage(response.data.message);
                    })

                    .catch(error => {
                        this.showMessage(error.response.data.message);
                        // Error
                    });
                }

            }
            else{//soft delete a sale
                let conf = confirm(
                'Do you ready want to delete reservation "' +
                this.sales[index].ticket_nr +
                '"? (It will be placed in deleted reservations and can be restored later)'
                );
                if (conf === true) {
                    axios
                    .delete("/admin/sale/" + this.sales[index].id)

                    .then(response => {
                        this.sales.splice(index, 1);
                        this.showMessage(response.data.message);
                    })

                    .catch(error => {
                        this.showMessage(error.response.data.message);
                        // Error
                    });
                }
            }
        },



        updateBasket(){

            if (this.selection.event_id>0 && this.selection.nr_tickets>0 && this.selection.ticket_id>0){
                axios
                    .post("/basket", this.selection )
                    .then(response => {
                        console.log('basket updated');
                    })
                    .catch(error => {
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
        onToggleDeletedSales(){
          //  this.show_deleted_sales=!this.show_deleted_sales;
            if(this.show_deleted_sales){
                console.log('show deleted sales');
                this.readDeletedSales();
            }
            else{
                console.log('show normal sales');
                this.readSales();
            }


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
                    console.log("response="+response.data.promocode)
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
            console.log('event change')
            this.readEvent();
            if (this.add_update === "add")  this.updateBasket();//basket only needs create or update when tickets have been selected
            else{
                //we are updating a sale, no basket, just check availability
                this.checkAvailability();
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

        showMessage(message) {
            this.message = message;
            this.show = true;
        },

        showWarningMessage(warning_message) {
            this.warning_messages = warning_message;
            this.show_warning = true;
        },

         showErrors(error) {
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
