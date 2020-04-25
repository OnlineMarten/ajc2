<template>
<div>
    <div v-if="event">
        <div class="row mt-1">


<!-- basket-->
<div class="col-sm-5 order-sm-2 mb-4" >
        <div class="col-md-12 ">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Order details</span>
            <span class="badge badge-secondary badge-pill">3</span>
          </h4>
          <ul class="list-group mb-3">

            <li class="list-group-item d-flex justify-content-between lh-condensed">

              <div>
                <h6 class="my-0"> {{ new Date(event.event_date) | dateFormat('dd DD MMM YYYY') }}<br>
                {{selection.nr_tickets}} tickets</h6>
                <small v-if="selection.nr_tickets>0 && selection.ticket_id" class="text-muted">({{ticket.title}}  {{ticket.price  | toCurrency}})</small>
              </div>

              <span v-if="selection.nr_tickets>0 && selection.ticket_id" class="text-muted">{{selection.nr_tickets * ticket.price  | toCurrency}}</span>

            </li>





            <span v-for="(extra) in extras" :key="extra.id">



                                    <span v-if="extra.max === 'ticket'">

                                           <li v-if="extra.nr>0" class="list-group-item d-flex justify-content-between lh-condensed">

                            <div >
                                <h6 class="my-0">{{ extra.title }}</h6>
                                <small class="text-muted">({{selection.nr_tickets }} x {{extra.price | toCurrency}})</small>
                                <span class="text-muted">{{selection.nr_tickets * (extra.price) | toCurrency}}</span>
                            </div>



                            </li>
                                    </span>
                                    <span v-else>

                                         <li v-if="extra.nr>0" class="list-group-item d-flex justify-content-between lh-condensed">

                                <div >
                                <h6 class="my-0"> {{ extra.title }}</h6>
                                    <small class="text-muted">{{ extra.nr }} x {{extra.price  | toCurrency}}</small>
                                    <span class="text-muted">{{extra.nr * (extra.price) | toCurrency}}</span>
                                </div>



                                </li>
                                    </span>
                    </span><!--extras-->






            <li v-if="valid_promocode" class="list-group-item d-flex justify-content-between bg-light">
                    <span>Total without discount</span>
              <strong>{{(totalAmount+this.selection.total_discount) | toCurrency}}</strong>

            </li>

            <li v-if="valid_promocode" class="list-group-item d-flex justify-content-between bg-light">
              <div class="text-success">
                <h6 class="my-0">Promo code Discount</h6>
                <small>
                    <span v-if="promocode.discount_amount">- {{promocode.discount_amount | toCurrency}}</span>
                    <span v-if="promocode.discount_perc">{{promocode.discount_perc}} %</span>

                    </small>
              </div>
              <span class="text-success">- {{this.selection.total_discount | toCurrency}}</span>
            </li>

            <li v-if="selection.nr_tickets>0 && selection.ticket_id" class="list-group-item d-flex justify-content-between">
              <span>Total</span>
              <strong>{{totalAmount | toCurrency}}</strong>
            </li>
          </ul>

          <form class="card p-2">
               <small class="text-danger" v-if="promocode_error_message"> * Invalid code</small>
            <div class="input-group">

              <input type="text" v-model="promocode.code" class="form-control" placeholder="Promo code">
              <div class="input-group-append">
                <button type="submit" class="btn btn-secondary" @click.prevent="checkPromoCode()">Redeem</button>
              </div>
            </div>
          </form>
        </div>
</div>
 <!--end basket-->

