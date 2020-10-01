<template>
<div class="component-holder">

    <div class="alert alert-info alert-dismissible fade show" v-if="show" role="alert" id="alert" name="alert">
        <button type="button" class="close" v-on:click="show = !show" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        {{ message }}
    </div>


  <div v-if="show_what === 'events'">

      <br>
      <b-button @click="initAddEvent()" variant="outline-info"><b-icon icon="plus-square"></b-icon> Add New Event(s)</b-button>


    <br><hr>
   <!-- <input type="checkbox" id="checkbox" v-model="show_details_table">
    <label for="checkbox">show detailed table</label>-->



    <b-table sticky-header small hover :items="events" :fields="table_fields_small" head-variant="light">

      <template v-slot:cell(event_date)="data">
        {{ new Date(data.value) | dateFormat('dd DD MMM YYYY') }}
      </template>

      <template v-slot:cell(title)="data">
        <b-button @click="readSales( data.index )" size="sm" variant="outline-secondary"> <b-icon icon="person-lines-fill"></b-icon> {{ data.value }} </b-button>
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

      <template v-slot:cell(index)="data">
          <b-button @click="initUpdate( data.index )" size="sm" variant="outline-info"><b-icon icon="pencil-square"></b-icon></b-button>
          <b-button @click="deleteEvent( data.index )" size="sm" variant="outline-danger"><b-icon icon="trash"></b-icon></b-button>
      </template>


    </b-table>
    <input type="checkbox" id="checkbox" v-model="show_past_events" v-on:change="onShowEventsChange()">
    <label for="checkbox">show past events</label>
    </div><!--show_what = events-->

    <div v-if="show_what === 'sales'">
        <br>
        <b-button @click="reset" variant="info"><b-icon icon="arrow90deg-left"></b-icon> Back to events</b-button>

        <h2>Reservations</h2>

        <p>Event: {{event.title}}<br>
            Date: {{ new Date(event.event_date) | dateFormat('dd DD MMM YYYY') }}<br>
            Status:
            <!--TODO threshold (8) via config laten lopen-->
            <span v-if="(event.active==false)"><b-badge variant="secondary">no sale</b-badge></span>
            <span v-else-if="(event.sold_out ==true)"><b-badge variant="danger">sold out</b-badge></span>
            <span v-else-if="(event.tickets_sold>8)"><b-badge variant="warning">available</b-badge></span>
            <span v-else><b-badge variant="success">available</b-badge></span>
        </p>

        <b-table sticky-header small hover :items="sales" :fields="sales_table_fields"  head-variant="light">
        </b-table>

        <b-button @click="reset" variant="info"><b-icon icon="arrow90deg-left"></b-icon> Back to events</b-button>

    </div><!--show_what = sales-->



    <div v-if="show_what==='add_edit'">
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

        </div><!--show_what = add_edit-->


    </div><!--container-->
</template>


<script>
import DatePicker from 'v-calendar/lib/components/date-picker.umd';


export default {
    components: {
    DatePicker,
  },
  data() {
    return {
        table_fields_small:
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
        sales_table_fields: ['nr_tickets', 'ticket_title', 'name'],
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
        this.show_what="add_edit";
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
          this.showMessage(response.data.message);
        })

        .catch(error => {
          this.showErrors(error);
        });
    },

    showMessage(message) {
      this.message = message;
      this.show = true;
    },

    reset() {
        this.readEvents();
       /* this.event.title = "";
        this.event.description = "";
        this.event.event_date = "";
        this.event.start_time = "";
        this.event.end_time = "";
        this.event.min_per_sale="";
        this.event.max_per_sale = "";
        this.event.capacity = "";
        this.event.ticket_groups_reserved = "0";
        this.event.active = "1";
        */
       this.show_what="events";
    },

    readEvents() {
        if(this.show_past_events){
            axios.get("/admin/getevents/all").then(response => {
                this.events = response.data.events;
            });

        }
        else{
            axios.get("/admin/getevents/future").then(response => {
                this.events = response.data.events;
            });

        }

    },
    onShowEventsChange(){
        this.readEvents();
    },


    initUpdate(index) {
    this.show_what="add_edit";
    console.log("index="+index);
      this.errors = "";
      this.add_update = "update";
      $("#add_event_model").modal("show");
      this.event = this.events[index];
     /*  axios.get("eventgetticketgroups/"+ this.events[index].id)

        .then(response => {
        this.checkedTicketGroup = response.data.checkedTicketGroup;

      });*/
      axios.get("/admin/eventgetcategories/"+ this.events[index].id)

        .then(response => {
        this.checkedCategories = response.data.checkedCategories;

      });
    },

    updateEvent() {
       // this.event.checkedTicketGroup = this.checkedTicketGroup;//place selection in event data so it will be transferred with axios
        this.event.checkedCategories = this.checkedCategories;//place selection in event data so it will be transferred with axios
      axios
        .put("/admin/event/" + this.event.id, this.$data.event)

        .then(response => {
          $("#add_event_model").modal("hide");
          this.showMessage(response.data.message);
          this.reset();
        })

        .catch(error => {
          this.showErrors(error);
          console.log(error);
        });
    },

    deleteEvent(index) {
      let conf = confirm(
        'Do you ready want to delete event "' +
          this.events[index].title +
          '"?'
      );
      if (conf === true) {
        axios
          .delete("/admin/event/" + this.events[index].id)

          .then(response => {
            this.events.splice(index, 1);
            this.showMessage(response.data.message);
          })

          .catch(error => {
            this.showMessage(error.response.data.message);
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

    //sales
    async readSales(index){
            this.event = this.events[index];
            console.log('get sales before');
           try{
                let response = await axios.get("/admin/getsales/" + this.events[index].id);
                this.sales = response.data.sales;
            }
            catch(error){
                console.log(error)
            }
            console.log('get sales after');
            this.show_what = "sales";
        },
  }
};
</script>
