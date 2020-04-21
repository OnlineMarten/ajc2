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
                                       <span v-if="sale.promocode_id">
                                           Promocode:{{ getPromoCode(sale.promocode_id)}}<br></span>

                                       Total: {{(sale.total_amount+sale.total_discount) | toCurrency}}
                                       <span v-if="sale.promocode">Discount: {{(sale.total_discount) | toCurrency}} </span>
                                            Still to pay: {{(sale.total_amount-sale.amount_paid) | toCurrency}}
                                           <br>pspRef: {{sale.pspReference}}</small>
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
    }//return
  },//data


    mounted() {
        console.log('mounted');
        this.readSales();
        //load promocodes and available events so it is ready in case we want to add or update a sale.
        this.readPromoCodes();
        this.readAvailableEvents();

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
            this.promocode={};
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
        },



        readSales(){
            axios.get("/admin/sale").then(response => {
                this.sales = response.data.sales;
            });
        },
        readDeletedSales(){
            axios.get("/admin/deletedsales").then(response => {
                this.sales = response.data.sales;
            });
        },

        readAvailableEvents(){
            axios.get("openevents").then(response => {
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

        readPromoCodes(){
            axios.get("admin/promocode").then(response => {
                this.promocodes = response.data.promocodes;
            });
        },

        checkSaleDateTransferrable(){
                console.log('checking sale transferrable');
            //move extras to
          //  this.selected_extras=this.extras;
           // this.extras=[];
            //selected date event details are in this.selection, this.tickets and this.extras
            //current sale details are stored in this.selected_sale and this.selected_extras
            // now compare and set all seleted options into new date where possible and notify where not possible
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

        refreshBaskets(){
            axios
                .get("/refreshbaskets")
                .then(response => {
                })
                .catch(error => {
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
                this.promocode.code = this.promocodes.find(promocode => promocode.id === this.selection.promocode_id).code;
                console.log('checking '+ this.promocode.code);

                axios.get("/checkpromocode/"+this.promocode.code).then(response => {
                    console.log("response="+response.data.promocode)
                    if (response.data.promocode!='false'){//we have a valid code

                        this.valid_promocode = true;
                        this.promocode = this.promocodes.find(promocode => promocode.id === this.selection.promocode_id);
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
            this.promocode = this.promocodes.find(promocode => promocode.id === id);
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
