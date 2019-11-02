<template>
<div class="component-holder">
    <div class="row">
        <div class="col-md-12">

            <div class="alert alert-info alert-dismissible fade show" v-if="show" role="alert" id="alert" name="alert">
                <button type="button" class="close" v-on:click="show = !show" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {{ message }}
            </div>

            <div class="card">
                <div class="card-header">Events


                </div>
                <!--card header-->
                <div class="card-body">
                    <button @click="initAddEvent()" class="btn btn-primary btn-xs float-left">
                            + Add New Event(s)
                    </button>

                    <br><hr>
                    <input type="checkbox" id="checkbox" v-model="show_details_table">
                    <label for="checkbox">show detailed table</label>

                    <table class="table table-striped table-bordered table-responsive table-sm" v-if="events.length > 0" ref="table">
                        <thead>
                            <tr>

                                <th>Event</th>
                                <th>Date</th>
                                <!--<th>Event times</th>-->
                                <th v-if="show_details_table">min-max tickets/sale</th>
                                <th>Tickets sold</th>
                                <th v-if="show_details_table">Ticket group (#tickets)</th>
                                <th v-if="show_details_table">#categories</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>


                            <tr v-for="(event, index) in events" :key="event.id">

                                <td>
                                    {{ event.title }}
                                </td>
                                <td>
                                    {{ new Date(event.event_date) | dateFormat('dd DD MMM YYYY') }}
                                </td>
                                <!--
                                <td>
                                    {{ event.start_time}} - {{ event.end_time}}
                                </td>
                                -->
                                <td v-if="show_details_table">
                                    {{ event.min_per_sale }}-{{ event.max_per_sale }}
                                </td>
                                <td>
                                    {{ event.tickets_sold }} of {{ event.capacity }}
                                </td>
                                <td v-if="show_details_table">
                                    <!-- check if ticket_groups and events are loaded to avoid can not read property warning-->
                                    <span v-if="ticket_groups.find(ticket_groups => ticket_groups.id === event.ticket_group_id)">
                                        {{ ticket_groups.find(ticket_groups => ticket_groups.id === event.ticket_group_id).admin_notes }}
                                        ({{ticket_groups.find(ticket_groups => ticket_groups.id === event.ticket_group_id).tickets_count}} ticket(s))
                                    </span>
                                </td>
                                <td v-if="show_details_table">
                                    {{event.categories_count }}

                                </td>
                                <td v-if= "event.sold_out ===0 && event.active ===1">
                                      active
                                </td>
                                <td v-else-if = "event.sold_out">
                                      sold out
                                </td>
                               <td v-else-if =  "event.active === 0">
                                      not active
                                </td>
                                <td>

                                    <button @click="initUpdate(index)" class="btn btn-success btn-sm"><i class="fas fa-edit"></i>Edit</button>
                                    <button @click="deleteEvent(index)" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i>Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                     <div v-else>
                        <br><hr>
                        <p v-if="loaded">No events yet</p>
                    </div>
                </div>
                <!--card body-->

            </div>
            <!--card-->

        </div>
        <!--col-->
    </div>
    <!--row-->




    <div class="modal fade" tabindex="-1" role="dialog" id="add_event_model">

        <!--MODAL ADD UPDATE-->
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">
                        <span v-if="add_update=='add'">Add New Event(s)</span><span v-else>Edit Event</span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>

                </div>
                <!--modal header-->


                <div class="modal-body">

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
                            <label for="ticket_groups_reserved" class="col-sm-3 col-form-label">Tickets reserved</label>
                            <div class="col-sm-8">
                            <input type="number" class="form-control form-control-sm" name="ticket_groups_reserved" min="0" step="1" v-model="event.ticket_groups_reserved">
                            <small class="form-text text-muted">Optional, number of ticket_groups kept apart, not available for online sale (no value entered means zero)</small>
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
<!--
                        <div class="form-group row">
                            <label for="ticket_group-selection" class="col-sm-3 col-form-label">Select one ticket group for sale:</label>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item  ml-3" v-for="ticket_group in ticket_groups" :key="ticket_group.id">

                                    <input class="form-check-input" type="checkbox" :value="ticket_group.id"
                                                                        v-bind:id="ticket_group.title" :name="ticket_group.title" v-model="checkedTicketGroups">
                                    <label class="form-check-label" for="defaultCheck1">{{ ticket_group.admin_notes }} </label>

                                </li>
                            </ul>
                            <br>
                        </div>

-->                        <div class="form-group row">
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



                        </div>
                        <!--modal body-->

                        <div class="modal-footer">
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
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
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
        //checkbox
        show_details_table:true,
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
        $("#add_event_model").modal("show");
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
          this.readEvents();
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
    },

    readEvents() {
        axios.get("/admin/event").then(response => {
        this.events = response.data.events;

      });
    },



    initUpdate(index) {

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
  }
};
</script>
