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
                <h6 class="my-0"> {{ new Date(event.event.event_date) | dateFormat('dd DD MMM YYYY') }}<br>
                {{selection.nrtickets}} tickets</h6>
                <small v-if="selection.nrtickets>0 && selection.ticket!==0" class="text-muted">({{selection.ticket.title}}  {{selection.ticket.price  | toCurrency}})</small>
              </div>

              <span v-if="selection.nrtickets>0 && selection.ticket!==0" class="text-muted">{{selection.nrtickets * (selection.ticket.price)  | toCurrency}}</span>

            </li>

            <span v-for="category in selection.categories" :key="category.title">
                <span v-for="(extra) in category.extras" :key="extra.id">

                    <span v-if="extra.max === 'ticket' && extra.selected">
                        <span v-if="selection.nrtickets>0 ">

                            <li class="list-group-item d-flex justify-content-between lh-condensed">

                            <div>
                                <h6 class="my-0">{{ extra.title }}</h6>
                                <small class="text-muted">({{selection.nrtickets }} x {{extra.price | toCurrency}})</small>
                            </div>

                            <span class="text-muted">{{selection.nrtickets * (extra.price) | toCurrency}}</span>

                            </li>

                        </span>
                    </span>

                    <span v-else>
                        <span v-if="extra.selected>0">
                            <span v-if="selection.nrtickets>0 ">

                                <li class="list-group-item d-flex justify-content-between lh-condensed">

                                <div>
                                <h6 class="my-0"> {{ extra.title }}</h6>
                                    <small class="text-muted">{{ extra.selected }} x {{extra.price  | toCurrency}}</small>
                                </div>

                                 <span class="text-muted">{{extra.selected * (extra.price) | toCurrency}}</span>

                                </li>

                            </span>
                        </span>
                    </span>

                </span>
            </span>
<!--
            <li class="list-group-item d-flex justify-content-between bg-light">
              <div class="text-success">
                <h6 class="my-0">Promo code</h6>
                <small>10% discount</small>
              </div>
              <span class="text-success">-$5</span>
            </li>
-->
            <li v-if="selection.nrtickets>0 && selection.ticket!==0" class="list-group-item d-flex justify-content-between">
              <span>Total (EURO)</span>
              <strong>{{totalAmount | toCurrency}}</strong>
            </li>
          </ul>

          <form class="card p-2">
            <div class="input-group">
              <input type="text" class="form-control" placeholder="Promo code">
              <div class="input-group-append">
                <button type="submit" class="btn btn-secondary">Redeem</button>
              </div>
            </div>
          </form>
        </div>
</div>
 <!--end basket-->

<div class="col-sm-7 order-sm-1 shadow-sm p-3 mb-5 bg-white rounded">



    <div class="row">
        <div class="col-sm-12">
            <hr>

            <!--ticketselection-->

            <!--ticket group title and description-->
            <div class="form-group" v-if="event.ticketgroup.description && show_titles">
                <h4>{{event.ticketgroup.title}}</h4>
            </div>

            <!-- # tickets -->
            <div class="form-group">
                <select v-model="selection.nrtickets">
                    <option  :value="0" >0</option>
                    <option v-for="counter in getNumbers(event.event.min_per_sale,event.event.max_per_sale)" :value="counter" :key="counter">{{counter}}</option>
                </select>
                <select v-model="selection.ticket">
                       <option :value="0" disabled>Select ticket type</option>
                    <option v-for="(ticket) in event.tickets" :value="ticket" :key="ticket.id">{{ticket.title}}</option>
                </select>
            </div>
            <!--end ticketselection-->
        </div>
    </div>

    <!--extras selection-->
    <span v-if="selection.nrtickets>0 && selection.ticket!==0">
        <span v-for="category in selection.categories" :key="category.id" >

            <div class="row">
            <div class="col-sm-12">
                <hr>
            <div class="form-group">
            <h4 v-if="show_titles">{{category.title}}</h4>
            <p v-if="show_descriptions">{{category.description}}</p>

            <span v-for="(extra, index) in category.extras" :key="extra.id">
                <span v-if="extra.max === 'ticket'">
                    <input type="checkbox" :id="index" :value="extra.title" v-model="extra.selected">
                    <label :for="extra.id" class="form-check-label">{{ extra.title }} {{extra.price | toCurrency}} per person</label>

                    <p class="text-right" v-if="extra.selected>0" >Total: {{selection.nrtickets*extra.price | toCurrency}}</p>
                </span>
                <span v-else>

                    <select name="active" v-model="extra.selected">
                        <option selected value="0">0</option>
                        <option v-for="counter in parseInt(extra.max)" :key="counter" >{{counter}}</option>
                    </select>
                    <label :for="extra.id" class="form-check-label">{{ extra.title }} {{extra.price | toCurrency}}</label>

                    <p class="text-right" v-if="extra.selected>0" >Total: {{extra.selected*extra.price | toCurrency}}</p>
                    <hr>
                </span>


                <div class="col-sm-5 ml-3" v-if="extra.description && show_descriptions">Info: {{extra.description}}</div>
                <br/>
            </span>
            </div><!--form-group-->
            </div><!--col 8-->
            </div><!--row-->
        </span>
    </span>
    <!--end extras selection-->


