<template>
<div class="component-holder">

  <div v-if="show_what === 'events'">

    <br>
    <b-button @click="initAddEvent()" variant="outline-info"><b-icon icon="plus-square"></b-icon> Add New Event(s)</b-button>
    <br><hr>
   <!-- <input type="checkbox" id="checkbox" v-model="show_details_table">
    <label for="checkbox">show detailed table</label>-->



    <b-table sticky-header small hover :items="events" :fields="events_table_fields" head-variant="light">

      <template v-slot:cell(event_date)="data">
        {{ new Date(data.value) | dateFormat('dd DD MMM YYYY') }}
      </template>

      <template v-slot:cell(title)="data">
        <b-button @click="readSales( data.item.id )" size="sm" variant="outline-secondary"> <b-icon icon="person-lines-fill"></b-icon> {{ data.value }} </b-button>
      </template>

      <template v-slot:cell(status)="data">
        <span v-if="(data.item.active==false)"><b-badge variant="secondary">no sale</b-badge></span>
                <span v-else-if="(data.item.sold_out ==true)"><b-badge variant="danger">sold out</b-badge></span>
                <span v-else-if="(data.item.tickets_sold>1)"><b-badge variant="warning">available</b-badge></span>
                <span v-else><b-badge variant="success">available</b-badge></span>
      </template>

      <template v-slot:cell(tickets_sold)="data">
        {{ data.item.tickets_sold }} of {{ data.item.capacity }}
      </template>

      <template v-slot:cell(tickets_reserved)="data">
          <b-form-spinbutton id="" inline size="sm" @change="adjustReservedTickets(data.index)"  v-model="data.item.tickets_reserved" min="0" max="100"></b-form-spinbutton>
      </template>

      <template v-slot:cell(index)="data">
          <b-button @click="initUpdateEvent( data.item.id )" size="sm" variant="outline-info"><b-icon icon="pencil-square"></b-icon></b-button>
          <b-button @click="deleteEvent( data.index )" size="sm" variant="outline-danger"><b-icon icon="trash"></b-icon></b-button>
      </template>


    </b-table>
    <input type="checkbox" id="checkbox" v-model="show_past_events" v-on:change="onShowEventsChange()">
    <label for="checkbox">show past events</label>
    </div><!--show_what = events-->


    <!--crudsalecomponent-->
    <div v-if="show_what === 'sales'">

        <!--<b-button @click="reset" variant="info"><b-icon icon="arrow90deg-left"></b-icon> Back to events</b-button>-->

        <span class=" d-print-none">

        <hr>
        <b-button @click="reset" variant="info" ><b-icon icon="arrow90deg-left"></b-icon> Back to events</b-button>

        <div class="row">
            <div class="col-sm-12">
                <h2>Reservations {{event.title}} {{ new Date(event.event_date) | dateFormat('dd DD MMM YYYY') }}</h2>
            </div>
        </div>

        </span>



        <div class="row">
            <div class="col-sm-6">
                <p>Event: {{event.title}}<br>
                    Date: {{ new Date(event.event_date) | dateFormat('dd DD MMM YYYY') }}<br>

                    <span class="d-print-none">
                        Status:
                        <span v-if="(event.active==false)"><b-badge variant="secondary">no sale</b-badge></span>
                        <span v-else-if="(event.sold_out ==true)"><b-badge variant="danger">sold out</b-badge></span>
                        <span v-else-if="(event.tickets_sold>8)"><b-badge variant="warning">available</b-badge></span>
                        <!--TODO threshold (8) via config laten lopen-->
                        <span v-else><b-badge variant="success">available</b-badge></span>
                    </span>

                    <br>Total: {{event.tickets_sold}} (
                    <span v-for="(index,key) in sales_per_ticket_type" :key="key">
                     {{index}}x {{key}}
                    </span>  )
                </p>
            </div>
            <div class="col-sm-3 d-print-none">
                 <b-button @click="printGuestList()" variant="success" class="float-left mt-md-5"><b-icon icon="file-text"></b-icon> Print guestlist</b-button>
            </div>
            <div class="col-sm-3 d-print-none">
                <b-button @click="initAddSale()" variant="info" :disabled="event.sold_out==1" class="float-sm-left float-md-right mt-md-5"><b-icon icon="plus-square"></b-icon> Add New Reservation</b-button>
            </div>
        </div>

        <b-table sticky-header="600px" small bordered hover :items="sales" :fields="sales_table_fields"  head-variant="light">
           <!--
            <template v-slot:cell(nr_tickets)></template>
            <template v-slot:cell(ticket_title)></template>
            <template v-slot:cell(name)></template>
            -->
            <template v-slot:cell(tickets)="data">
                {{data.item.nr_tickets}}x {{data.item.ticket_title}}
            </template>
            <template v-slot:cell(still_to_pay)="data">
                <span v-if="data.item.total_amount - data.item.amount_paid !==0">
                {{ data.item.total_amount - data.item.amount_paid | toCurrency }}
                </span>
            </template>

            <template v-slot:row-details="row">
                <b-card class="d-print-none">

                <b-row>
                    <b-col sm="3" class="text-sm-right">
                        <b>Phone:<br>
                        Email:<br>
                        Extra's:</b>
                    </b-col>
                    <b-col sm="3">
                        {{ row.item.phone }}<br>
                        {{ row.item.email }}<br>
                        <span v-for="(extra) in row.item.extras" :key="extra.id">
                        <span v-if="!extra.show_on_door_list">
                            {{extra.pivot.nr}} x {{ extra.title }}<br>
                        </span>
                    </span>
                    </b-col>

                    <b-col sm="3" class="text-sm-right">
                        <b>Total Amount:<br>
                        Discount:<br>
                        Paid:<br>
                        Booked on:<br>
                        Last updated:<br>
                        Ticket sent on:<br>
                        pspRef:</b>
                    </b-col>
                    <b-col sm="3">
                        {{ row.item.total_amount+row.item.total_discount | toCurrency }}<br>
                        {{ row.item.total_discount | toCurrency }}<br>
                        {{ row.item.amount_paid | toCurrency }}<br>
                        {{ new Date(row.item.created_at) | dateFormat('DD MMM YY HH:mm') }}<br>
                        <span v-if="row.item.created_at !== row.item.updated_at">{{ new Date(row.item.updated_at) | dateFormat('DD MMM YY HH:mm') }}</span>
                        <span v-else>--</span><br>
                        <span v-if="row.item.ticket_sent">{{ new Date(row.item.ticket_sent) | dateFormat('DD MMM YY HH:mm') }}</span>
                        <span v-else class="text-danger" ><b>-no ticket sent yet-</b></span><br>
                        {{row.item.pspReference}}
                    </b-col>
                </b-row>

                <!--<b-button size="sm" @click="row.toggleDetails">Hide Details</b-button>-->
                </b-card>
            </template>

            <template v-slot:cell(comments)="data">
               <span v-if="data.item.guestlist_comments"><b>{{data.item.guestlist_comments}}</b><br></span>
               <span v-if="data.item.admin_comments" class="font-weight-lighter font-italic text-secondary d-print-none">[{{data.item.admin_comments}}]</span>
            </template>

            <template v-slot:cell(extras)="data">
                <span v-for="(extra) in data.item.extras" :key="extra.id">
                  <span v-if="extra.show_on_door_list">{{extra.pivot.nr}} x  {{ extra.title }}<br></span>
                </span>
            </template>

            <template v-slot:cell(show_details)="row">
                <b-button size="sm" @click="row.toggleDetails" class="mr-2">
                    <span v-if="row.detailsShowing"><b-icon icon="chevron-double-up"></b-icon></span>
                    <span v-else><b-icon icon="chevron-double-down"></b-icon></span>
                </b-button>
            </template>

            <template v-slot:cell(index)="data">
                <b-button @click="initUpdateSale( data.index )" size="sm" variant="outline-info"><b-icon icon="pencil-square"></b-icon></b-button>
                <b-button @click="deleteSale( data.index )" size="sm" variant="outline-danger"><b-icon icon="trash"></b-icon></b-button>
                <b-button @click="emailTickets( data.index )" size="sm" variant="outline-success"><b-icon icon="envelope"></b-icon></b-button>
            </template>
        </b-table>

        <b-button @click="reset" variant="info" class="d-print-none"><b-icon icon="arrow90deg-left"></b-icon> Back to events</b-button>

    </div><!--show_what = sales-->
    <!--crudsalecomponent-->

    <div v-if="show_what==='add_edit_event'">
        <br>

      <h4 >
          <b-button @click="reset" variant="info"><b-icon icon="arrow90deg-left"></b-icon> Back </b-button>
          <span v-if="add_update=='add'"> Add New Event(s)</span><span v-else>Edit Event</span>
      </h4>

      <div class="alert alert-danger" v-if="errors.length > 0">
          We have errors:
          <ul>
              <span v-html="errors">{{ errors }}</span>
          </ul>
      </div>

      <div class="form-group row">
          <label for="name" class="col-sm-3 col-form-label">Title:</label>
          <div class="col-sm-8">
          <input required type="text" name="title" id="title" class="form-control"
              v-model="event.title">
          </div>

      </div>

      <div class="form-group row">
          <label for="title" class="col-sm-3 col-form-label">Description (Optional):</label>
          <div class="col-sm-8">
          <textarea name="description" id="description" cols="30" rows="2" class="form-control"
              v-model="event.description"></textarea>
          </div>
      </div>

      <div class="form-group">

          <!-- in case of add-->
          <span v-if="add_update=='add'">
              <label for="event_date">Event date(s):</label>

              <DatePicker
                  v-model="event.event_date"
                  mode="multiple"
                  :value="null"

                  is-inline

                  :input-props='{
                      placeholder: "Please choose a date",
                      readonly: true,
                  }'
              />
            <!--  <multipleDatepicker v-model="event.event_date" name="event_date" id="event_date"></multipleDatepicker>-->
          </span>

          <!-- in case of update-->
          <span v-if="add_update=='update'">
              <label for="event_date">Event date:</label>
              <input required type="date" name="event_date" id="event_date" class="form-control"
              v-model="event.event_date">
          </span>

          </div>

          <div class="form-group row">
              <label for="start_time" class="col-sm-3 col-form-label">Start time:</label>
              <div class="col-sm-8">
              <input required type="time" name="start_time" id="start_time" class="form-control"
                  v-model="event.start_time">
              </div>
          </div>
          <div class="form-group row">
              <label for="end_time" class="col-sm-3 col-form-label">End time:</label>
              <div class="col-sm-8">
              <input required type="time" name="end_time" id="end_time" class="form-control"
                  v-model="event.end_time">
              </div>
          </div>
          <div class="form-group row">
              <label for="capacity" class="col-sm-3 col-form-label">Total capacity</label>
              <div class="col-sm-8">
              <input type="number" class="form-control form-control-sm" name="capacity" min="0" step="1" v-model="event.capacity">
              <small class="form-text text-muted">Total number of seats available, minimal 1</small>
              </div>
          </div>

          <div class="form-group row">
              <label for="min_per_sale" class="col-sm-3 col-form-label">Min tickets/sale</label>
              <div class="col-sm-8">
              <input type="number" class="form-control form-control-sm" name="min_per_sale" min="0" step="1" v-model="event.min_per_sale">
              <small class="form-text text-muted">Optional, no value entered means minimum per sale is 1</small>
              </div>
          </div>

          <div class="form-group row">
              <label for="max_per_sale" class="col-sm-3 col-form-label">Max tickets/sale</label>
              <div class="col-sm-8">
              <input type="number" class="form-control form-control-sm" name="max_per_sale" min="0" step="1" v-model="event.max_per_sale">
              <small class="form-text text-muted">Optional, no value entered means maximum per sale is equal to total capacity</small>
              </div>
          </div>

          <div class="form-group row">
              <label for="tickets_reserved" class="col-sm-3 col-form-label">Tickets reserved</label>
              <div class="col-sm-8">
              <input type="number" class="form-control form-control-sm" name="tickets_reserved" min="0" step="1" v-model="event.tickets_reserved">
              <small class="form-text text-muted">Optional, number of tickets kept apart, not available for online sale (no value entered means zero)</small>
              </div>
          </div>

          <div class="form-group row">
              <label for="active" class="col-sm-3 col-form-label">Open for sale</label>
              <div class="col-sm-8">
              <select class="form-control form-control-sm" name="active" v-model="event.active">
                  <option value="1" selected>Yes</option>
                  <option value="0">No</option>
              </select>
              <small class="form-text text-muted">select yes to start selling immediately.<br>Select no to close for sale and then you have to open it later manually.</small>
              </div>
          </div>

          <div class="form-group row">
              <label for="ticket_group-selection" class="col-sm-3 col-form-label">Select ticket group for sale:</label>
              <div class="col-sm-8">
                <span v-for="ticket_group in ticket_groups" :key="ticket_group.id">

                  <input type="radio" :id="ticket_group.id" :value="ticket_group.id" v-model="event.ticket_group_id">
                  <label class="form-check-label" :for="ticket_group.id">{{ ticket_group.admin_notes }}</label>
                    <br/><hr>
                </span>
              </div>
          </div>

          <div class="form-group row">
              <label for="ticket_group-selection" class="col-sm-3 col-form-label">Select categories for sale:</label>
              <div class="col-sm-8">
              <ul class="list-group list-group-flush">
                  <li class="list-group-item" v-for="category in categories" :key="category.id">

                      <input class="form-check-input" type="checkbox" :value="category.id"
                                                          v-bind:id="category.title" :name="category.title" v-model="checkedCategories">
                      <label class="form-check-label" for="defaultCheck1">{{ category.title }} </label>

                  </li>
              </ul>
              </div>
          </div>

          <!-- in case of add-->
          <span v-if="add_update=='add'">
          <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="button" @click="createEvent" class="btn btn-primary">Create</button>
          </span>

          <!-- in case of update-->
          <span v-if="add_update=='update'">
          <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="button" @click="updateEvent" class="btn btn-primary">Update</button>
          </span>

        </div><!--show_what = add_edit-event-->


    <div v-if="show_what==='add_edit_sale'">
        <CrudSale v-bind:sale_id="sale_id" v-bind:event_id="event_id" v-on:returnValue="onCloseCrudSale"></CrudSale>
    </div><!--show_what = add_edit_sale-->


    </div><!--container-->
