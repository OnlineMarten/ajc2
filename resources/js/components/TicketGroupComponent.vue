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
                <div class="card-header">Ticketgroups

                </div>
                <!--card header-->

                <div class="card-body">
                    <button @click="initAddTicketgroup()" class="btn btn-primary btn-xs float-left">
                            + Add New Ticketgroup
                    </button>
                    <table class="table  table-striped table-responsive table-sm" v-if="ticket_groups.length > 0" ref="table">
                        <thead>
                            <tr>
                                <th>Group name</th>
                                <th>Public title</th>
                                <th>Public description</th>
                                <th> # ticket's</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(ticket_group, index) in ticket_groups" :key="ticket_group.id">
                                <td>
                                    {{ ticket_group.admin_notes }}
                                </td>
                                <td>
                                    {{ ticket_group.title }}
                                </td>
                                <td>
                                    {{ ticket_group.description }}
                                </td>

                                <td>
                                    {{ ticket_group.tickets_count }}
                                </td>
                                <td>

                                    <button @click="initUpdate(index)" class="btn btn-success btn-sm"><i class="fas fa-edit"></i>Edit</button>
                                    <button @click="deleteTicketgroup(index)" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i>Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                     <div v-else>
                        <br><hr>
                        <p>no ticket_groups yet</p>
                    </div>
                </div>
                <!--card body-->

            </div>
            <!--card-->

        </div>
        <!--col-->
    </div>
    <!--row-->

    <div class="modal fade" tabindex="-1" role="dialog" id="add_ticket_group_model">

        <!--MODAL ADD UPDATE-->
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title"><span v-if="add_update=='add'">Add New Ticketgroup</span><span v-else>Edit Ticketgroup</span></h4>
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

                    <div class="form-group">
                        <label for="title">Group name (needed for identification, will be displayed internally only):</label>

                        <input type="text" name="admin_notes" id="admin_notes" class="form-control"
                            v-model="ticket_group.admin_notes">
                    </div>

                    <div class="form-group">
                        <label for="name">Public group title (Optional):</label>
                        <input required type="text" name="title" id="title" class="form-control"
                            v-model="ticket_group.title">
                        <small class="form-text text-muted">The title is optional and will be displayed above the tickets on the website</small>
                    </div>

                    <div class="form-group">
                        <label for="title">Public description (Optional):</label>
                        <small class="form-text text-muted">The description is optional and will be displayed below the title on the website</small>
                        <textarea name="description" id="description" cols="30" rows="5" class="form-control"
                            v-model="ticket_group.description"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="order">Display Order (optional)</label>
                        <input required type="number" step="1" min="1" name="order" id="order" class="form-control"
                                v-model="ticket_group.order">
                        <small class="form-text text-muted">group display order, only used on this screen</small>
                    </div>



                    <div class="form-group row">
                            <label for="ticket-selection" class="col-sm-3 col-form-label">Tickets connected to this group:</label>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item  ml-3" v-for="ticket in tickets" :key="ticket.id">

                                    <input class="form-check-input" type="checkbox" :value="ticket.id"
                                                                        v-bind:id="ticket.title" :name="ticket.title" v-model="checkedTickets">
                                    <label class="form-check-label" for="defaultCheck1">{{ ticket.title }} </label>

                                </li>
                            </ul>
                        <br>
                    </div>

                    </div>
                    <!--modal body-->

                    <div class="modal-footer">
                        <!-- in case of add-->
                        <span v-if="add_update=='add'">
                            <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="button" @click="createTicketgroup" class="btn btn-primary">Create</button>
                        </span>
                        <!-- in case of update-->
                        <span v-if="add_update=='update'">
                            <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="button" @click="updateTicketgroup" class="btn btn-primary">Update</button>
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
      ticket_group: {
        order: "",
        title: "",
        description: "",
        admin_notes: ""
      },
      errors: "",
      ticket_groups: [],
      message: "",
      add_update: "",
      show: false,

      //tickets data
        ticket: {
        title: "",
        admin_notes: ""
      },
      tickets:[],
      checkedTickets:[],
    };
  },

  mounted() {
    this.readTicketgroups();

  },

  methods: {
    initAddTicketgroup() {
      this.errors = "";
      this.add_update = "add";
       this.readTickets();
       this.checkedTickets=[];
      $("#add_ticket_group_model").modal("show");
    },

    showErrors(error) {
      this.errors = "<ul>";
      let response = error.response;
      Object.keys(response.data.errors).forEach(item => {
        this.errors += "<li>" + response.data.errors[item] + "</li>";
      });
      this.errors += "</ul>";
    },

    createTicketgroup() {

     this.ticket_group.checkedTickets = this.checkedTickets;//place selection in ticket_group data so it will be transferred with axios
      axios
        .post("/admin/ticket_group", this.$data.ticket_group)

        .then(response => {
          $("#add_ticket_group_model").modal("hide");
          //refresh table on screen (there may be a better way of doing this) *verbeterpunt*
          this.showMessage(response.data.message);
          this.reset();
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
      this.ticket_group.order = "";
      this.ticket_group.title = "";
      this.ticket_group.description = "";
      this.ticket_group.admin_notes = "";
      this.readTicketgroups();
    },

    readTicketgroups() {

      axios.get("/admin/ticket_group").then(response => {
        this.ticket_groups = response.data.ticket_groups;
      });
    },



    initUpdate(index) {
      this.errors = "";
      this.add_update = "update";
       this.readTickets();
      $("#add_ticket_group_model").modal("show");
      this.ticket_group = this.ticket_groups[index];
       axios.get("/admin/ticketgroupgettickets/"+ this.ticket_groups[index].id)

        .then(response => {
        this.checkedTickets = response.data.checkedTickets;

      });
    },

    updateTicketgroup() {
      this.ticket_group.checkedTickets = this.checkedTickets;//place selection in ticket_group data so it will be transferred with axios
      axios
        .put("/admin/ticket_group/" + this.ticket_group.id, this.$data.ticket_group)

        .then(response => {
          $("#add_ticket_group_model").modal("hide");
          this.showMessage(response.data.message);
          this.reset();
        })

        .catch(error => {
          this.showErrors(error);
        });
    },

    deleteTicketgroup(index) {
      let conf = confirm(
        'Do you ready want to delete ticket_group "' +
          this.ticket_groups[index].title +
          '"?'
      );
      if (conf === true) {
        axios
          .delete("/admin/ticket_group/" + this.ticket_groups[index].id)

          .then(response => {
            this.ticket_groups.splice(index, 1);
            this.showMessage(response.data.message);
          })

          .catch(error => {
            this.showMessage(error.response.data.message);
            // Error
          });
      }
    },
    //tickets
    readTickets() {
        axios.get("/admin/ticket").then(response => {
        this.tickets = response.data.tickets;

      });
    }
  }
};
</script>