<!--contact details-->
<div class="row">
        <div v-show="selection.nrtickets>0 && selection.ticket!==0" class="col-sm-12">
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

<!--payment container-->
<div class="row">
        <div v-show="selection.nrtickets>0 && selection.ticket!==0" class="col-sm-12">
            <hr>
            <h4>Payment</h4>

        <span v-if="show_error">error? {{error}}</span>
    <!--adyen drop in-->
    <div class="checkout-container">
        <div class="payment-method">
            <div id="dropin-container">
            <!-- Drop-in will be rendered here -->
            </div>
        </div>
    </div>
    <!--end adyen drop in-->
    </div><!--col 12-->
</div><!--row-->
<!--end payment container-->



<hr>
<div class="row">
<div class="col-sm-12">
        <button style="" v-if="show_payment_page" class="btn btn-primary" type="submit" @click.prevent="prev()">Previous</button>
        <button style="float:right" v-show="selection.nrtickets>0 && selection.ticket!==0" class="btn btn-primary" type="submit" @click.prevent="dropin.submit()" >Complete your booking</button>
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
            countrycode:"",
            nrtickets:"0",
            ticket:0,
            categories:{
                extras:[],
            },
            event:[],
            total_amount:"0",
        },
        show_error:false,
        paymentmethods_not_yet_loaded:true,//to avoid dropin being loaded more than once
        error:"",
        dropin:"",
        phone:"",
        phone_data:[],
    }//return
  },//data


    mounted() {
        console.log('mounted');
        console.log(axios.defaults.baseURL);
        this.event_id = _.last( window.location.pathname.split( '/' ) );
        console.log(this.event_id);
        this.getEvent();
        if(this.paymentmethods_not_yet_loaded){
            this.getPaymentMethods();
            this.paymentmethods_not_yet_loaded =false;
        }

    },//mounted

    methods: {

        country_changed(data){
            console.log('country changed');
            this.phone_data=data;
            if (this.selection){
                this.selection.dialcode = this.phone_data.dialCode;
                this.selection.countrycode = this.phone_data.iso2;

            }
        },

        getEvent()
        {
            axios.get("/getevent/"+this.event_id).then(response => {
            this.event = response.data.event;
            //do we need to check if still available?
            this.selection.categories = response.data.event.categories;
            this.selection.event = response.data.event.event;

            });
        },
        //adyen
        getPaymentMethods()
        {
            //countryCode sets payment options (NL for iDeal)
            //lang-country  sets language on form
            let countrycode = "NL";
            let language_country = "en-US";
            let originkey;
            let environment_setting;

            if (process.env.MIX_APP_ENV =="local"){
                originkey = process.env.MIX_ADYEN_ORIGINKEY_TEST;
                environment_setting = "test";
            }
            if (process.env.MIX_APP_ENV =="production"){
                originkey = process.env.MIX_ADYEN_ORIGINKEY_AJC;
                environment_setting = "live";
            }

            let total_amount = 25000; if (this.totalAmount>0) total_amount = this.totalAmount;
            axios.get("/paymentmethods",{params: {amount: total_amount, countryCode: countrycode}}).then(response => {

            // 1. Create an instance of AdyenCheckout
            const configuration = {
                locale: language_country,
                environment: environment_setting,
                originKey: originkey,
                paymentMethodsResponse:response.data
            };
            const checkout = new AdyenCheckout(configuration);

            // 2. Create and mount the Component
            this.dropin = checkout

            .create("dropin", {
                showPayButton:false,
                paymentMethodsConfiguration: {

                ideal: { // Optional configuration for iDEAL
                    configuration: {
                    showImage: false, // Optional. Set to **false** to remove the bank logos from the iDEAL form.
                    issuer: "0031" // // Optional. Set this to an **id** of an iDEAL issuer to preselect it.

                    },
                    name: 'ideal'
                },

                card: { // Example optional configuration for Cards
                    hasHolderName: true,
                    holderNameRequired: true,
                    enableStoreDetails: true,
                    name: 'creditcard'
                }
                },//end paymentmethodsconfiguration

                onSubmit: (state, dropin) => {
                //makePayment(state.data)
                    // Your function calling your server to make the /payments request
                    console.log(state.data);
                    this.show_error = false;
                    this.makePayment(state.data);
                },//submit

                onSelect(component){
                 //   this.pay_button_text=component.props.name;//werkt niet scope probleem?
                    console.log(component.props.name);

                }

            })//end create

            .mount('#dropin-container');


            });
        },

        makePayment(data){
            console.log(data);
            axios.post("/makepayment",{paymentDetails: data.paymentMethod }).then(response => {
                console.log(response);
                if( response.data.hasOwnProperty('action')) {

                  this.additionalDetails(response.data['action']);

                }

                else{
                  //go to result page and implement this switch there.
                 // window.location = "http://your-company.com/checkout?shopperOrder=12xy"
                  switch (response.data.resultCode) {
                    case "Authorised":
                      // code block
                      console.log('Authorised');
                      console.log(response.data);
                      window.location = process.env.MIX_APP_URL+"/checkout/"+response.data;//get the root folder from the .env file
                      break;
                    case "Cancelled":
                      // code block
                      console.log('Cancelled');
                      break;
                    case "Refused":
                      console.log('Refused');
                      this.error = "The payment has been refused. Please check your card details or try another card"
                      this.show_error = true;
                      //this.dropin.setStatus('error', { message: 'Something went wrong.'});

                     console.log(response);
                      break;
                    default:
                      console.log('something else');
                  }
                }//else
            })

        },
        additionalDetails(result){

            console.log('further action required');

            if(result.type == "redirect"){
                window.location = result.url
            }
            //else do something else here

        },
        //end adyen

        prev() {
            if (!this.nosteps) this.step--;
            this.show_payment_page = false;
        },
        next() {
            this.step++;
        },
        cancel(){
            window.history.back();
        },
        go_to_payment_page(){
            this.show_payment_page = true;

            if(this.paymentmethods_not_yet_loaded){
                this.getPaymentMethods();
                this.paymentmethods_not_yet_loaded =false;
            }
        },

        //helper function for iterating over the correct number of tickets
        getNumbers:function(start,stop){

            stop++;//add one to the end to include the last iteration (2-6 tickets needs 5 iterations, not 4)
            return new Array(stop-start).fill(start).map((n,i)=>n+i);
        },



    },//methods
    computed:{

        //calculate total amount in basket
        totalAmount: function () {
            this.selection.total_amount =  this.selection.nrtickets*(this.selection.ticket.price);

            for (var i = 0; i < this.selection.categories.length; i++  ) {
                console.log("in categories loop");
                for (var n = 0; n < this.selection.categories[i].extras.length; n++  ) {

                   if(this.selection.categories[i].extras[n].selected===true){
                        console.log(this.selection.categories[i].extras[n].title + ' : multiply by nrtickets');
                       // console.log(this.selection.categories[i].extras[n].price + this.selection.categories[i].extras[n].selected);
                        this.selection.total_amount += this.selection.categories[i].extras[n].price*this.selection.nrtickets;
                   }
                   else{
                       if (this.selection.categories[i].extras[n].selected){//check if selected exists, it does not exist automatically
                            console.log(this.selection.categories[i].extras[n].title +  ' : multiply by selected amount');
                           // console.log(this.selection.categories[i].extras[n].price + this.selection.categories[i].extras[n].selected);
                            this.selection.total_amount += this.selection.categories[i].extras[n].price*this.selection.categories[i].extras[n].selected;
                       }
                   }

                }//extras

            }//for catagories

            return this.selection.total_amount;
        }

    }
}//export default
</script>