</template>


<script>
import DatePicker from 'v-calendar/lib/components/date-picker.umd';
import CrudSale from './CrudSaleComponent.vue'



export default {
    components: {
    DatePicker,
    CrudSale
  },
  data() {
    return {
        //data crudsalecomponent
        event_id:"",
        sale_id:"",
        //end data crudsalecomponent

        events_table_fields:
        [
            {
                key: 'event_date',
                label: 'Date',
                sortable: false
            },
            {
                key: 'title',
                label: 'Event',
                sortable: false
            },
            {
                key: 'status',
                label: 'Status',
                sortable: false
            },

            // A virtual column made up from two fields
            {
                key: 'tickets_sold',
                label: 'Tickets sold',
                sortable: false,
            },
            {
                key: 'tickets_reserved',
                label: 'Tickets Reserved',
                sortable: false,
            },
            {
                key: 'index',
                label: 'edit/delete',
                sortable: false
            },
        ],


        //checkbox
        show_what:"events",
        show_past_events:false,
        loaded:false,
        //event data
        event: {
            title: "",
            description: "",
            event_date: "",
            start_time: "",
            end_time: "",
            min_per_sale: "",
            max_per_sale: "",
            capacity: "",
            ticket_groups_reserved: "",
            active: "1",
            sold_out: "",
            ticket_group_id:"",
            checkedCategories: []

        },
        errors: "",
        events: [],
        message: "",
        add_update: "",
        show: false,
        dateValue: "",

        //ticket_group data
        /*
        ticket_group: {
            title: "",
            description: "",
            admin_notes: "",
            price: "",
            vat: "",
            order: "",
        },*/
        //category data
        category: {
        order: "",
        title: "",
        description: "",
        admin_notes: ""
      },

        ticket_groups: [],
        categories: [],
       // checkedTicketGroup: "",
        checkedCategories: [],


        //DatePicker
        date:"",

        //sales
        sales: [],
        sales_per_ticket_type:[],
        sales_table_fields: [
            {
            key: 'still_to_pay',
            label: 'Still to pay',
            sortable: false
            },
            'tickets',
            {
                key:'extras',
                label:'Extras',
            },
            'name',
            'country_code',
            {
            key: 'phone',
            label: 'Phone',
            class: 'd-none d-print-table-cell',
          },
            'ticket_nr',
            'comments',
         {
            key: 'promocode_code',
            label: 'Promocode',
            class: 'd-print-none',
          },

          {
            key:'show_details',
            label:'Details',
            class: 'd-print-none',
          },
          {
            key: 'index',
            label: 'Edit/Delete',
            class: 'd-print-none',
          },
        ],
    };

  },

  mounted() {
    this.readEvents();
    this.readTicketGroups();
    this.readCategories();
    this.loaded=true;
  },

  methods: {

    initAddEvent() {
        this.errors = "";
        this.event.event_date = "";
        this.date="";
        this.add_update = "add";
    //    this.checkedTicketGroup="";
        this.checkedCategories=[];
        this.show_what="add_edit_event";
        //$("#add_event_model").modal("show");
    },

    showErrors(error) {
      this.errors = "<ul>";
      let response = error.response;
      Object.keys(response.data.errors).forEach(item => {
        this.errors += "<li>" + response.data.errors[item] + "</li>";
      });
      this.errors += "</ul>";
    },

    createEvent() {
     //   this.event.checkedTicketGroup = this.checkedTicketGroup;//place selection in event data so it will be transferred with axios
        this.event.checkedCategories = this.checkedCategories;//place selection in event data so it will be transferred with axios
      axios
        .post("/admin/event", this.$data.event )

        .then(response => {
          $("#add_event_model").modal("hide");
          //refresh table on screen (there may be a better way of doing this) *verbeterpunt*
          this.reset();
          this.$toasted.global.ajc_success({ message : response.data.message});
        })

        .catch(error => {
          this.showErrors(error);
        });
    },

    reset() {
        this.readEvents();
        this.show_what="events";
    },

    readEvents() {
        if(this.show_past_events){
            axios.get("/admin/getevents/all").then(response => {
                this.events = response.data.events;
                if (this.event_id) this.event = this.events.find(event => event.id === this.event_id);//update current event
            });

        }
        else{
            axios.get("/admin/getevents/future").then(response => {
                this.events = response.data.events;
                if (this.event_id) this.event = this.events.find(event => event.id === this.event_id);//update current event
            });

        }

    },

    onShowEventsChange(){
        this.readEvents();
    },

    refreshEvent(index){
        axios.get("/admin/getevents/"+this.events[index].id).then(response => {
                //Vue.set needed to tell vue a part of an aray is updated, is normally not reactive as it did not exist when initialized
                Vue.set(this.events, index, response.data.event);
            });
    },


    initUpdateEvent(edit_event_id) {
    console.log("event_id="+edit_event_id);
      this.errors = "";
      this.add_update = "update";
      this.event = this.events.find(event => event.id === edit_event_id);
     // this.event = this.events[index];
      axios.get("/admin/eventgetcategories/"+ edit_event_id)

        .then(response => {
        this.checkedCategories = response.data.checkedCategories;
        this.show_what="add_edit_event";

      });
    },

    updateEvent() {
       // this.event.checkedTicketGroup = this.checkedTicketGroup;//place selection in event data so it will be transferred with axios
        this.event.checkedCategories = this.checkedCategories;//place selection in event data so it will be transferred with axios
      axios
        .put("/admin/event/" + this.event.id, this.$data.event)

        .then(response => {
          $("#add_event_model").modal("hide");
          this.$toasted.global.ajc_success({ message : response.data.message});
          this.reset();
        })

        .catch(error => {
          this.showErrors(error);
          console.log(error);
        });
    },

    deleteEvent(index) {
        console.log('index='+index);
        let conf = confirm(
        'Do you ready want to delete event "' +
          this.events[index].title + " " +this.events[index].event_date +
          '"?'
      );
      if (conf === true) {
        axios
          .delete("/admin/event/" + this.events[index].id)

          .then(response => {
            this.events.splice(index, 1);
            this.$toasted.global.ajc_success({ message : response.data.message});
          })

          .catch(error => {
            this.$toasted.global.ajc_error({ message : error.response.data.message});
            // Error
          });
      }
    },

    //ticket_group functions
    readTicketGroups() {
      axios.get("/admin/ticket_group").then(response => {
        this.ticket_groups = response.data.ticket_groups;
      });
    },
    //category functions
    readCategories() {
      axios.get("/admin/category").then(response => {
        this.categories = response.data.categories;
      });
    },

    async adjustReservedTickets(index){
        console.log('adjusting reserved tickets for event id:'+this.events[index].id);
        try{
            let response = await axios.get("/admin/adjustreservedtickets/",
                {params: {
                event_id: this.events[index].id,
                reserved: this.events[index].tickets_reserved
                }})

            console.log(response.data.message);
            this.$toasted.global.ajc_success({ message : response.data.message});
            this.events[index].tickets_reserved = Number(response.data.reserved);
            console.log('setting reserved tickets to:'+response.data.reserved);
            this.refreshEvent(index);
          // this.readEvents();

        }
        catch(error){
            console.log("errormessage:"+error.response.data.message);
            this.$toasted.global.ajc_error({ message : error.response.data.message});
            this.events[index].tickets_reserved = Number(error.response.data.reserved);
            console.log('setting reserved tickets to:'+error.response.data.reserved);
        }
    },



    //sales
    async readSales(event_id){
        this.event_id=event_id;//set event id to current selected event
        this.event = this.events.find(event => event.id === event_id);//not sure why this line is here?
        console.log('get sales before');
        try{
            let response = await axios.get("/admin/getsales/" + event_id);
            this.sales = response.data.sales;

            //get sales per ticket type
            this.sales_per_ticket_type = response.data.sales_per_ticket_type;

        }
        catch(error){
            console.log(error)
        }
        console.log('get sales after');
        this.show_what = "sales";
    },

    deleteSale(index) {
        console.log('index='+index);
        let conf = confirm(
        'Do you ready want to delete reservation "' +
          this.sales[index].name + " " +this.sales[index].ticket_nr +
          '"?  (It will be placed in deleted reservations and can be restored later)'
        );
        if (conf === true) {
            axios
            .delete("/admin/sale/" + this.sales[index].id)

            .then(response => {
                this.sales.splice(index, 1);
                this.$toasted.global.ajc_success({ message : response.data.message});
                this.readEvents();//because the event can now be no longer sold out for instance
            })

            .catch(error => {
                this.$toasted.global.ajc_error({ message : error.response.data.message});
                // Error
            });
        }
    },

    //crudsalecompnonent functions
    initUpdateSale(index) {
        console.log("index="+index);
        this.sale_id=this.sales[index].id;
        //this.event_id=this.sale.event_id;
        this.show_what = "add_edit_sale";
    },
    emailTickets(index) {
        console.log('index='+index);
        let conf = confirm(
        'Do you want to email the tickets (again!?) to the guests "' +
          this.sales[index].name + " " +this.sales[index].ticket_nr +
          '"?'
        );
        if (conf === true) {
            axios
            .get("/admin/saletickets/" + this.sales[index].id)

            .then(response => {
                this.$toasted.global.ajc_success({ message : response.data.message});
                this.readSales(this.event_id);
            })

            .catch(error => {
                this.$toasted.global.ajc_error({ message : error.response.data.message});

                // Error
            });
        }
    },
    printGuestList(){
        window.print();
    },

    initAddSale() {
        this.event_id=this.event.id;
        this.sale_id="";
        //this.event_id=this.sale.event_id;
        this.show_what = "add_edit_sale";
    },
    onCloseCrudSale: function (returnValue) {
        console.log('crud closed with value:'+returnValue);
        if (returnValue!=='canceled'){
            this.$toasted.global.ajc_success({ message : returnValue});
            this.readSales(this.event.id);
            this.readEvents();//because the event can now be sold out for instance

        }
        else{
            this.$toasted.global.ajc_info({ message : 'canceled'});
        }
        this.show_what = "sales";
    },

    //end crudsalecompnonent functions


  }
};
</script>