<div class="col-sm-7 order-sm-1 shadow-sm p-3 mb-5 bg-white rounded">



    <div class="row">
        <div class="col-sm-12">



             <div class="alert alert-danger" v-if="errors.length > 0">

                <ul>
                    <span v-html="errors">{{ errors }}</span>
                </ul>
            </div>

            <div class="alert alert-info alert-dismissible fade show" v-if="show" role="alert" id="alert" name="alert">
                <button type="button" class="close" v-on:click="show = !show" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {{ message }}
            </div>

            <hr>

            <!--ticketselection-->

            <!--ticket group title and description
            <div class="form-group" v-if="event.ticketgroup.description && show_titles">
                <h4>{{event.ticketgroup.title}}</h4>
            </div>-->

            <!-- # tickets -->
            <div class="form-group">
                <select v-model="selection.nr_tickets" v-on:change="onNrTicketsChange()">
                    <option  :value="0" >0</option>
                    <option v-for="counter in getNumbers(event.min_per_sale,event.max_per_sale)" :value="counter" :key="counter">{{counter}}</option>
                </select>
                <select v-model="selection.ticket_id" v-on:change="onTicketChange()">
                       <option value=0 disabled>Select ticket type</option>
                    <option v-for="(ticket) in tickets" :value="ticket.id" :key="ticket.id">{{ticket.title}}</option>
                </select>
            </div>
            <!--end ticketselection-->
        </div>
    </div>

    <!--extras selection-->
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
    <!--end extras selection-->


<!--contact details-->
<div class="row">
        <div  class="col-sm-12">
            <hr>
        <h4>Contact</h4>

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
                    @country-changed="country_changed"
                    :inputOptions="{
                        showDialCode: true,
                        tabindex: 0
                    }"
                    :preferredCountries="[
                    'NL',
                    'US',
                    'GB',
                    ]"
                    mode="international"
                    placeholder=""

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

    </div><!--col 12-->
</div><!--row-->
<!--end contact details-->


<hr>
<div class="row">
<div class="col-sm-12">
        <button style="" class="btn btn-primary" type="submit" @click.prevent="prev()">Back</button>
        <button style="float:right" class="btn btn-primary" type="submit" @click.prevent="checkout()" >Go to payment</button>
</div>
</div>

</div><!--col 7-->
</div><!-- row-->

