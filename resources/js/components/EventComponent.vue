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
                            + Add New Event
                    </button>
                    <table class="table  table-striped table-responsive table-sm" v-if="events.length > 0" ref="table">
                        <thead>
                            <tr>

                                <th>Title</th>
                                <th>Description</th>
                                <th>Event date</th>
                                <!--<th>Event times</th>-->
                                <th>min-max</th>
                                <th>sold</th>
                                <th>status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>


                            <tr v-for="(event, index) in events" :key="event.id">

                                <td>
                                    {{ event.title }}
                                </td>
                                <td>
                                    {{ event.description }}
                                </td>

                                <td>
                                    {{ new Date(event.event_date) | dateFormat('dd DD MMM YYYY', dateFormatConfig) }}
                                </td>
                                <!--
                                <td>
                                    {{ event.start_time}} - {{ event.end_time}}
                                </td>
                                -->
                                <td>
                                    {{ event.min_per_sale }}-{{ event.max_per_sale }}
                                </td>
                                <td>
                                    {{ event.tickets_sold }} of {{ event.capacity }}
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
                        <p>no events yet</p>
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
                                <multipleDatepicker v-model="event.event_date" name="event_date" id="event_date"></multipleDatepicker>
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

export default {
  data() {
    return {
        event: {
        title: "",
        description: "",
        event_date: "",
        start_time: "",
        end_time: "",
        min_per_sale: "",
        max_per_sale: "",
        capacity: "",
        tickets_reserved: "",
        active: "1",
        sold_out: ""
      },
        errors: "",
        events: [],
        message: "",
        add_update: "",
        show: false,
        dateValue: "",
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
    };

  },

  mounted() {
    this.readEvents();
  },

  methods: {
    initAddEvent() {
      this.errors = "";
      this.event.event_date = "";
      this.date="";
      this.add_update = "add";
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
      axios
        .post("event", this.$data.event)

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
        this.event.tickets_reserved = "0";
        this.event.active = "1";
        */
    },

    readEvents() {
        axios.get("event").then(response => {
        this.events = response.data.events;

      });
    },



    initUpdate(index) {
      this.errors = "";
      this.add_update = "update";
      $("#add_event_model").modal("show");
      this.event = this.events[index];
    },

    updateEvent() {
      axios
        .put("event/" + this.event.id, this.$data.event)

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
          .delete("event/" + this.events[index].id)

          .then(response => {
            this.events.splice(index, 1);
            this.showMessage(response.data.message);
          })

          .catch(error => {
            this.showMessage(error.response.data.message);
            // Error
          });
      }
    }
  }
};
</script>