</div><!-- if-event-->
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
         errors: "",
        message: "",
        show:false,
        show_titles:true,
        show_descriptions:false,
        nosteps:true,
        show_payment_page:false,
        event_id:"",
        event:"",
        tickets:"",
        counter:"0",
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
            ticket_id:0,
            promocode_id:"0",
            promocode_code:"",//we need to store the code in case we have a page reload, otherwise we can not safely retrieve the code
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
        categories:[],
        tickets:[],
        extras:[],
        show_error:false,
        error:"",
        phone:"",
        phone_data:[],
        not_enough_tickets:false,

    }//return
  },//data


    mounted() {
        this.event_id = _.last( window.location.pathname.split( '/' ) );
        console.log(this.event_id);
        this.getEvent();

        let uri = window.location.search.substring(1);
        let params = new URLSearchParams(uri);
        let result = params.get("type");
        console.log('type='+result);
        if (result==="cancel"){
            this.error = "You have cancelled the payment."
            this.show_error = true;
        }
        if (result==="refused"){
            this.error = "The payment has been refused. Please check your card details or try another card"
            this.show_error = true;
        }
    },

    methods: {

        country_changed(data){
            console.log('country changed');
            this.phone_data=data;
            if (this.selection){
                this.selection.dial_code = this.phone_data.dialCode;
                this.selection.country_code = this.phone_data.iso2;

            }
        },
        async getBasket(){
            //if we have a basket already (or still) when entering the page we will load it
        console.log('get basket before');
        try{
            let response = await axios.get("/getsessionbasket")
            //console.log(response);
            if (response.data.basket){//we have a basket
                console.log('we have a basket');
                this.selection.email = response.data.basket.email;
                this.selection.phone = response.data.basket.phone;
                this.selection.country_code = response.data.basket.country_code;
                this.selection.dial_code = response.data.basket.dial_code;
                this.selection.nr_tickets = response.data.basket.nr_tickets;
                this.selection.ticket_nr = response.data.basket.ticket_nr;
                //this.selection.event_id = response.data.basket.event_id;
                this.selection.ticket_id = response.data.basket.ticket_id;
                this.selection.promocode_id = response.data.basket.promocode_id;
                this.selection.promocode_code = response.data.basket.promocode_code;
                this.selection.name = response.data.basket.name;

                //get extras
                for (var i = 0; i < response.data.basket.extras.length; i++  ) {
                    var index = this.extras.findIndex(extra => extra.id === response.data.basket.extras[i].id);
                    if (index>=0){
                        console.log('extra found, id='+index);
                            this.extras[index].nr = response.data.basket.extras[i].nr;

                    }
                    else{
                        console.log('extra not found: '+response.data.basket.extras[i].title);
                    }
                }

                console.log('get basket after');
                console.log('get ticket');
                this.ticket = this.tickets.find(ticket => ticket.id === this.selection.ticket_id);
                if (!this.ticket){
                    this.selection.ticket_id="0";
                    console.log('no ticket found')
                }
                else console.log('ticket found');

                if (this.selection.promocode_id){//we have loaded a promocode from the basket, reinstall it
                    console.log('we have a promocode, needs to be loaded')
                    this.promocode.code =this.selection.promocode_code;
                    this.checkPromoCode();
                }
                //update basket to sync it with current event (in case the event date was changed)
                this.updateBasket();

            }
            else{
                console.log('no basket');
            }

        }
        catch(error){
            console.log(error)
        }
        },
         async checkPromoCode(){
            console.log('checking code: '+this.promocode.code);
            //reset values
            this.selection.promocode_id="0";
            this.selection.promocode_code="";
            this.promocode.discount_amount="0";
            this.promocode.discount_perc="0";
            this.promocode.apply_to_extras="0";
            this.promocode.apply_to_tickets="0";
            this.promocode_error_message=false;

            if (this.promocode.code){

                try{
                    let response = await axios.get("/checkpromocode/"+this.promocode.code)
                    console.log("response="+response.data.promocode)
                    if (response.data.promocode!=='false'){//we have a valid code
                        console.log("we have a valid code")
                        this.valid_promocode = true;
                        this.promocode = response.data.promocode;
                        this.selection.promocode_id = this.promocode.id;
                        this.selection.promocode_code = this.promocode.code;
                    }
                    else{
                        console.log("invalid code");
                        this.valid_promocode = false;
                        this.promocode_error_message=true;
                    }
                }
                catch(error){
                    console.log(error)
                }
            }
            else{
                console.log("no code entered");
                this.valid_promocode = false;
            }

        },

        async getEvent()
        {
            console.log('get event before');
           try{
                let response = await axios.get("/getevent/"+this.event_id)
                this.event = response.data.event.event;
                //load event details
                this.categories = response.data.event.categories;
                this.tickets = response.data.event.tickets;
                this.show_event_details=true;
                this.selection.event_id = this.event.id;
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
                console.log('get event after');
                this.getBasket();
            }
            catch(error){
                console.log(error)
            }
        },

        checkAvailability(){
            this.show_warning = false;
            axios
                .post("/eventcheckavailability",
                {
                    event_id: this.event_id,
                    sale_id: "",
                    ingore_reserved: false,
                })
                .then(response => {
                    console.log(response.data);
                    if (response.data.available<this.selection.nr_tickets){//not enough tickets
                        console.log('not enough tickets');

                        this.not_enough_tickets=true;//will be checked before updating sale
                        this.warning_messages="not enough tickets, only "+response.data.available+" available."
                        this.showWarningMessage(this.warning_messages);
                    }
                    else{//enough tickets available
                        this.not_enough_tickets=false;
                    }

                })
                .catch(error => {
                    this.showErrors(error);
                });
        },
         updateBasket(){

         //   if ( this.selection.nr_tickets>0 && this.selection.ticket_id>0){
                axios
                    .post("/basket", this.selection )
                    .then(response => {
                        console.log('basket updated');
                        if ( this.selection.nr_tickets>0 && this.selection.ticket_id>0){
                            this.showMessage('The tickets will be reserved for you for 10 minutes');
                        }
                    })
                    .catch(error => {
                        //we should only get here if someone else is currently holding the last tickets
                        //therefore set nr tickets to 0
                        this.selection.nr_tickets=0;
                        this.showErrors(error);
                        this.show=false;
                    });
        //    }//end if

        },
        prev(){
            javascript:history.back();
        },


        checkout(){

            console.log('check necessary info is present and update basket');
            console.log('nrtickets='+this.selection.nr_tickets);
            axios
            .post("/checkbasketcomplete", this.selection )
            .then(response => {
                console.log('basket complete and updated');
                console.log('ticketnr='+response.data.ticket_nr);
                this.selection.ticket_nr = response.data.ticket_nr
                if (this.selection.total_amount>0){
                    console.log("going to payment");
                    //go to multisafepay payment page
                    this.makePayment(this.selection);
                }
                else{
                    console.log("nothing to pay, going to confirmation");
                    window.location = process.env.MIX_APP_URL+"/checkout?direct=true&ticket_nr="+this.selection.ticket_nr;
                }
            })
            .catch(error => {
                //we should only get here if someone else is currently holding the last tickets
                //therefore set nr tickets to 0
                this.showErrors(error);
                this.show=false;
            });
        },

        makePayment(data){
            console.log("data="+data);
            axios.post("/makepayment",{
                amount: this.selection.total_amount,
                shopperEmail: this.selection.email,
                shopperName:this.selection.name,
                telephoneNumber: this.selection.phone,
                shopperStatement: this.selection.nr_tickets +" tickets "+this.ticket.title,
                countryCode: this.phone_data.iso2,
                ticket_nr: this.selection.ticket_nr,
                tickets:this.selection.nr_tickets +" "+this.ticket.title,
                event_id: this.selection.event_id,

            })
            .then(function (response) {
                // handle success
                console.log( 'response from makepayment:' + response.data)
                console.log("payment data:"+ response.data.payment_url);

                window.location = response.data.payment_url;
            })
            .catch(function (error) {
                //we could not create payment link
                console.log(error.toJSON());
                //TODO send damin email?
            })
        },

        cancel(){
            window.history.back();
        },

        showMessage(message) {
            this.message = message;
            this.show = true;
        },
        showErrors(error) {
            this.errors = "<ul>";
            let response = error.response;
            Object.keys(response.data.errors).forEach(item => {
                this.errors += "<li>" + response.data.errors[item] + "</li>";
            });
            this.errors += "</ul>";
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

        //helper function for iterating over the correct number of tickets
        getNumbers:function(start,stop){

            stop++;//add one to the end to include the last iteration (2-6 tickets needs 5 iterations, not 4)
            return new Array(stop-start).fill(start).map((n,i)=>n+i);
        },
        onTicketChange(){
            this.ticket = this.tickets.find(ticket => ticket.id === this.selection.ticket_id);
            this.updateBasket();

            console.log('new ticket loaded: '+ this.ticket.title);
        },
        onNrTicketsChange(){
            this.updateBasket();
            if (this.selection.nr_tickets===0) this.show=false;
            this.errors="";
            console.log('nrtickets changed:'+this.selection.nr_tickets);
        },



    },//methods
    computed:{

              //calculate total amount in basket

        totalAmount: function () {
            this.selection.total_discount=0;
         //   this.selection.paying_now = this.paying_now_div_100 * 100;
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
                        this.selection.total_discount += Math.round((this.promocode.discount_perc/100)*totalTickets);

                    }
                    if(this.promocode.apply_to_extras){
                         this.selection.total_discount += Math.round((this.promocode.discount_perc/100)*totalExtras);

                    }

                }
             }

            this.selection.total_amount = totalTickets + totalExtras - this.selection.total_discount;
            console.log('total tickets'+totalTickets +'total extras'+totalExtras +'total discount' +this.selection.total_discount );
            //adjust remaining amount when paying now changes
         //   this.remaining_after_paying_now = this.selection.total_amount - this.selection.amount_paid-this.selection.paying_now;
            console.log('total amount: '+this.selection.total_amount +' paying now: '+this.selection.paying_now +' already paid: ' +this.selection.amount_paid +' remaining: '+this.remaining_after_paying_now  );

            return this.selection.total_amount;
        }
    }
}//export default
</script>
